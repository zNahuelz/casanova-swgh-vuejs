<?php

namespace App\Enums;

enum WorkerType: string
{
    case Nurse = 'ENFERMERA';
    case Secretary = 'SECRETARIA';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
