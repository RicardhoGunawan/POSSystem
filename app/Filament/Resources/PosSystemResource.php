<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PosSystemResource extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'POS System';
    protected static ?string $title = 'Point of Sale';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.pos-system';
}