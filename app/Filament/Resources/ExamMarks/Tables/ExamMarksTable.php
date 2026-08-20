<?php

namespace App\Filament\Resources\ExamMarks\Tables;

use App\Enums\GradeScale;
use App\Models\ExamMark;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ExamMarksTable
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
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('exam.name')
                    ->label('Exam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('examSubject.subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_obtained')
                    ->label('Marks')
                    ->formatStateUsing(fn($state, $record) => "{$state} / {$record->total_maximum}")
                    ->sortable()
                    ->badge()
                    ->color(fn(ExamMark $record) => $record->is_passed ? 'success' : 'danger'),

                TextColumn::make('percentage')
                    ->label('%')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('grade')
                    ->badge()
                    ->label('Grade')
                    ->colors([
                        'success' => ['A+', 'A'],
                        'info'    => ['B+', 'B'],
                        'warning' => ['C+', 'C'],
                        'gray'    => ['D'],
                        'danger'  => ['F'],
                    ]),

                TextColumn::make('source')
                    ->label('Source')
                    ->badge(),

                IconColumn::make('is_locked')
                    ->label('Locked')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('grader.name')
                    ->label('Graded By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
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

                SelectFilter::make('grade')
                    ->label('Grade')
                    ->options(GradeScale::forFilamentSelect()),

                TernaryFilter::make('is_locked')
                    ->label('Lock Status')
                    ->trueLabel('Locked')
                    ->falseLabel('Unlocked'),

                TernaryFilter::make('is_passed')
                    ->label('Result')
                    ->trueLabel('Passed')
                    ->falseLabel('Failed'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info'),

                    EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->visible(fn(ExamMark $record) => $record->canBeEditedBy(Auth::user())),

                    Action::make('lock')
                        ->label('Lock Marks')
                        ->icon('heroicon-o-lock-closed')
                        ->color('danger')
                        ->visible(fn(ExamMark $record) => !$record->is_locked
                            && (Auth::user()?->hasAnyRole(['super_admin', 'admin', 'principal']) ?? false))
                        ->requiresConfirmation()
                        ->modalDescription('Once locked, this mark cannot be edited by teachers.')
                        ->action(function (ExamMark $record) {
                            $record->update(['is_locked' => true]);
                            Notification::make()->title('Marks locked')->success()->send();
                        }),

                    Action::make('unlock')
                        ->label('Unlock Marks')
                        ->icon('heroicon-o-lock-open')
                        ->color('warning')
                        ->visible(fn(ExamMark $record) => $record->is_locked
                            && (Auth::user()?->hasRole('super_admin') ?? false))
                        ->requiresConfirmation()
                        ->action(function (ExamMark $record) {
                            $record->update(['is_locked' => false]);
                            Notification::make()->title('Marks unlocked')->warning()->send();
                        }),

                    DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn(ExamMark $record) => !$record->is_locked
                            && (Auth::user()?->hasRole('super_admin') ?? false)),
                ])
                ->dropdownPlacement('bottom-start'),                
                // ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()?->hasRole('super_admin')),

                    Action::make('bulk_lock')
                        ->label('Lock Selected')
                        ->icon('heroicon-o-lock-closed')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each(fn($r) => $r->update(['is_locked' => true]))),
                ]),
            ]);
    }
}
