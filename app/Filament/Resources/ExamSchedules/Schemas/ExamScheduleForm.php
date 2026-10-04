<?php

namespace App\Filament\Resources\ExamSchedules\Schemas;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ExamScheduleForm
{
    public static function examOptionsQuery(?int $branchId = null)
    {
        return Exam::query()->when(
            $branchId !== null,
            fn ($query) => $query->where('branch_id', $branchId)
        );
    }

    public static function invigilatorOptionsQuery(?int $examId = null, ?int $fallbackBranchId = null)
    {
        $branchId = $examId ? (Exam::find($examId)?->branch_id ?? $fallbackBranchId) : $fallbackBranchId;

        return User::query()->when(
            $branchId !== null,
            fn ($query) => $query->where('branch_id', $branchId)
        );
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Exam Reference')
                ->schema([
                    Grid::make(2)->schema([

                        // Exam selector — branch-scoped
                        Select::make('exam_id')
                            ->label('Exam')
                            ->options(fn() => self::examOptionsQuery(Auth::user()?->branch_id)
                                ->with('branchClass')
                                ->get()
                                ->mapWithKeys(fn($exam) => [
                                    $exam->id => "{$exam->name} ({$exam->branchClass?->name})",
                                ]))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('exam_subject_id', null)),

                        // Subject scoped to chosen exam
                        Select::make('exam_subject_id')
                            ->label('Subject')
                            ->options(fn(Get $get) => ExamSubject::query()
                                ->where('exam_id', $get('exam_id'))
                                ->with('subject')
                                ->get()
                                ->pluck('subject.name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $examSubject = ExamSubject::with('exam')->find($get('exam_subject_id'));
                                if ($examSubject) {
                                    $set('exam_date', $examSubject->exam?->start_date?->toDateString());
                                    $set('start_time', $examSubject->exam?->start_time?->format('H:i'));
                                    if ($examSubject->duration_minutes) {
                                        $set('end_time', $examSubject->exam?->start_time?->addMinutes($examSubject->duration_minutes)?->format('H:i'));
                                    }
                                }
                            }),
                    ]),
                ]),

            Section::make('Schedule Details')
                ->schema([
                    Grid::make(2)->schema([

                        // Date limited to exam date range
                        DatePicker::make('exam_date')
                            ->label('Exam Date')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->minDate(fn(Get $get) => Exam::find($get('exam_id'))?->start_date)
                            ->maxDate(fn(Get $get) => Exam::find($get('exam_id'))?->end_date),

                        // Room number
                        TextInput::make('room_number')
                            ->label('Room / Hall Number')
                            ->placeholder('e.g. A-101, Exam Hall B, Lab 3')
                            ->maxLength(50),

                        // Start time
                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->required()
                            ->seconds(false),

                        // End time must be after start time
                        TimePicker::make('end_time')
                            ->label('End Time')
                            ->required()
                            ->seconds(false)
                            ->after('start_time'),

                        // Invigilator (teacher / principal from same branch)
                        Select::make('invigilator_id')
                            ->label('Invigilator')
                            ->options(fn(Get $get) => self::invigilatorOptionsQuery(
                                $get('exam_id'),
                                Auth::user()?->branch_id
                            )
                                ->whereHas('roles', fn($q) => $q->whereIn('name', ['teacher', 'principal']))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Select invigilator (optional)'),

                        // Subject-specific instructions
                        Textarea::make('instructions')
                            ->label('Subject-Specific Instructions')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Additional instructions for this subject (printed on admit card / question paper)...'),
                    ]),
                ]),
        ]);
    }
}
