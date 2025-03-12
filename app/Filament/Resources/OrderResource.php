<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_number')
                    ->required()
                    ->maxLength(255)
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($record) => $record !== null),
                Forms\Components\TextInput::make('customer_name')
                    ->maxLength(255),
                Forms\Components\Select::make('payment_method_id')
                    ->relationship('paymentMethod', 'name')
                    ->preload(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Section::make('Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('orderItems')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::query()->pluck('name', 'id'))
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            $set('unit_price', $product->price);
                                        }
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $unitPrice = $get('unit_price');
                                        $quantity = $get('quantity');
                                        $subtotal = $unitPrice * $quantity;
                                        $set('subtotal', $subtotal);
                                    }),
                                Forms\Components\TextInput::make('unit_price')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(true)
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $unitPrice = $get('unit_price');
                                        $quantity = $get('quantity');
                                        $subtotal = $unitPrice * $quantity;
                                        $set('subtotal', $subtotal);
                                    }),
                                Forms\Components\TextInput::make('subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(true),
                                Forms\Components\Textarea::make('notes')
                                    ->maxLength(65535),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::calculateTotals($set, $get);
                            }),
                    ])
                    ->columnSpanFull(),
                Forms\Components\Section::make('Amounts')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(true),
                        Forms\Components\TextInput::make('tax_amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::calculateFinalAmount($set, $get);
                            }),
                        Forms\Components\TextInput::make('discount_amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::calculateFinalAmount($set, $get);
                            }),
                        Forms\Components\TextInput::make('final_amount')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    private static function calculateTotals(Set $set, Get $get): void
    {
        $orderItems = $get('orderItems');
        
        if (!$orderItems) {
            $set('total_amount', 0);
            $set('final_amount', 0);
            return;
        }
        
        $totalAmount = collect($orderItems)
            ->reduce(function ($total, $item) {
                return $total + ($item['subtotal'] ?? 0);
            }, 0);
        
        $set('total_amount', $totalAmount);
        
        // Recalculate final amount
        self::calculateFinalAmount($set, $get);
    }
    
    private static function calculateFinalAmount(Set $set, Get $get): void
    {
        $totalAmount = $get('total_amount') ?? 0;
        $taxAmount = $get('tax_amount') ?? 0;
        $discountAmount = $get('discount_amount') ?? 0;
        
        $finalAmount = $totalAmount + $taxAmount - $discountAmount;
        $set('final_amount', $finalAmount);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->relationship('paymentMethod', 'name'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
    
    // Memastikan data tetap dihitung saat form disubmit
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Jika orderItems ada, hitung total dari subtotal
        if (isset($data['orderItems'])) {
            $totalAmount = collect($data['orderItems'])->sum('subtotal');
            $data['total_amount'] = $totalAmount;
            
            // Hitung final_amount
            $finalAmount = $totalAmount + ($data['tax_amount'] ?? 0) - ($data['discount_amount'] ?? 0);
            $data['final_amount'] = $finalAmount;
        }
        
        return $data;
    }
    
    public static function mutateFormDataBeforeUpdate(array $data): array
    {
        return static::mutateFormDataBeforeCreate($data);
    }
}