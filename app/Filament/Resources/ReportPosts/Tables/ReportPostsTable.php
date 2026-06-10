<?php

namespace App\Filament\Resources\ReportPosts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Notifications\Notification;
use App\Models\ReportPost;

class ReportPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('post.media')
                    ->disk('public')
                    ->label('Media')
                    ->square()
                    ->url(fn (ReportPost $record) => $record->post?->media ? asset('storage/' . $record->post->media) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('post.pesan')
                    ->label('Unggahan')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('post.author.name')
                    ->label('Pengunggah')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('reporter.name')
                    ->label('Pelapor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'resolved' => 'success',
                        'dismissed' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('take_down')
                    ->label('Tangguhkan')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tangguhkan Unggahan?')
                    ->modalDescription('Unggahan ini akan dihapus (soft delete) dan tidak dapat dilihat lagi oleh pengguna. Laporan akan ditandai sebagai resolved.')
                    ->action(function (ReportPost $record) {
                        if ($record->post) {
                            $author = $record->post->author;
                            $record->post->delete();
                            
                            if ($author) {
                                $author->notify(new \App\Notifications\PostSuspended());
                            }
                        }
                        $record->update(['status' => 'resolved']);
                        
                        Notification::make()
                            ->title('Unggahan Berhasil Dihapus')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (ReportPost $record) => $record->status === 'pending' && $record->post !== null && !$record->post->trashed()),
                    
                Action::make('tolak_laporan')
                    ->label('Tolak Laporan')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Laporan?')
                    ->modalDescription('Laporan ini akan ditandai sebagai dismissed dan diabaikan.')
                    ->action(function (ReportPost $record) {
                        $record->update(['status' => 'dismissed']);
                        
                        Notification::make()
                            ->title('Laporan Ditolak')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (ReportPost $record) => $record->status === 'pending'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
