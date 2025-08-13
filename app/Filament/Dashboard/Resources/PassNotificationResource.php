<?php
namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\PassNotificationResource\Pages;
use App\Filament\Dashboard\Resources\PassNotificationResource\RelationManagers;
use App\Models\PassNotification;
use App\Models\WalletPass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendPassNotificationJob;

class PassNotificationResource extends Resource
{
    protected static ?string $model = PassNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->placeholder('e.g., Welcome Message')
                    ->maxLength(100),
                Forms\Components\Select::make('wallet_pass_id')
                    ->label('Wallet Pass')
                    ->options(function () {
                        return WalletPass::query()
                            ->where('user_id', Auth::id())
                            ->get()
                            ->mapWithKeys(function ($p) {
                                $label = trim(($p->name ?: 'Untitled') . ' (' . $p->platform . ')');
                                return [$p->id => $label];
                            });
                    })
                    ->helperText('Choose the pass whose subscribers should receive this notification.')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->maxLength(100)
                    ->helperText('Optional title for the notification/card.'),
                Forms\Components\Textarea::make('message')
                    ->rows(4)
                    ->required()
                    ->helperText('Main content of the notification.'),
                Forms\Components\TextInput::make('button_text')
                    ->maxLength(30)
                    ->helperText('Optional button label (Google shows as a link).'),
                Forms\Components\TextInput::make('button_url')
                    ->url()
                    ->helperText('Optional URL to open when the button is tapped.'),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Schedule')
                    ->seconds(false)
                    ->helperText('Optional: schedule this notification for later.'),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'queued' => 'Queued',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ])
                    ->default('draft')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Name')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('walletPass.name')->label('Pass')->limit(30),
                Tables\Columns\TextColumn::make('title')->limit(30),
                Tables\Columns\TextColumn::make('message')->limit(50),
                Tables\Columns\BadgeColumn::make('status'),
                Tables\Columns\TextColumn::make('scheduled_at')->dateTime()->since(),
                Tables\Columns\TextColumn::make('sent_at')->dateTime()->since(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('send')
                    ->label('Send')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (PassNotification $record) => in_array($record->status, ['draft','failed']))
                    ->requiresConfirmation()
                    ->action(function (PassNotification $record) {
                        $record->update(['status' => 'queued']);
                        dispatch(new SendPassNotificationJob($record->id));
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
            'index' => Pages\ListPassNotifications::route('/'),
            'create' => Pages\CreatePassNotification::route('/create'),
            'edit' => Pages\EditPassNotification::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('walletPass', fn ($q) => $q->where('user_id', Auth::id()));
    }
}
