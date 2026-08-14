<?php

namespace App\Filament\Resources\QuestionBankItems\Tables;

use App\Enums\DifficultyLevel;
use App\Enums\QuestionType;
use App\Models\QuestionBankItem;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
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

class QuestionBankItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ->columns([
            //     TextColumn::make('questionBank.name')
            //         ->searchable(),
            //     TextColumn::make('subject.name')
            //         ->searchable(),
            //     TextColumn::make('topic.name')
            //         ->searchable(),
            //     TextColumn::make('question_type')
            //         ->badge(),
            //     TextColumn::make('marks')
            //         ->numeric()
            //         ->sortable(),
            //     TextColumn::make('negative_marks')
            //         ->numeric()
            //         ->sortable(),
            //     TextColumn::make('difficulty')
            //         ->badge(),
            //     TextColumn::make('created_by')
            //         ->numeric()
            //         ->sortable(),
            //     IconColumn::make('is_active')
            //         ->boolean(),
            //     TextColumn::make('created_at')
            //         ->dateTime()
            //         ->sortable()
            //         ->toggleable(isToggledHiddenByDefault: true),
            //     TextColumn::make('updated_at')
            //         ->dateTime()
            //         ->sortable()
            //         ->toggleable(isToggledHiddenByDefault: true),
            // ])

            ->columns([
                TextColumn::make('question_text')
                    ->label('Question')
                    ->limit(60)
                    ->tooltip(fn (QuestionBankItem $record) => $record->question_text)
                    ->weight('medium')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('questionBank.name')
                    ->label('Question Bank')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('topic.name')
                    ->label('Topic')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('question_type')
                    ->label('Type')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (QuestionType $state): string => ucfirst(str_replace('_', ' ', $state->value)))
                    ->color(fn (QuestionType $state): string => match ($state) {
                        QuestionType::MCQ->value,
                        QuestionType::TRUE_FALSE->value => 'info',

                        QuestionType::FILL_BLANK->value,
                        QuestionType::MATCHING->value => 'warning',

                        QuestionType::SHORT_ANSWER->value,
                        QuestionType::LONG_ANSWER->value => 'primary',
                        
                        default => 'gray',
                    })
                    ->icon(fn (QuestionType $state): string => match ($state) {                       
                        QuestionType::MCQ->value => 'heroicon-o-list-bullet',
                        QuestionType::TRUE_FALSE->value => 'heroicon-o-check-badge',
                        QuestionType::FILL_BLANK->value => 'heroicon-o-pencil-square',
                        QuestionType::MATCHING->value => 'heroicon-o-arrows-right-left',
                        QuestionType::SHORT_ANSWER->value => 'heroicon-o-chat-bubble-left-right',
                        QuestionType::LONG_ANSWER->value => 'heroicon-o-document-text',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                TextColumn::make('difficulty')
                    ->label('Difficulty')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (DifficultyLevel $state): string => ucfirst($state->value))
                    ->color(fn (DifficultyLevel $state): string => match ($state) {
                        DifficultyLevel::EASY->value => 'success',
                        DifficultyLevel::MEDIUM->value => 'warning',
                        DifficultyLevel::HARD->value => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('marks')
                    ->label('Marks')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('negative_marks')
                    ->label('Negative')
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                TextColumn::make('created_by')
                    ->label('Created By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault:true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('question_bank_id')
                    ->label('Question Bank')
                    ->relationship('questionBank', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('question_type')
                    ->label('Type')
                    ->options(QuestionType::class),

                SelectFilter::make('difficulty')
                    ->label('Difficulty')
                    ->options(DifficultyLevel::class),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                    EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('primary'),
                    Action::make('duplicate')
                        ->label('Duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (QuestionBankItem $record): void {
                            $new = $record->replicate();
                            $new->question_text = '[Copy] ' . $record->question_text;
                            $new->save();
                            Notification::make()
                                ->title('Question duplicated successfully')
                                ->success()
                                ->send();
                        }),
                    DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation(),
                ]),
            ])
            ->recordActionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                    DeleteBulkAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('No questions found')
            ->emptyStateDescription('Create your first question to start building question banks.')
            ->emptyStateIcon('heroicon-o-question-mark-circle')
            ->paginated([25, 50, 100]);
    }
}
