<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Aberto = 'aberto';
    case Confirmado = 'confirmado';
    case Entregue = 'entregue';
    case Cancelado = 'cancelado';

    public function label(): string
    {
        return match ($this) {
            self::Aberto => 'Aberto',
            self::Confirmado => 'Confirmado',
            self::Entregue => 'Entregue',
            self::Cancelado => 'Cancelado',
        };
    }
}
