<?php

namespace App\Enums;

enum StatusPembayaran: string
{
    case Pending = 'pending';
    case Ditolak = 'ditolak';
    case Berhasil = 'berhasil';

    /**
     * Get a human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Ditolak => 'Ditolak',
            self::Berhasil => 'Berhasil',
        };
    }
}
