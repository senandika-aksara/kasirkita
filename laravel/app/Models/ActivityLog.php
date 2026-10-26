<?php

namespace App\Models;

use Database\Factories\ActivityLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    /** @use HasFactory<ActivityLogFactory> */
    use HasFactory;

    // created_at ikut fillable agar seeder bisa menaruh aktivitas pada jam yang sesuai
    // dengan transaksinya; tanpa itu fill() membuangnya diam-diam.
    protected $fillable = ['user_id', 'category', 'detail', 'status', 'created_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Mencatat satu baris aktivitas untuk ringkasan di dashboard. */
    public static function record(?User $user, string $category, string $detail, string $status = 'Sukses'): void
    {
        static::create([
            'user_id' => $user?->id,
            'category' => $category,
            'detail' => mb_substr($detail, 0, 150),
            'status' => $status,
        ]);
    }
}
