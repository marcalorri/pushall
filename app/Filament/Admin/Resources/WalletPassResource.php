<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WalletPassResource\Pages;
use App\Filament\Admin\Resources\WalletPassResource\RelationManagers;
use App\Models\WalletPass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Jobs\ApplePushJob;
use App\Jobs\GoogleUpdateJob;

class WalletPassResource extends Resource
{
    protected static ?string $model = WalletPass::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('platform')
                    ->options([
                        'apple' => 'Apple',
                        'google' => 'Google',
                    ])
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'generic' => 'Generic',
                        'loyalty' => 'Loyalty',
                        'offer' => 'Offer',
                        'event' => 'Event',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('meta')
                    ->label('Metadata (JSON)')
                    ->rows(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('platform')->colors([
                    'primary',
                    'success' => 'apple',
                    'warning' => 'google',
                ])->sortable(),
                Tables\Columns\TextColumn::make('type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('serial_number')->label('Serial')->toggleable(),
                Tables\Columns\TextColumn::make('object_id')->label('Object')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->since()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('updatePass')
                    ->label('Update')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function (WalletPass $record) {
                        if ($record->platform === 'apple') {
                            dispatch(new ApplePushJob($record->id));
                        } else {
                            dispatch(new GoogleUpdateJob($record->id));
                        }
                    }),
                Tables\Actions\Action::make('revoke')
                    ->label('Revoke')
                    ->color('danger')
                    ->icon('heroicon-o-no-symbol')
                    ->requiresConfirmation()
                    ->action(function (WalletPass $record) {
                        $record->update(['status' => 'revoked']);
                    }),
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
            'index' => Pages\ListWalletPasses::route('/'),
            'create' => Pages\CreateWalletPass::route('/create'),
            'edit' => Pages\EditWalletPass::route('/{record}/edit'),
        ];
    }
}
