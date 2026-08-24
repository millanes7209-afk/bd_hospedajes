<?php

namespace App\Enums;

enum DiaSemana: string
{
    case LUNES = 'LUNES';
    case MARTES = 'MARTES';
    case MIERCOLES = 'MIERCOLES';
    case JUEVES = 'JUEVES';
    case VIERNES = 'VIERNES';
    case SABADO = 'SABADO';
    case DOMINGO = 'DOMINGO';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getDiaActualEnEspanol($date = null): string
    {
        $diasIndex = [
            0 => self::DOMINGO->value,
            1 => self::LUNES->value,
            2 => self::MARTES->value,
            3 => self::MIERCOLES->value,
            4 => self::JUEVES->value,
            5 => self::VIERNES->value,
            6 => self::SABADO->value,
        ];

        $w = (int) date('w', $date ? strtotime($date) : time());
        return $diasIndex[$w] ?? self::DOMINGO->value;
    }
}
