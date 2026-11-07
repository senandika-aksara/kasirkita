<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'aktif';
    case Inactive = 'non_aktif';

    public function label(): string
    {
        return $this === self::Active ? 'Aktif' : 'Non-Aktif';
    }

    public function badgeClass(): string
    {
        return $this === self::Active
            ? 'bg-brand-100 text-brand-700 border-brand-700/20'
            : 'bg-warn-100 text-warn-700 border-warn-700/20';
    }
}
