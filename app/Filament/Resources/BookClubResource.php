<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookClubResource\Pages;
use App\Models\BookClub;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;

class BookClubResource extends Resource
{
    protected static ?string $model = BookClub::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Klub';

    protected static ?string $modelLabel = 'Klub';

    protected static ?string $pluralModelLabel = 'Klub';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi Klub')
                    ->schema([
                        Forms\Components\TextInput::make('nama_klub')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Klub'),
                        Forms\Components\TextInput::make('kategori')
                            ->required()
                            ->maxLength(255)
                            ->label('Kategori'),
                        Forms\Components\Select::make('id_owner')
                            ->relationship('owner', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Owner'),
                        Forms\Components\Select::make('admin_id')
                            ->relationship('admin', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Admin'),
                        Forms\Components\TextInput::make('member_count')
                            ->numeric()
                            ->default(0)
                            ->label('Jumlah Anggota'),
                        Forms\Components\Textarea::make('deskripsi')
                            ->required()
                            ->rows(3)
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Tampilan')
                    ->schema([
                        Forms\Components\FileUpload::make('foto_klub')
                            ->image()
                            ->directory('foto_klub')
                            ->disk('public')
                            ->label('Foto Klub'),
                        Forms\Components\ColorPicker::make('gradient_from')
                            ->default('#FFDDAF')
                            ->label('Gradient From'),
                        Forms\Components\ColorPicker::make('gradient_to')
                            ->default('#C7E7FF')
                            ->label('Gradient To'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto_klub')
                    ->disk('public')
                    ->circular()
                    ->label('Foto'),
                Tables\Columns\TextColumn::make('nama_klub')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Klub'),
                Tables\Columns\TextColumn::make('kategori')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('owner.name')
                    ->searchable()
                    ->sortable()
                    ->label('Owner'),
                Tables\Columns\TextColumn::make('member_count')
                    ->sortable()
                    ->label('Anggota'),
                Tables\Columns\ColorColumn::make('gradient_from')
                    ->label('Warna 1'),
                Tables\Columns\ColorColumn::make('gradient_to')
                    ->label('Warna 2'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable()
                    ->label('Dibuat'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                    ->options(fn () => BookClub::query()->distinct()->pluck('kategori', 'kategori')->toArray())
                    ->label('Kategori'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
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
