<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\ActivityLog;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use App\Support\SqlDialect;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Membuat transaksi contoh 30 hari terakhir dengan pola yang masuk akal:
 * ramai jam 11-13 & 18-20, akhir pekan lebih padat, sebagian kecil belum lunas.
 */
class DemoTransactionSeeder extends Seeder
{
    private const DAYS = 30;

    public function run(): void
    {
        // Baris transaksi disisipkan dengan id tetap mulai dari 1, jadi seeder ini
        // tidak boleh jalan dua kali di atas data yang sudah ada.
        if (Transaction::query()->exists()) {
            $this->command?->info("Transaksi contoh sudah ada — DemoTransactionSeeder dilewati.");

            return;
        }

        $cashiers = User::query()
            ->where('role', UserRole::Cashier)
            ->where('status', UserStatus::Active)
            ->get();

        $menus = Menu::query()->get();

        if ($cashiers->isEmpty() || $menus->isEmpty()) {
            return;
        }

        // Menu populer lebih sering terjual agar grafik "best seller" bermakna.
        $weighted = [];
        foreach ($menus->values() as $index => $menu) {
            $weight = max(1, 10 - intdiv($index, 2));
            for ($i = 0; $i < $weight; $i++) {
                $weighted[] = $menu;
            }
        }

        $today = CarbonImmutable::today();
        $transactionRows = [];
        $itemRows = [];
        $id = 0;

        for ($dayOffset = self::DAYS - 1; $dayOffset >= 0; $dayOffset--) {
            $date = $today->subDays($dayOffset);
            $isWeekend = $date->isWeekend();
            $count = random_int($isWeekend ? 22 : 12, $isWeekend ? 34 : 24);

            // Hari ini hanya terisi sampai jam berjalan.
            if ($dayOffset === 0) {
                $count = max(4, (int) round($count * min(1, now()->hour / 20)));
            }

            $sequence = 0;

            for ($n = 0; $n < $count; $n++) {
                $at = $date->setTime($this->busyHour($dayOffset === 0 ? now()->hour : null), random_int(0, 59), random_int(0, 59));

                if ($dayOffset === 0 && $at->isFuture()) {
                    continue;
                }

                $sequence++;
                $id++;

                $cashier = $cashiers->random();
                $lineCount = random_int(1, 4);
                $total = 0;
                $picked = [];

                for ($l = 0; $l < $lineCount; $l++) {
                    $menu = $weighted[array_rand($weighted)];

                    if (isset($picked[$menu->id])) {
                        continue;
                    }

                    $qty = random_int(1, 3);
                    $subtotal = $menu->price * $qty;
                    $total += $subtotal;
                    $picked[$menu->id] = true;

                    $itemRows[] = [
                        'transaction_id' => $id,
                        'menu_id' => $menu->id,
                        'menu_name' => $menu->name,
                        'price' => $menu->price,
                        'qty' => $qty,
                        'subtotal' => $subtotal,
                        'created_at' => $at,
                        'updated_at' => $at,
                    ];
                }

                // Transaksi belum lunas hanya wajar untuk hari berjalan.
                $unpaid = $dayOffset === 0 ? random_int(1, 100) <= 25 : random_int(1, 100) <= 2;
                $pay = $unpaid ? 0 : $this->roundedPayment($total);

                $transactionRows[] = [
                    'id' => $id,
                    'invoice_number' => 'KSRK-'.$date->format('md').'-'.sprintf('%03d', $sequence),
                    'table_number' => random_int(1, 100) <= 85 ? (string) random_int(1, 20) : null,
                    'user_id' => $cashier->id,
                    'total_amount' => $total,
                    'pay_amount' => $pay,
                    'change_amount' => $unpaid ? 0 : $pay - $total,
                    'payment_status' => $unpaid ? PaymentStatus::Unpaid->value : PaymentStatus::Paid->value,
                    'paid_at' => $unpaid ? null : $at->addMinutes(random_int(1, 8)),
                    'created_at' => $at,
                    'updated_at' => $at,
                ];
            }
        }

        DB::transaction(function () use ($transactionRows, $itemRows) {
            foreach (array_chunk($transactionRows, 200) as $chunk) {
                DB::table('transactions')->insert($chunk);
            }

            foreach (array_chunk($itemRows, 500) as $chunk) {
                DB::table('transaction_items')->insert($chunk);
            }
        });

        // Baris di atas disisipkan dengan id eksplisit; selaraskan sequence
        // PostgreSQL agar transaksi berikutnya dari aplikasi tidak bentrok id.
        SqlDialect::resetSequence('transactions');
        SqlDialect::resetSequence('transaction_items');

        $this->seedActivityLog($cashiers);
    }

    /**
     * Jam sibuk makan siang & makan malam.
     * $maxHour dipakai untuk hari berjalan agar tidak membuat transaksi di masa depan.
     */
    private function busyHour(?int $maxHour = null): int
    {
        $hours = [
            10, 11, 11, 11, 12, 12, 12, 12, 13, 13, 13,
            14, 15, 16, 17,
            18, 18, 18, 19, 19, 19, 19, 20, 20, 21,
        ];

        if ($maxHour !== null) {
            $hours = array_values(array_filter($hours, fn (int $h) => $h <= $maxHour));

            if ($hours === []) {
                return max(0, $maxHour);
            }
        }

        return $hours[array_rand($hours)];
    }

    /** Pelanggan biasanya membayar dengan pecahan bulat. */
    private function roundedPayment(int $total): int
    {
        if (random_int(1, 100) <= 35) {
            return $total;
        }

        foreach ([20000, 50000, 100000, 150000, 200000] as $note) {
            if ($note >= $total) {
                return $note;
            }
        }

        return (int) (ceil($total / 50000) * 50000);
    }

    /** @param  Collection<int, User>  $cashiers */
    private function seedActivityLog(Collection $cashiers): void
    {
        $manager = User::query()->where('role', UserRole::Manager)->first();

        Transaction::query()
            ->latest('created_at')
            ->take(3)
            ->get()
            ->each(function (Transaction $transaction) {
                ActivityLog::create([
                    'user_id' => $transaction->user_id,
                    'category' => 'Order',
                    'detail' => 'Memproses transaksi '.$transaction->invoice_number,
                    'status' => $transaction->isPaid() ? 'Selesai' : 'Pending',
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->created_at,
                ]);
            });

        if ($manager) {
            ActivityLog::create([
                'user_id' => $manager->id,
                'category' => 'Edit Menu',
                'detail' => 'Memperbarui harga menu "Es Teh Manis"',
                'status' => 'Sukses',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ]);

            ActivityLog::create([
                'user_id' => $manager->id,
                'category' => 'Create User',
                'detail' => 'Menambah pengguna baru "Budi Santoso" (Kasir)',
                'status' => 'Sukses',
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ]);
        }

        if ($cashiers->isNotEmpty()) {
            ActivityLog::create([
                'user_id' => $cashiers->first()->id,
                'category' => 'Login',
                'detail' => 'Masuk ke halaman transaksi kasir',
                'status' => 'Selesai',
                'created_at' => now()->subHour(),
                'updated_at' => now()->subHour(),
            ]);
        }
    }
}
