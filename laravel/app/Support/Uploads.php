<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Satu pintu untuk unggahan foto menu & pengguna.
 *
 * Disk diambil dari config('filesystems.uploads') sehingga aplikasi yang sama
 * bisa menyimpan ke folder lokal (XAMPP) atau ke Supabase Storage (Vercel)
 * hanya dengan mengubah variabel lingkungan UPLOAD_DISK.
 */
class Uploads
{
    public static function disk(): Filesystem
    {
        return Storage::disk(config('filesystems.uploads'));
    }

    /** Menyimpan berkas dan mengembalikan path relatifnya. */
    public static function store(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, config('filesystems.uploads'));
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            self::disk()->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        return $path ? self::disk()->url($path) : null;
    }
}
