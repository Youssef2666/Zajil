<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('مجموع المستخدمين', User::count()),
            Stat::make('مجموع المتاجر', Store::count()),
            Stat::make('مجموع الطلبات', Order::count()),
            Stat::make('مجموع المنتجات', Product::count()),

        ];
    }
}
