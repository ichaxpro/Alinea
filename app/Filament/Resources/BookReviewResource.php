<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookReviewResource\Pages;
use App\Models\BookReview;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Notifications\ContentHidden;

class BookReviewResource extends Resource
{
    /**
     * Create a new class instance.
     */
    protected static ?string $model = BookReview::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Book Reviews';

    protected static ?string $modelLabel = 'Review';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema {
        return $schema
            ->components([
                Section::make('Informasi Review')
                    ->schema([
                        TextInput::make('book_identifier')
                            ->disabled()
                            ->label('Book ID'),
                        TextInput::make('book_identifier_type')
                            ->disabled()
                            ->label('Tipe'),
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->label('User'),
                        TextInput::make('rating')
                            ->disabled()
                            ->label('Rating'),
                        Textarea::make('ulasan')
                            ->disabled()
                            ->rows(5)
                            ->label('Ulasan'),
                        TextInput::make('helpful')
                            ->disabled()
                            ->label('Membantu'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('User'),
                TextColumn::make('book_title')
                    ->searchable(query: function ($query, $search) {
                        $query->where('book_identifier', 'like', "%{$search}%");
                    })
                    ->label('Book'),
                TextColumn::make('book_identifier_type')
                    ->badge()
                    ->label('Tipe'),
                TextColumn::make('rating')
                    ->sortable()
                    ->label('Rating'),
                TextColumn::make('ulasan')
                    ->limit(60)
                    ->searchable()
                    ->label('Ulasan'),
                TextColumn::make('helpful')
                    ->sortable()
                    ->label('Membatu'),
                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->label('Dibuat'),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->options([
                        1 => '1 ★',
                        2 => '2 ★',
                        3 => '3 ★',
                        4 => '4 ★',
                        5 => '5 ★',
                    ]),
                SelectFilter::make('book_identifier_type')
                    ->options([
                        'db' => 'Database',
                        'google' => 'Google Books',
                    ]),
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make()
                    ->label('Sembunyikan')
                    ->icon('heroicon-o-eye-slash')
                    ->modalHeading('Sembunyikan Review Ini?')
                    ->modalDescription('Review ini akan disembunyikan dari aplikasi pengguna (Soft Delete).')
                    ->successNotificationTitle('Review berhasil disembunyikan')
                    ->after(function (BookReview $record) {
                        if ($record->user) {
                            $record->user->notify(new ContentHidden('Ulasan Anda untuk buku "' . $record->book_title . '" telah disembunyikan oleh admin karena melanggar panduan komunitas.', 'review_hidden'));
                        }
                    }),
                ForceDeleteAction::make(),
                RestoreAction::make()
                    ->successNotificationTitle('Review berhasil dipulihkan')
                    ->after(function (\App\Models\BookReview $record) {
                        if ($record->user) {
                            $record->user->notify(new \App\Notifications\ContentRestored('Ulasan Anda untuk buku "' . $record->book_title . '" telah dipulihkan oleh admin.'));
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array {
        return [];
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListBookReviews::route('/'),
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
