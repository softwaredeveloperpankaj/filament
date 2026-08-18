<?php

namespace App\Filament\Resources\QuestionBanks\Tables;

use App\Filament\Resources\ExamQuestionPapers\ExamQuestionPaperResource;
use App\Models\QuestionBank;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class QuestionBanksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(40)
                    ->tooltip(fn(QuestionBank $record) => $record->description),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->badge()
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('type')
                    ->badge()
                    ->label('Type')
                    ->colors([
                        'info' => 'objective',
                        'warning' => 'subjective',
                        'primary' => 'mixed',
                    ])
                    ->icons([
                        'objective' => 'heroicon-o-square-3-stack-3d',
                        'subjective' => 'heroicon-o-pencil',
                        'mixed' => 'heroicon-o-squares-2x2',
                    ]),

                TextColumn::make('items_count')
                    ->label('Questions')
                    ->counts('items')
                    ->badge()
                    ->color(fn(int $state) => $state > 0 ? 'success' : 'danger')
                    ->sortable(),
                                        
                TextColumn::make('items_active_count')
                    ->label('Active')
                    ->getStateUsing(fn(QuestionBank $record) => $record->items()->where('is_active', true)->count())
                    ->badge()
                    ->color('info'),

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
                    ->toggleable(isToggledHiddenByDefault: true),

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

                TextColumn::make('deleted_at')
                    ->label('Deleted')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
                
                SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'objective' => 'Objective',
                        'subjective' => 'Subjective',
                        'mixed' => 'Mixed',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),                
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('info'),                
                    EditAction::make()
                        ->icon('heroicon-o-pencil')
                        ->color('primary'),

                    // Action::make('generate_paper')
                    //     ->label('Generate Paper')
                    //     ->icon('heroicon-o-document-plus')
                    //     ->color('success')
                    //     ->visible(fn(QuestionBank $record) => $record->items()->where('is_active', true)->count() >= 5)
                    //     ->url(fn(QuestionBank $record) => route('exams.papers.generate', ['bank' => $record]))
                    //     ->openUrlInNewTab()
                    //     ->requiresConfirmation(),

                    Action::make('generate_paper')
                        ->label('Generate Paper')
                        ->icon('heroicon-o-document-plus')
                        ->color('success')
                        ->visible(fn(QuestionBank $record) => $record->items()->where('is_active', true)->count() >= 5)
                        ->url(fn(QuestionBank $record) => ExamQuestionPaperResource::getUrl('create', [
                            'question_bank_id' => $record->id,
                        ]))
                        ->requiresConfirmation(),

                    DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation(),                        
                ])
            ])
            ->recordActionsColumnLabel('Actions')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-question-mark-circle');         
    }
}
