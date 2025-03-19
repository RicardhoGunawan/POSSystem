<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreSettingResource\Pages;
use App\Filament\Resources\StoreSettingResource\RelationManagers;
use App\Models\StoreSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StoreSettingResource extends Resource
{
    protected static ?string $model = StoreSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('store_name')
                    ->label('Store Name')
                    ->required(),
                Forms\Components\TextInput::make('tax_percentage')
                    ->label('Tax Percentage (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0)
                    ->helperText('Masukkan nilai dalam persen. Contoh: 10 untuk 10%'),
                Forms\Components\TextInput::make('address_line_1')
                    ->label('Address Line 1'),
                Forms\Components\TextInput::make('address_line_2')
                    ->label('Address Line 2'),
                Forms\Components\TextInput::make('phone')
                    ->label('Phone Number'),
                Forms\Components\Textarea::make('footer_message')
                    ->label('Footer Message'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store_name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax_percentage')
                    ->label('Tax (%)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoreSettings::route('/'),
            'create' => Pages\CreateStoreSetting::route('/create'),
            'edit' => Pages\EditStoreSetting::route('/{record}/edit'),
        ];
    }
}
