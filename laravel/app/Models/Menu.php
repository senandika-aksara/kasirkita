<?php

namespace App\Models;

use App\Enums\MenuStatus;
use App\Support\SqlDialect;
use App\Support\Uploads;
use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'category_id',
        'name',
        'price',
        'image',
        'status',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'status' => MenuStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', MenuStatus::Available);
    }

    public function isAvailable(): bool
    {
        return $this->status === MenuStatus::Available;
    }

    /** Menu yang sudah pernah terjual tidak boleh dihapus permanen. */
    public function hasBeenSold(): bool
    {
        return $this->transactionItems()->exists();
    }

    public function imageUrl(): ?string
    {
        return Uploads::url($this->image);
    }

    /** Membuat kode menu berikutnya: MNU-001, MNU-002, ... */
    public static function nextCode(): string
    {
        $last = static::query()
            ->where('code', 'like', 'MNU-%')
            ->orderByRaw(SqlDialect::naturalOrder('code', 'DESC'))
            ->value('code');

        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        return sprintf('MNU-%03d', $next);
    }
}
