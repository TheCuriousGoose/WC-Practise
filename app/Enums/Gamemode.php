<?php

namespace App\Enums;

enum Gamemode: string
{
    case FREEHAND = 'freehand';
    case RETRACE = 'retrace';

    public function value()
    {
        return $this->value;
    }

    public static function values()
    {
        return array_column(self::cases(), 'value');
    }
}
