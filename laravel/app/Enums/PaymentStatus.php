<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Paid = 'lunas';
    case Unpaid = 'belum_bayar';

    public function label(): string
    {
        return $this === self::Paid ? 'Lunas' : 'Belum Bayar';
    }

    public function badgeClass(): string
    {
        return $this === self::Paid
            ? 'bg-brand-100 text-brand-700 border-brand-700/30'
            : 'bg-danger-100 text-danger-700 border-danger-700/30';
    }
}
