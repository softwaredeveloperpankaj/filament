<?php

namespace App\Filament\Resources\Exams\RelationManagers;

use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;

class ExamSchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';
    protected static ?string $title = 'Exam Schedule';
    protected static ?string $recordTitleAttribute = 'examSubject.subject.name';
    protected static ?string $permissionPrefix = 'exam_schedule';

    public function schema(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Schedule Details')
                ->schema([
                    Grid::make(2)->schema([

                        // Subject (scoped to exam's subjects)
                        Select::make('exam_subject_id')
                            ->label('Subject')
                            ->options(fn() => $this->getOwnerRecord()
                                ->subjects()
                                ->with('subject')
                                ->get()
                                ->pluck('subject.name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $subject = $this->getOwnerRecord()->subjects()->find($state);
                                if ($subject) {
                                    $set('exam_date', $this->getOwnerRecord()->start_date);
                                    $set('start_time', $this->getOwnerRecord()->start_time);
                                    $set('end_time', $this->getOwnerRecord()->start_time?->addMinutes($subject->duration_minutes));
                                }
                            }),

                        // Exam Date (within exam date range)
                        DatePicker::make('exam_date')
                            ->label('Exam Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->minDate($this->getOwnerRecord()->start_date)
                            ->maxDate($this->getOwnerRecord()->end_date),

                        // Start Time
                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->required()
                            ->seconds(false),

                        // End Time
                        TimePicker::make('end_time')
                            ->label('End Time')
                            ->required()
                            ->seconds(false)
                            ->after('start_time'),

                        // Room Number
                        TextInput::make('room_number')
                            ->label('Room Number')
                            ->placeholder('e.g. A-101, Hall B, Lab 3')
                            ->maxLength(50),

                        // Invigilator (teachers/principals from same branch)
                        Select::make('invigilator_id')
                            ->label('Invigilator')
                            ->options(fn() => User::query()
                                ->where('branch_id', $this->getOwnerRecord()->branch_id)
                                ->whereHas('roles', fn($q) => $q->whereIn('name', ['teacher', 'principal']))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Select invigilator'),

                        // Subject-specific instructions
                        Textarea::make('instructions')
                            ->label('Subject-Specific Instructions')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Additional instructions for this subject (printed on question paper)...'),
                    ]),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('examSubject.subject.name')
            ->columns([
                TextColumn::make('exam_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('start_time')
                    ->label('Time')
                    ->formatStateUsing(fn($state, $record) => $state?->format('H:i') . ' - ' . $record->end_time?->format('H:i'))
                    ->sortable(),

                TextColumn::make('examSubject.subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('room_number')
                    ->label('Room')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('invigilator.name')
                    ->label('Invigilator')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('examSubject.is_graded')
                    ->label('Graded')
                    ->badge()
                    ->trueLabel('Yes')
                    ->falseLabel('No'),
            ])
            ->defaultSort('exam_date')
            ->headerActions([
                CreateAction::make()->label('Add Schedule'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                ])
            ])
            ->toolbarActions([
                DeleteAction::make(),
            ]);
    }
}