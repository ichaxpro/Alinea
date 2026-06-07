<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeaturedBookResource\Pages;
use App\Models\FeaturedBook;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
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
                Section::make('Informasi Buku')
                    ->schema([
                        TextInput::make('judul')
                            ->required()
                            ->maxLength(255)
                            ->label('Judul'),
                        TextInput::make('penulis')
                            ->required()
                            ->maxLength(255)
                            ->label('Penulis'),
                        TextInput::make('penerbit')
                            ->maxLength(255)
                            ->label('Penerbit'),
                        TextInput::make('isbn')
                            ->maxLength(255)
                            ->label('ISBN'),
                        TextInput::make('tahun')
                            ->numeric()
                            ->label('Tahun Terbit'),
                        TextInput::make('jumlah_halaman')
                            ->numeric()
                            ->label('Jumlah Halaman'),
                        TextInput::make('bahasa')
                            ->default('Indonesia')
                            ->maxLength(255)
                            ->label('Bahasa'),
                        TextInput::make('kategori')
                            ->maxLength(255)
                            ->label('Kategori'),
                        TextInput::make('status')
                            ->disabled()
                            ->label('Status'),
                    ])->columns(2),

                Section::make('Deskripsi')
                    ->schema([
                        Textarea::make('sinopsis')
                            ->rows(4)
                            ->label('Sinopsis')
                            ->columnSpanFull(),
                        TagsInput::make('genres')
                            ->placeholder('Tambah genre')
                            ->label('Genres')
                            ->columnSpanFull(),
                    ]),

                Section::make('Tampilan')
                    ->schema([
                        FileUpload::make('cover_url')
                            ->image()
                            ->directory('featured_books')
                            ->disk('public')
                            ->label('Cover Buku')
                            ->columnSpanFull(),
                        ColorPicker::make('gradient_from')
                            ->default('#C7E7FF')
                            ->label('Gradient From'),
                        ColorPicker::make('gradient_to')
                            ->default('#FFDDAF')
                            ->label('Gradient To'),
                    ])->columns(2),

                Section::make('Rating')
                    ->schema([
                        TextInput::make('rating_avg')
                            ->disabled()
                            ->dehydrated(false)
                            ->label('Rating Rata-rata'),
                        TextInput::make('rating_count')
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
                ImageColumn::make('cover_url')
                    ->disk('public')
                    ->label('Cover')
                    ->width(50)
                    ->height(70),
                TextColumn::make('judul')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->label('Judul'),
                TextColumn::make('penulis')
                    ->searchable()
                    ->sortable()
                    ->label('Penulis'),
                TextColumn::make('tahun')
                    ->sortable()
                    ->label('Tahun'),
                TextColumn::make('kategori')
                    ->searchable()
                    ->badge()
                    ->label('Kategori')
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tersedia' => 'success',
                        'dipinjam' => 'danger',
                        'tidak_tersedia' => 'gray',
                        default => 'gray',
                    })
                    ->label('Status'),
                TextColumn::make('rating_avg')
                    ->sortable()
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' ⭐' : '-'),
                TextColumn::make('rating_count')
                    ->sortable()
                    ->label('Reviews')
                    ->toggleable(),
                ColorColumn::make('gradient_from')
                    ->label('Warna 1')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable()
                    ->label('Dibuat'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'tersedia' => 'Tersedia',
                        'dipinjam' => 'Dipinjam',
                        'tidak_tersedia' => 'Tidak Tersedia',
                    ]),
                SelectFilter::make('kategori')
                    ->options(fn () => FeaturedBook::query()->whereNotNull('kategori')->distinct()->pluck('kategori', 'kategori')->toArray()),
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
            'index' => Pages\ListFeaturedBooks::route('/'),
            'create' => Pages\CreateFeaturedBook::route('/create'),
            'edit' => Pages\EditFeaturedBook::route('/{record}/edit'),
        ];
    }
}
