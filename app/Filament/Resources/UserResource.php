<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | \UnitEnum | null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'username', 'email'];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->disabled(),
                        TextInput::make('username')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled(),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled(),
                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->disabled(),
                        Select::make('role')
                            ->options([
                                'user' => 'User',
                                'admin' => 'Admin',
                            ])
                            ->default('user')
                            ->required(),
                    ])->columns(2),

                Section::make('Profil')
                    ->schema([
                        FileUpload::make('foto_profil')
                            ->image()
                            ->directory('foto_profil')
                            ->disk('public')
                            ->columnSpanFull()
                            ->disabled(),
                        TextInput::make('kota')
                            ->maxLength(255)
                            ->disabled(),
                        TextInput::make('no_telp')
                            ->tel()
                            ->maxLength(255)
                            ->disabled(),
                        Textarea::make('deskripsi')
                            ->rows(3)
                            ->columnSpanFull()
                            ->disabled(),
                        TagsInput::make('preferred_genres')
                            ->placeholder('Tambah genre')
                            ->columnSpanFull()
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto_profil')
                    ->disk('public')
                    ->circular()
                    ->label('Foto'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'user' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('kota')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('is_banned')
                    ->label('Diblokir')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'user' => 'User',
                        'admin' => 'Admin',
                    ]),
                TernaryFilter::make('is_banned')
                    ->label('Status Blokir')
                    ->placeholder('Semua')
                    ->trueLabel('Diblokir')
                    ->falseLabel('Aktif'),
            ])
            ->actions([
                Action::make('buka_blokir')
                    ->label('Buka Blokir')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Buka Blokir Pengguna?')
                    ->modalDescription('Pengguna ini akan dipulihkan dan dapat menggunakan platform secara normal.')
                    ->action(function (User $record) {
                        $record->update(['is_banned' => false]);
                        
                        Notification::make()
                            ->title('Blokir Pengguna Dibuka')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (User $record) => $record->is_banned),
                Action::make('blokir')
                    ->label('Blokir')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Blokir Pengguna?')
                    ->modalDescription('Pengguna ini akan diblokir dan tidak dapat mengirim pesan, memposting, atau berinteraksi.')
                    ->action(function (User $record) {
                        $record->update(['is_banned' => true]);
                        
                        Notification::make()
                            ->title('Pengguna Berhasil Diblokir')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (User $record) => !$record->is_banned && $record->id !== auth()->id()),
                ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
