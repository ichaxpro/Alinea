<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimelinePostResource\Pages;
use App\Models\TimelinePost;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

class TimelinePostResource extends Resource
{
    protected static ?string $model = TimelinePost::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Timeline Posts';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Detail Post')
                    ->schema([
                        Forms\Components\Select::make('id_user')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Author'),
                        Forms\Components\Select::make('id_klub')
                            ->relationship('club', 'nama_klub')
                            ->searchable()
                            ->preload()
                            ->label('Klub')
                            ->nullable(),
                        Forms\Components\TextInput::make('judul_buku_dibahas')
                            ->maxLength(120)
                            ->label('Judul Buku Dibahas'),
                        Forms\Components\Select::make('tag')
                            ->options([
                                'review' => 'Review',
                                'diskusi' => 'Diskusi',
                                'rekomendasi' => 'Rekomendasi',
                                'kutipan' => 'Kutipan',
                            ])
                            ->label('Tag'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Konten')
                    ->schema([
                        Forms\Components\RichEditor::make('pesan')
                            ->required()
                            ->label('Pesan')
                            ->columnSpanFull(),
                    ]),

                \Filament\Schemas\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('media')
                            ->image()
                            ->disk('public')
                            ->directory('timeline_media')
                            ->visibility('public')
                            ->label('File Media'),
                        Forms\Components\TextInput::make('media_type')
                            ->maxLength(255)
                            ->label('Tipe Media'),
                        Forms\Components\TextInput::make('media_original_name')
                            ->maxLength(255)
                            ->label('Nama File Asli'),
                        Forms\Components\TextInput::make('media_size')
                            ->numeric()
                            ->label('Ukuran Media (Bytes)'),
                    ])->columns(2)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('ID'),
                ImageColumn::make('media')
                    ->disk('public')
                    ->label('Media')
                    ->square()
                    ->defaultImageUrl(url('/images/no-image.png')),
                Tables\Columns\TextColumn::make('author.name')
                    ->searchable()
                    ->sortable()
                    ->label('Author'),
                Tables\Columns\TextColumn::make('judul_buku_dibahas')
                    ->searchable()
                    ->limit(30)
                    ->label('Buku Dibahas')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pesan')
                    ->limit(50)
                    ->label('Pesan')
                    ->html(),
                Tables\Columns\TextColumn::make('tag')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'review' => 'info',
                        'diskusi' => 'success',
                        'rekomendasi' => 'warning',
                        'kutipan' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('club.nama_klub')
                    ->label('Klub')
                    ->toggleable()
                    ->default('-'),
                Tables\Columns\TextColumn::make('comments_count')
                    ->counts('comments')
                    ->label('Komentar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('likes_count')
                    ->counts('likes')
                    ->label('Likes')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->label('Dibuat'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tag')
                    ->options([
                        'review' => 'Review',
                        'diskusi' => 'Diskusi',
                        'rekomendasi' => 'Rekomendasi',
                        'kutipan' => 'Kutipan',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
                \Filament\Actions\ForceDeleteAction::make(),
                \Filament\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListTimelinePosts::route('/'),
            'create' => Pages\CreateTimelinePost::route('/create'),
            'edit' => Pages\EditTimelinePost::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}
