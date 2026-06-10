<?php

namespace App\Filament\Resources\Reports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Models\Report;
use App\Notifications\UserWarning;
use App\Notifications\UserBanned;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reporter.name')
                    ->label('Pelapor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('reportedUser.name')
                    ->label('Dilaporkan')
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
                        default => 'gray',
                    }),
                TextColumn::make('reportedUser.sp_count')
                    ->label('SP')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state >= 3 ? 'danger' : 'warning'),
                IconColumn::make('reportedUser.is_banned')
                    ->label('Diblokir')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('beri_peringatan')
                    ->label('Beri Peringatan')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Surat Peringatan (SP)')
                    ->modalDescription('Pesan peringatan ini akan dikirimkan ke kotak masuk notifikasi pengguna yang dilaporkan.')
                    ->form([
                        Textarea::make('warning_message')
                            ->label('Pesan Peringatan')
                            ->required()
                            ->rows(4)
                            ->default('Anda telah melanggar panduan komunitas kami. Mohon perhatikan interaksi Anda agar tidak diblokir secara permanen.'),
                    ])
                    ->action(function (Report $record, array $data) {
                        $user = $record->reportedUser;
                        
                        // Increment SP count up to 3
                        if ($user->sp_count < 3) {
                            $user->increment('sp_count');
                        }
                        
                        $user->notify(new UserWarning($data['warning_message']));
                        $record->update(['status' => 'resolved']);
                        
                        Notification::make()
                            ->title('Peringatan Berhasil Dikirim (SP ' . $user->sp_count . ')')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Report $record) => $record->status === 'pending' && $record->reportedUser->sp_count < 3 && !$record->reportedUser->is_banned),

                Action::make('blokir_pengguna')
                    ->label('Blokir Pengguna')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Blokir Pengguna Ini?')
                    ->modalDescription('Pengguna akan diblokir dari platform (read-only).')
                    ->action(function (Report $record) {
                        $user = $record->reportedUser;
                        $user->update(['is_banned' => true]);
                        $record->update(['status' => 'resolved']);
                        
                        $user->notify(new UserBanned());
                        
                        Notification::make()
                            ->title('Pengguna Berhasil Diblokir')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Report $record) => !$record->reportedUser->is_banned),
                    
                Action::make('tolak_laporan')
                    ->label('Tolak Laporan')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Laporan?')
                    ->modalDescription('Laporan ini akan ditandai sebagai dismissed dan diabaikan.')
                    ->action(function (Report $record) {
                        $record->update(['status' => 'dismissed']);
                        
                        Notification::make()
                            ->title('Laporan Ditolak')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Report $record) => $record->status === 'pending'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
