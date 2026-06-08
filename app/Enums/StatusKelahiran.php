<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusKelahiran: string
{
    case HIDUP = 'HIDUP';
    case MATI = 'MATI';
}
