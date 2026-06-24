<?php

namespace App\Enums;

enum StatusSeleksi: string
{
    case BelumSeleksi = 'belum_seleksi';
    case Penyisihan = 'penyisihan';
    case TidakLolosFinal = 'tidak_lolos_final';
    case Final = 'final';

    /**
     * Statuses that represent an "active/qualified" team (for admin listing).
     *
     * @return list<string>
     */
    public static function qualified(): array
    {
        return [
            self::Penyisihan->value,
            self::Final->value,
        ];
    }

    /**
     * Get a human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::BelumSeleksi => 'Belum Seleksi',
            self::Penyisihan => 'Penyisihan',
            self::TidakLolosFinal => 'Tidak Lolos Final',
            self::Final => 'Final',
        };
    }
}
