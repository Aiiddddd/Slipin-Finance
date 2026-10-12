<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class Fmt
{
    public static function rp($n): string
    {
        $n = (float) $n;
        return ($n < 0 ? '-' : '') . 'Rp ' . number_format(abs($n), 0, ',', '.');
    }

    public static function num($n): string
    {
        return number_format((float) $n, 0, ',', '.');
    }

    public static function tgl($d, string $fmt = 'd M Y'): string
    {
        return Carbon::parse($d)->locale('id')->translatedFormat($fmt);
    }

    /** Selisih bertanda: +5 / -3 / 0 */
    public static function sel($n): string
    {
        $n = (int) round((float) $n);
        return $n > 0 ? '+' . self::num($n) : ($n < 0 ? '-' . self::num(abs($n)) : '0');
    }

    public static function selRp($n): string
    {
        $n = (int) round((float) $n);
        return $n > 0 ? '+' . self::rp($n) : self::rp($n);
    }

    /** Warna teks utk selisih: 0 = hijau, selain itu = merah */
    public static function selClass($n): string
    {
        return ((int) round((float) $n)) === 0 ? 'text-emerald-400' : 'text-red-400';
    }
}
