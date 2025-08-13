<?php

namespace App\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\WalletPassResource\Pages;
use App\Filament\Dashboard\Resources\WalletPassResource\RelationManagers;
use App\Models\WalletPass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Services\Wallet\GooglePassService;
use App\Jobs\ApplePushJob;
use App\Jobs\GoogleUpdateJob;
use App\Filament\Dashboard\Resources\WalletPassResource\RelationManagers\NotificationsRelationManager;

class WalletPassResource extends Resource
{
    protected static ?string $model = WalletPass::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->placeholder('e.g., My Apple Pass or My Google Pass')
                    ->maxLength(100)
                    ->required(),
                Forms\Components\Select::make('platform')
                    ->options([
                        'apple' => 'Apple',
                        'google' => 'Google',
                    ])
                    ->default(fn () => self::detectPlatform())
                    ->required()
                    ->rules(fn () => [
                        Rule::unique('wallet_passes', 'platform')
                            ->where(fn ($q) => $q->where('user_id', Auth::id())),
                    ])
                    ->helperText('We will pick the best platform for your device, but you can override it.'),
                Forms\Components\Select::make('type')
                    ->options([
                        'generic' => 'Generic',
                        'loyalty' => 'Loyalty',
                        'offer' => 'Offer',
                        'event' => 'Event',
                    ])
                    ->required(),
                Forms\Components\FileUpload::make('image_path')
                    ->label('Custom image')
                    ->image()
                    ->directory('passes/images')
                    ->imageEditor()
                    ->helperText('Optional image to personalize your pass (logo or header).'),
                Forms\Components\Fieldset::make('Assets')
                    ->schema([
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->directory('passes/assets')
                            ->imageEditor()
                            ->helperText('Appears as the main logo; prefer transparent PNG.'),
                        Forms\Components\FileUpload::make('strip_path')
                            ->label('Strip')
                            ->image()
                            ->directory('passes/assets')
                            ->imageEditor()
                            ->helperText('Wide header/strip image for the pass card.'),
                        Forms\Components\FileUpload::make('background_path')
                            ->label('Background')
                            ->image()
                            ->directory('passes/assets')
                            ->imageEditor()
                            ->helperText('Background image; ensure good contrast with text.'),
                        Forms\Components\FileUpload::make('thumbnail_path')
                            ->label('Thumbnail')
                            ->image()
                            ->directory('passes/assets')
                            ->imageEditor()
                            ->helperText('Small square thumbnail for lists/previews.'),
                        Forms\Components\FileUpload::make('icon_path')
                            ->label('Icon')
                            ->image()
                            ->directory('passes/assets')
                            ->imageEditor()
                            ->helperText('Icon used in compact views; prefer square PNG.'),
                    ])
                    ->columns(2),
                // Welcome notification fields removed; use PassNotification resource instead.
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Name')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('platform')->badge(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('serial_number')->label('Serial/Object')
                    ->getStateUsing(fn (WalletPass $record) => $record->platform === 'apple' ? ($record->serial_number ?? '-') : ($record->object_id ?? '-'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->since(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('share')
                    ->label('Share')
                    ->icon('heroicon-o-qr-code')
                    ->modalHeading('Share this pass')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->form([
                        Forms\Components\Placeholder::make('qr')
                            ->label('Scan to open')
                            ->content(function (WalletPass $record) {
                                $url = route('public.pass.smart', $record);
                                $qr = 'https://quickchart.io/qr?size=240&text=' . urlencode($url);
                                return new \Illuminate\Support\HtmlString(
                                    '<div style="text-align:center">'
                                    . '<img alt="QR code" width="240" height="240" src="' . $qr . '" />'
                                    . '<div style="margin-top:10px;font-size:12px;color:#64748b">Scan to add</div>'
                                    . '</div>'
                                );
                            })
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('share_url')
                            ->label('Public URL')
                            ->default(fn (WalletPass $record) => route('public.pass.smart', $record))
                            ->extraInputAttributes(['readonly' => true])
                            ->suffixIcon('heroicon-o-clipboard')
                            ->helperText('Copy this URL or scan the QR code.')
                            ->columnSpanFull(),
                    ]),
                Tables\Actions\Action::make('downloadApple')
                    ->label('Download Pass')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (WalletPass $record) => $record->platform === 'apple')
                    ->url(fn (WalletPass $record) => route('wallet.apple.download', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('addToGoogleWallet')
                    ->label('Add to Google Wallet')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->visible(fn (WalletPass $record) => $record->platform === 'google')
                    ->url(function (WalletPass $record) {
                        return app(GooglePassService::class)->getAddToWalletUrl($record);
                    })
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('sendUpdate')
                    ->label('Send Update')
                    ->icon('heroicon-o-bell-alert')
                    ->requiresConfirmation()
                    ->action(function (WalletPass $record) {
                        if ($record->platform === 'apple') {
                            dispatch(new ApplePushJob($record->id));
                        } else {
                            dispatch(new GoogleUpdateJob($record->id));
                        }
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
            NotificationsRelationManager::class,
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

    /**
     * Scope queries to the authenticated user for the dashboard panel.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    /**
     * Detect platform from the current User-Agent.
     */
    protected static function detectPlatform(): string
    {
        $ua = request()->header('User-Agent', '');
        $ua = Str::of($ua)->lower();
        if ($ua->contains('iphone') || $ua->contains('ipad') || $ua->contains('ipod') || $ua->contains('ios') || $ua->contains('safari') && $ua->contains('mobile')) {
            return 'apple';
        }
        if ($ua->contains('android') || $ua->contains('linux;') || $ua->contains('crandroid')) {
            return 'google';
        }
        // Default to Apple (safer for .pkpass); user can change
        return 'apple';
    }
}
