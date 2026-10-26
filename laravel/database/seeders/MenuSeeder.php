<?php

namespace Database\Seeders;

use App\Enums\MenuStatus;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /** @var array<string, array<int, array{0: string, 1: int, 2?: string}>> */
    private array $catalog = [
        'Makanan' => [
            ['Nasi Goreng Ayam', 25000],
            ['Nasi Goreng Spesial', 35000],
            ['Ayam Geprek', 30000],
            ['Ayam Bakar Madu', 32000],
            ['Bubur Ayam', 20000],
            ['Mie Goreng Jawa', 22000],
            ['Soto Ayam Lamongan', 24000],
            ['Nasi Uduk Komplit', 23000],
            ['Bakso Urat Jumbo', 28000],
            ['Pecel Lele Sambal Ijo', 26000, 'habis'],
        ],
        'Minuman' => [
            ['Es Teh Manis', 8000],
            ['Es Teh Jumbo', 12000],
            ['Es Jeruk Peras', 10000],
            ['Kopi Susu Gula Aren', 18000],
            ['Kopi Hitam Tubruk', 10000],
            ['Es Campur', 15000],
            ['Air Mineral', 5000],
        ],
        'Camilan' => [
            ['Camilan Mix', 15000],
            ['Pisang Goreng Keju', 16000],
            ['Tahu Crispy', 12000],
            ['Kentang Goreng', 14000],
            ['Roti Bakar Cokelat', 13000],
        ],
    ];

    public function run(): void
    {
        $sort = 1;
        $code = 1;

        foreach ($this->catalog as $categoryName => $items) {
            $category = Category::updateOrCreate(
                ['name' => $categoryName],
                ['sort_order' => $sort++]
            );

            foreach ($items as $item) {
                Menu::updateOrCreate(
                    ['name' => $item[0]],
                    [
                        'code' => sprintf('MNU-%03d', $code++),
                        'category_id' => $category->id,
                        'price' => $item[1],
                        'status' => ($item[2] ?? 'tersedia') === 'habis'
                            ? MenuStatus::SoldOut
                            : MenuStatus::Available,
                    ]
                );
            }
        }
    }
}
