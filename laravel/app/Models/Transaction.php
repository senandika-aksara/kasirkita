<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'table_number',
        'user_id',
        'total_amount',
        'pay_amount',
        'change_amount',
        'payment_status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'pay_amount' => 'integer',
            'change_amount' => 'integer',
            'payment_status' => PaymentStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatus::Paid);
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('payment_status', PaymentStatus::Unpaid);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    /** Ringkasan item untuk tabel daftar, mis. "2 Nasi Goreng, 3 Es Teh". */
    public function itemSummary(int $limit = 3): string
    {
        $items = $this->items->take($limit)
            ->map(fn (TransactionItem $item) => $item->qty.' '.$item->menu_name)
            ->implode(', ');

        $rest = $this->items->count() - $limit;

        return $rest > 0 ? $items.', +'.$rest.' lainnya' : $items;
    }
}
