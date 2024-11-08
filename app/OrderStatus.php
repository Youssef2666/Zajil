<?php

namespace App;

use ReflectionEnum;
enum OrderStatus : string
{
    case INTRANSIT = 'in-transit';
    case DELIVERED = 'delivered';
    case CANCELED = 'canceled';

    public static function values(): array
    {
        return array_column((new ReflectionEnum(self::class))->getCases(), 'name');
    }

    public function label(): string
    {
        return match ($this) {
            self::DELIVERED => 'Delivered',
            self::INTRANSIT => 'In-Transit',
            self::CANCELED => 'Canceled',
        };
    }
}
