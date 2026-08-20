<?php

namespace App\Filament\Resources\QuestionBanks\RelationManagers;

use App\Enums\QuestionType;
use App\Enums\DifficultyLevel;
use App\Models\QuestionBankItem;
use App\Models\Topic;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class QuestionBankItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Questions';

    protected static ?string $recordTitleAttribute = 'question_text';

    protected static ?string $permissionPrefix = 'question_bank_item';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Question Details')
                ->schema([
                    Grid::make(2)->schema([

                        Select::make('question_type')
                            ->label('Question Type')
                            ->options(QuestionType::forFilamentSelect())
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn($state, callable $set) => $this->toggleFieldsByType($state, $set))
                            ->default(QuestionType::MCQ->value),

                        Select::make('topic_id')
                            ->label('Topic / Chapter')
                            ->options(function (Get $get) {
                                $bank = $this->getOwnerRecord();
                                return Topic::query()
                                    ->where('subject_id', $bank->subject_id)
                                    ->where('branch_id', $bank->branch_id)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Select topic/chapter (optional)'),

                        Select::make('difficulty')
                            ->label('Difficulty')
                            ->options(DifficultyLevel::forFilamentSelect())
                            ->required()
                            ->default(DifficultyLevel::MEDIUM->value),

                        TextInput::make('marks')
                            ->label('Marks')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(1)
                            ->required(),

                        TextInput::make('negative_marks')
                            ->label('Negative Marks')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn(Get $get) => $get('marks') ?? 1)
                            ->default(0)
                            ->step(0.25),

                        TextInput::make('tags')
                            ->label('Tags (comma-separated)')
                            ->placeholder('algebra, chapter-1, quadratic-equations')
                            ->helperText('Used for filtering and paper generation.'),

                        Textarea::make('question_text')
                            ->label('Question Text')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Enter the question...'),

                        KeyValue::make('options')
                            ->label('Options')
                            ->keyLabel('Option Key (A, B, C...)')
                            ->valueLabel('Option Text')
                            ->addActionLabel('Add Option')
                            ->visible(fn(Get $get) => in_array($get('question_type'), [
                                QuestionType::MCQ->value,
                                QuestionType::TRUE_FALSE->value,
                                QuestionType::MATCHING->value,
                            ]))
                            ->columnSpanFull()
                            ->default([
                                'A' => '',
                                'B' => '',
                                'C' => '',
                                'D' => '',
                            ]),

                        KeyValue::make('correct_answer')
                            ->label('Correct Answer(s)')
                            ->keyLabel('Question Part')
                            ->valueLabel('Answer')
                            ->addActionLabel('Add Answer')
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('explanation')
                            ->label('Explanation / Solution')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Explanation shown to student after attempt (if enabled in exam settings)...'),

                        FileUpload::make('media')
                            ->label('Attach Media (Image/Diagram)')
                            ->image()
                            ->maxSize(2048)
                            ->directory('question-banks')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive questions are excluded from paper generation.'),

                        TextInput::make('display_order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0),
                    ]),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text')
            ->columns([
                TextColumn::make('question_text')
                    ->label('Question')
                    ->limit(60)
                    ->tooltip(fn($record) => $record->question_text)
                    ->weight('medium')
                    ->searchable(),

                TextColumn::make('question_type')
                    ->badge()
                    ->label('Type')
                    ->icons([
                        QuestionType::MCQ->value => 'heroicon-o-square-3-stack-3d',
                        QuestionType::TRUE_FALSE->value => 'heroicon-o-check-circle',
                        QuestionType::FILL_BLANK->value => 'heroicon-o-minus-circle',
                        QuestionType::SHORT_ANSWER->value => 'heroicon-o-pencil',
                        QuestionType::LONG_ANSWER->value => 'heroicon-o-document-text',
                        QuestionType::MATCHING->value => 'heroicon-o-arrow-path',
                    ]),

                TextColumn::make('topic.name')
                    ->label('Topic')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('difficulty')
                    ->badge()
                    ->label('Difficulty')
                    ->colors([
                        'success' => DifficultyLevel::EASY->value,
                        'warning' => DifficultyLevel::MEDIUM->value,
                        'danger' => DifficultyLevel::HARD->value,
                    ]),

                TextColumn::make('marks')
                    ->label('Marks')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('negative_marks')
                    ->label('Neg. Marks')
                    ->badge()
                    ->color('danger')
                    ->placeholder('0'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('display_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('display_order')
            ->filters([
                SelectFilter::make('question_type')
                    ->label('Type')
                    ->options(QuestionType::forFilamentSelect()),

                SelectFilter::make('difficulty')
                    ->label('Difficulty')
                    ->options(DifficultyLevel::forFilamentSelect()),

                SelectFilter::make('topic_id')
                    ->label('Topic')
                    ->options(function () {
                        $bank = $this->getOwnerRecord();
                        return Topic::query()
                            ->where('subject_id', $bank->subject_id)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Question')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Create New Question')
                    ->modalSubmitActionLabel('Save Question'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('duplicate')
                        ->label('Duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->action(function (QuestionBankItem $record) {
                            $new = $record->replicate();
                            $new->question_text = '[Copy] ' . $new->question_text;
                            $new->display_order = $this->getOwnerRecord()->items()->max('display_order') + 1;
                            $new->save();
                            \Filament\Notifications\Notification::make()
                                ->title('Question duplicated')
                                ->success()
                                ->send();
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),

                BulkAction::make('activate')
                    ->label('Activate Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn ($records) => $records->each->update(['is_active' => true])),

                BulkAction::make('deactivate')
                    ->label('Deactivate Selected')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn ($records) => $records->each->update(['is_active' => false])),
            ])
            ->emptyStateHeading('No questions yet')
            ->emptyStateDescription('Click "Add Question" to start building your question bank.')
            ->paginated([10, 25, 50, 100]);
    }

    protected function toggleFieldsByType(?string $state, callable $set): void
    {
        // Implementation for toggling fields based on question type
    }
}