<?php

namespace App\Enums;

enum MenuStatus: string
{
    case Available = 'tersedia';
    case SoldOut = 'habis';

    public function label(): string
    {
        return $this === self::Available ? 'Tersedia' : 'Habis';
    }

    public function badgeClass(): string
    {
        return $this === self::Available
            ? 'bg-brand-100 text-brand-700 border-brand-700/20'
            : 'bg-warn-100 text-warn-700 border-warn-700/20';
    }
}
