<?php

namespace App\Filament\Resources\OnlineExamAttempts\Tables;

use App\Enums\AttemptStatus;
use App\Models\OnlineExamAttempt;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class OnlineExamAttemptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry.student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('entry.roll_no')
                    ->label('Roll No')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('exam.name')
                    ->label('Exam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('examSubject.subject.name')
                    ->label('Subject')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray'    => AttemptStatus::NOT_STARTED->value,
                        'info'    => AttemptStatus::IN_PROGRESS->value,
                        'success' => AttemptStatus::SUBMITTED->value,
                        'warning' => AttemptStatus::AUTO_SUBMITTED->value,
                        'danger'  => AttemptStatus::TERMINATED->value,
                    ])
                    ->icons([
                        AttemptStatus::NOT_STARTED->value => 'heroicon-o-clock',
                        AttemptStatus::IN_PROGRESS->value => 'heroicon-o-pencil',
                        AttemptStatus::SUBMITTED->value => 'heroicon-o-check-circle',
                        AttemptStatus::AUTO_SUBMITTED->value => 'heroicon-o-exclamation-triangle',
                        AttemptStatus::TERMINATED->value => 'heroicon-o-x-circle',
                    ])
                    ->sortable(),

                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Not started'),

                TextColumn::make('time_spent_display')
                    ->label('Time Spent')
                    ->getStateUsing(fn(OnlineExamAttempt $record) => gmdate('H:i:s', $record->time_spent))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('total_obtained')
                    ->label('Score')
                    ->formatStateUsing(fn($state, $record) => "{$state} / {$record->total_maximum}")
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => $record->percentage >= 40 ? 'success' : 'danger'),

                TextColumn::make('percentage')
                    ->label('%')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('started_at', 'desc')
            ->filters([
                SelectFilter::make('exam_id')
                    ->label('Exam')
                    ->relationship('exam', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('exam_subject_id')
                    ->label('Subject')
                    ->relationship('examSubject.subject', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(AttemptStatus::class),
            ])
            ->recordActions([
                // ViewAction::make(),
                // EditAction::make(),
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info'),

                    Action::make('recalculate')
                        ->label('Recalculate Score')
                        ->icon('heroicon-o-calculator')
                        ->color('warning')
                        ->visible(fn(OnlineExamAttempt $record) => in_array($record->status, [
                            AttemptStatus::SUBMITTED,
                            AttemptStatus::AUTO_SUBMITTED,
                        ]))
                        ->requiresConfirmation()
                        ->action(function (OnlineExamAttempt $record) {
                            $record->calculateAutoScore();
                            Notification::make()
                                ->title('Score recalculated')
                                ->success()
                                ->send();
                        }),

                    Action::make('terminate')
                        ->label('Terminate Attempt')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn(OnlineExamAttempt $record) => $record->status === AttemptStatus::IN_PROGRESS)
                        ->requiresConfirmation()
                        ->modalDescription('This will immediately end the attempt. Use for suspected cheating or technical failure.')
                        ->action(function (OnlineExamAttempt $record) {
                            $record->update([
                                'status' => AttemptStatus::TERMINATED,
                                'ended_at' => now(),
                            ]);
                            Notification::make()
                                ->title('Attempt terminated')
                                ->danger()
                                ->send();
                        }),

                    EditAction::make(),
                ])
                ->dropdownPlacement('bottom-start'),

            ])
            ->poll('30s') // live-refresh for monitoring active attempts
            ->emptyStateIcon('heroicon-o-computer-desktop')
            ->emptyStateHeading('No online attempts yet')
            ->emptyStateDescription('Attempts appear here once students start an online exam.');
    }
}
