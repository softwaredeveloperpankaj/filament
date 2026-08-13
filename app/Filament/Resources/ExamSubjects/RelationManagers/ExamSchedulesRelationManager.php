<?php

namespace App\Filament\Resources\ExamSubject\RelationManagers;

use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\IconColumn;

class ExamSchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';
    protected static ?string $title = 'Exam Schedules';
    protected static ?string $recordTitleAttribute = 'exam_date';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Schedule Details')
                ->schema([
                    Grid::make(2)->schema([
                        DatePicker::make('exam_date')
                            ->label('Exam Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->minDate(fn() => $this->getOwnerRecord()->exam->start_date)
                            ->maxDate(fn() => $this->getOwnerRecord()->exam->end_date),

                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->required()
                            ->seconds(false),

                        TimePicker::make('end_time')
                            ->label('End Time')
                            ->required()
                            ->seconds(false)
                            ->after('start_time'),

                        TextInput::make('room_number')
                            ->label('Room Number')
                            ->placeholder('e.g. A-101, Hall B, Lab 3')
                            ->maxLength(50),

                        Select::make('invigilator_id')
                            ->label('Invigilator')
                            ->options(fn() => User::query()
                                ->where('branch_id', $this->getOwnerRecord()->exam->branch_id)
                                ->whereHas('roles', fn($q) => $q->whereIn('name', ['teacher', 'principal']))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Select invigilator'),

                        Textarea::make('instructions')
                            ->label('Subject-Specific Instructions')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Additional instructions for this subject...'),
                    ]),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('exam_date')
            ->columns([
                TextColumn::make('exam_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('start_time')
                    ->label('Time')
                    ->formatStateUsing(fn($state, $record) => $state->format('H:i') . ' - ' . $record->end_time->format('H:i'))
                    ->sortable(),

                TextColumn::make('room_number')
                    ->label('Room')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('invigilator.name')
                    ->label('Invigilator')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('exam_subject.is_graded')
                    ->label('Graded')
                    ->boolean(),
            ])
            ->defaultSort('exam_date')
            ->headerActions([
                CreateAction::make()->label('Add Schedule'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}