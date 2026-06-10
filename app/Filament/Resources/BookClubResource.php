<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookClubResource\Pages;
use App\Models\BookClub;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class BookClubResource extends Resource
{
    protected static ?string $model = BookClub::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Klub';

    protected static ?string $modelLabel = 'Klub';

    protected static ?string $pluralModelLabel = 'Klub';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nama_klub';

    public static function getGloballySearchableAttributes(): array
    {
        return ['nama_klub', 'deskripsi'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Klub')
                    ->schema([
                        TextInput::make('nama_klub')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Klub'),
                        TextInput::make('kategori')
                            ->required()
                            ->maxLength(255)
                            ->label('Kategori'),
                        Select::make('id_owner')
                            ->relationship('owner', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Owner'),
                        Select::make('admin_id')
                            ->relationship('admin', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Admin'),
                        TextInput::make('member_count')
                            ->numeric()
                            ->default(0)
                            ->label('Jumlah Anggota'),
                        Textarea::make('deskripsi')
                            ->required()
                            ->rows(3)
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Tampilan')
                    ->schema([
                        FileUpload::make('foto_klub')
                            ->image()
                            ->directory('foto_klub')
                            ->disk('public')
                            ->label('Foto Klub'),
                        ColorPicker::make('gradient_from')
                            ->default('#FFDDAF')
                            ->label('Gradient From'),
                        ColorPicker::make('gradient_to')
                            ->default('#C7E7FF')
                            ->label('Gradient To'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto_klub')
                    ->disk('public')
                    ->circular()
                    ->label('Foto'),
                TextColumn::make('nama_klub')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Klub'),
                TextColumn::make('kategori')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->label('Kategori'),
                TextColumn::make('owner.name')
                    ->searchable()
                    ->sortable()
                    ->label('Owner'),
                TextColumn::make('member_count')
                    ->sortable()
                    ->label('Anggota'),
                ColorColumn::make('gradient_from')
                    ->label('Warna 1'),
                ColorColumn::make('gradient_to')
                    ->label('Warna 2'),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable()
                    ->label('Dibuat'),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->options(fn () => BookClub::query()->distinct()->pluck('kategori', 'kategori')->toArray())
                    ->label('Kategori'),
            ])
            ->actions([
                EditAction::make(),
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
            'index' => Pages\ListBookClubs::route('/'),
            'create' => Pages\CreateBookClub::route('/create'),
            'edit' => Pages\EditBookClub::route('/{record}/edit'),
        ];
    }
}
