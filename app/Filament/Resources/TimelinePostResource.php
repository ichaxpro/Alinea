<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimelinePostResource\Pages;
use App\Models\TimelinePost;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Notifications\PostHidden;

class TimelinePostResource extends Resource
{
    protected static ?string $model = TimelinePost::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Timeline Posts';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Post')
                    ->schema([
                        Select::make('id_user')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Author'),
                        Select::make('id_klub')
                            ->relationship('club', 'nama_klub')
                            ->searchable()
                            ->preload()
                            ->label('Klub')
                            ->nullable(),
                        TextInput::make('judul_buku_dibahas')
                            ->maxLength(120)
                            ->label('Judul Buku Dibahas'),
                        Select::make('tag')
                            ->options([
                                'review' => 'Review',
                                'diskusi' => 'Diskusi',
                                'rekomendasi' => 'Rekomendasi',
                                'kutipan' => 'Kutipan',
                            ])
                            ->label('Tag'),
                    ])->columns(2),

                Section::make('Konten')
                    ->schema([
                        RichEditor::make('pesan')
                            ->required()
                            ->label('Pesan')
                            ->columnSpanFull(),
                    ]),

                Section::make('Media')
                    ->schema([
                        FileUpload::make('media')
                            ->image()
                            ->disk('public')
                            ->directory('timeline_media')
                            ->visibility('public')
                            ->label('File Media'),
                        TextInput::make('media_type')
                            ->maxLength(255)
                            ->label('Tipe Media'),
                        TextInput::make('media_original_name')
                            ->maxLength(255)
                            ->label('Nama File Asli'),
                        TextInput::make('media_size')
                            ->numeric()
                            ->label('Ukuran Media (Bytes)'),
                    ])->columns(2)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->label('ID'),
                ImageColumn::make('media')
                    ->disk('public')
                    ->label('Media')
                    ->square(),
                TextColumn::make('author.name')
                    ->searchable()
                    ->sortable()
                    ->label('Author'),
                TextColumn::make('judul_buku_dibahas')
                    ->searchable()
                    ->limit(30)
                    ->label('Buku Dibahas')
                    ->toggleable(),
                TextColumn::make('pesan')
                    ->formatStateUsing(fn (string $state): string => \Illuminate\Support\Str::limit(strip_tags($state), 40))
                    ->label('Pesan')
                    ->searchable(),
                TextColumn::make('tag')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'review' => 'info',
                        'diskusi' => 'success',
                        'rekomendasi' => 'warning',
                        'kutipan' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('club.nama_klub')
                    ->label('Klub')
                    ->toggleable()
                    ->default('-'),
                TextColumn::make('comments_count')
                    ->counts('comments')
                    ->label('Komentar')
                    ->sortable(),
                TextColumn::make('likes_count')
                    ->counts('likes')
                    ->label('Likes')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->label('Dibuat'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tag')
                    ->options([
                        'review' => 'Review',
                        'diskusi' => 'Diskusi',
                        'rekomendasi' => 'Rekomendasi',
                        'kutipan' => 'Kutipan',
                    ]),
                \Filament\Tables\Filters\TernaryFilter::make('has_media')
                    ->label('Media Lampiran')
                    ->placeholder('Semua Post')
                    ->trueLabel('Hanya dengan Media')
                    ->falseLabel('Tanpa Media')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('media'),
                        false: fn (Builder $query) => $query->whereNull('media'),
                        blank: fn (Builder $query) => $query,
                    ),
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make()
                    ->label('Sembunyikan')
                    ->icon('heroicon-o-eye-slash')
                    ->modalHeading('Sembunyikan Post Ini?')
                    ->modalDescription('Post ini akan disembunyikan dari timeline pengguna (Soft Delete).')
                    ->successNotificationTitle('Post berhasil disembunyikan')
                    ->after(function (TimelinePost $record) {
                        $record->author->notify(new PostHidden());
                    }),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
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
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
