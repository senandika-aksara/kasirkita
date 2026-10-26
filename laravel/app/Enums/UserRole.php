<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Manager = 'manager';
    case Cashier = 'kasir';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Superadmin',
            self::Manager => 'Manajer',
            self::Cashier => 'Kasir',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Superadmin => 'bg-brand-100 text-brand-700 border-brand-700/20',
            self::Manager => 'bg-nav-100 text-nav-700 border-nav-700/20',
            self::Cashier => 'bg-slate-100 text-ink-2 border-line',
        };
    }

    /** Peran yang boleh membuka panel manajer. */
    public function isManagerial(): bool
    {
        return $this === self::Superadmin || $this === self::Manager;
    }

    public static function options(): array
    {
        return array_map(
            fn (self $role) => ['value' => $role->value, 'label' => $role->label()],
            self::cases()
        );
    }
}
