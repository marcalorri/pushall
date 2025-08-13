<?php

namespace App\Filament\Dashboard\Resources\WalletPassResource\RelationManagers;

use App\Jobs\SendPassNotificationJob;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'notifications';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->placeholder('e.g., Welcome Message')
                    ->maxLength(100),
                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->maxLength(100),
                Forms\Components\Textarea::make('message')
                    ->label('Message')
                    ->rows(4)
                    ->required(),
                Forms\Components\TextInput::make('button_text')
                    ->label('Button text')
                    ->maxLength(30),
                Forms\Components\TextInput::make('button_url')
                    ->label('Button URL')
                    ->url(),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Schedule')
                    ->seconds(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Name')->limit(30)->searchable(),
                Tables\Columns\TextColumn::make('title')->limit(30),
                Tables\Columns\TextColumn::make('message')->limit(50),
                Tables\Columns\BadgeColumn::make('status'),
                Tables\Columns\TextColumn::make('scheduled_at')->dateTime()->since(),
                Tables\Columns\TextColumn::make('sent_at')->dateTime()->since(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data, $livewire) {
                        // Ensure relation is set correctly
                        $data['wallet_pass_id'] = $livewire->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('send')
                    ->label('Send')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn ($record) => in_array($record->status, ['draft','failed']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['status' => 'queued']);
                        dispatch(new SendPassNotificationJob($record->id));
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
