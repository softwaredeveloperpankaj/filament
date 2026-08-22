<?php

namespace App\Filament\Resources\ExamResults\Tables;

use App\Enums\ResultStatus;
use App\Models\ExamResult;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
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

class ExamResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('exam.name')
                //     ->searchable(),
                // TextColumn::make('student.id')
                //     ->searchable(),
                // TextColumn::make('exam_student_entry_id')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('grand_total_obtained')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('grand_total_maximum')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('overall_percentage')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('overall_grade')
                //     ->searchable(),
                // TextColumn::make('rank_in_class')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('rank_in_section')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('rank_in_branch')
                //     ->numeric()
                //     ->sortable(),
                // IconColumn::make('is_passed')
                //     ->boolean(),
                // TextColumn::make('status')
                //     ->badge(),
                // TextColumn::make('published_by')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('published_at')
                //     ->dateTime()
                //     ->sortable(),
                // TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('student.name')
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

                TextColumn::make('grand_total_obtained')
                    ->label('Marks')
                    ->formatStateUsing(fn($state, $record) => "{$state} / {$record->grand_total_maximum}")
                    ->sortable()
                    ->badge()
                    ->color(fn(ExamResult $record) => $record->is_passed ? 'success' : 'danger'),

                TextColumn::make('overall_percentage')
                    ->label('%')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('overall_grade')
                    ->label('Grade')
                    ->badge()
                    ->colors([
                        'success' => ['A+', 'A'],
                        'info'    => ['B+', 'B'],
                        'warning' => ['C+', 'C'],
                        'gray'    => ['D'],
                        'danger'  => ['F'],
                    ]),

                TextColumn::make('rank_in_section')
                    ->label('Section Rank')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('rank_in_class')
                    ->label('Class Rank')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->placeholder('—'),

                IconColumn::make('is_passed')
                    ->label('Result')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('status')
                    ->label('Status'),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),                
            ])
            ->defaultSort('rank_in_section')
            ->filters([
                SelectFilter::make('exam_id')
                    ->label('Exam')
                    ->relationship('exam', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('overall_grade')
                    ->label('Grade')
                    ->options(\App\Enums\GradeScale::forFilamentSelect()),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ResultStatus::forFilamentSelect()),

                TernaryFilter::make('is_passed')
                    ->label('Result')
                    ->trueLabel('Passed')
                    ->falseLabel('Failed'),
            ])
            ->recordActionsColumnLabel('Actions')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info'),

                    Action::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-megaphone')
                        ->color('success')
                        ->visible(fn(ExamResult $record) => $record->status === ResultStatus::DRAFT
                            && (Auth::user()?->hasAnyRole(['super_admin', 'admin', 'principal']) ?? false))
                        ->requiresConfirmation()
                        ->modalDescription('This will make the result visible to the student/parent.')
                        ->action(function (ExamResult $record) {
                            $record->publish(Auth::user());
                            Notification::make()->title('Result published')->success()->send();
                        }),

                    Action::make('withhold')
                        ->label('Withhold')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->visible(fn(ExamResult $record) => $record->status === ResultStatus::PUBLISHED
                            && (Auth::user()?->hasAnyRole(['super_admin', 'admin', 'principal']) ?? false))
                        ->requiresConfirmation()
                        ->modalDescription('The student/parent will no longer be able to view this result.')
                        ->action(function (ExamResult $record) {
                            $record->update(['status' => ResultStatus::WITHHELD]);
                            Notification::make()->title('Result withheld')->warning()->send();
                        }),

                    EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('primary')
                        ->visible(fn() => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'principal']) ?? false),
                ])
                ->dropdownPlacement('bottom-start'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('bulk_publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-megaphone')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn() => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'principal']) ?? false)
                        ->action(function ($records) {
                            $records
                                ->where('status', ResultStatus::DRAFT)
                                ->each(fn($r) => $r->publish(Auth::user()));

                            Notification::make()
                                ->title('Selected results published')
                                ->success()
                                ->send();
                        }),

                    Action::make('bulk_withhold')
                        ->label('Withhold Selected')
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn() => Auth::user()?->hasAnyRole(['super_admin', 'admin', 'principal']) ?? false)
                        ->action(fn($records) => $records
                            ->where('status', ResultStatus::PUBLISHED)
                            ->each(fn($r) => $r->update(['status' => ResultStatus::WITHHELD]))),
                ]),
            ]);
    }
}
