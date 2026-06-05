<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeaturedBookResource\Pages;
use App\Models\FeaturedBook;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;

class FeaturedBookResource extends Resource
{
    protected static ?string $model = FeaturedBook::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Featured Books';

    protected static ?string $modelLabel = 'Featured Book';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi Buku')
                    ->schema([
                        Forms\Components\TextInput::make('judul')
                            ->required()
                            ->maxLength(255)
                            ->label('Judul'),
                        Forms\Components\TextInput::make('penulis')
                            ->required()
                            ->maxLength(255)
                            ->label('Penulis'),
                        Forms\Components\TextInput::make('penerbit')
                            ->maxLength(255)
                            ->label('Penerbit'),
                        Forms\Components\TextInput::make('isbn')
                            ->maxLength(255)
                            ->label('ISBN'),
                        Forms\Components\TextInput::make('tahun')
                            ->numeric()
                            ->label('Tahun Terbit'),
                        Forms\Components\TextInput::make('jumlah_halaman')
                            ->numeric()
                            ->label('Jumlah Halaman'),
                        Forms\Components\TextInput::make('bahasa')
                            ->default('Indonesia')
                            ->maxLength(255)
                            ->label('Bahasa'),
                        Forms\Components\TextInput::make('kategori')
                            ->maxLength(255)
                            ->label('Kategori'),
                        Forms\Components\TextInput::make('status')
                            ->disabled()
                            ->label('Status'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Deskripsi')
                    ->schema([
                        Forms\Components\Textarea::make('sinopsis')
                            ->rows(4)
                            ->label('Sinopsis')
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('genres')
                            ->placeholder('Tambah genre')
                            ->label('Genres')
                            ->columnSpanFull(),
                    ]),

                \Filament\Schemas\Components\Section::make('Tampilan')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_url')
                            ->image()
                            ->directory('featured_books')
                            ->disk('public')
                            ->label('Cover Buku')
                            ->columnSpanFull(),
                        Forms\Components\ColorPicker::make('gradient_from')
                            ->default('#C7E7FF')
                            ->label('Gradient From'),
                        Forms\Components\ColorPicker::make('gradient_to')
                            ->default('#FFDDAF')
                            ->label('Gradient To'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Rating')
                    ->schema([
                        Forms\Components\TextInput::make('rating_avg')
                            ->disabled()
                            ->dehydrated(false)
                            ->label('Rating Rata-rata'),
                        Forms\Components\TextInput::make('rating_count')
                            ->disabled()
                            ->dehydrated(false)
                            ->label('Jumlah Rating'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_url')
                    ->disk('public')
                    ->label('Cover')
                    ->width(50)
                    ->height(70),
                Tables\Columns\TextColumn::make('judul')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->label('Judul'),
                Tables\Columns\TextColumn::make('penulis')
                    ->searchable()
                    ->sortable()
                    ->label('Penulis'),
                Tables\Columns\TextColumn::make('tahun')
                    ->sortable()
                    ->label('Tahun'),
                Tables\Columns\TextColumn::make('kategori')
                    ->searchable()
                    ->badge()
                    ->label('Kategori')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tersedia' => 'success',
                        'dipinjam' => 'danger',
                        'tidak_tersedia' => 'gray',
                        default => 'gray',
                    })
                    ->label('Status'),
                Tables\Columns\TextColumn::make('rating_avg')
                    ->sortable()
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' ⭐' : '-'),
                Tables\Columns\TextColumn::make('rating_count')
                    ->sortable()
                    ->label('Reviews')
                    ->toggleable(),
                Tables\Columns\ColorColumn::make('gradient_from')
                    ->label('Warna 1')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable()
                    ->label('Dibuat'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'tersedia' => 'Tersedia',
                        'dipinjam' => 'Dipinjam',
                        'tidak_tersedia' => 'Tidak Tersedia',
                    ]),
                Tables\Filters\SelectFilter::make('kategori')
                    ->options(fn () => FeaturedBook::query()->whereNotNull('kategori')->distinct()->pluck('kategori', 'kategori')->toArray()),
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
            'index' => Pages\ListFeaturedBooks::route('/'),
            'create' => Pages\CreateFeaturedBook::route('/create'),
            'edit' => Pages\EditFeaturedBook::route('/{record}/edit'),
        ];
    }
}
