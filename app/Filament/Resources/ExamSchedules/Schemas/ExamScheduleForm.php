<?php

namespace App\Filament\Resources\ExamSchedules\Schemas;

use App\Enums\RoomNumber;
use App\Models\BranchClass;
use App\Models\ClassSection;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\ExamSubject;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
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

    protected static function timeToMinutes(mixed $time): ?int
    {
        if (blank($time)) {
            return null;
        }

        $parsed = Carbon::parse($time);

        return ((int) $parsed->format('H') * 60) + (int) $parsed->format('i');
    }

    protected static function timesOverlap(
        mixed $start,
        mixed $end,
        mixed $existingStart,
        mixed $existingEnd
    ): bool {
        $startMinutes = self::timeToMinutes($start);
        $endMinutes = self::timeToMinutes($end);
        $existingStartMinutes = self::timeToMinutes($existingStart);
        $existingEndMinutes = self::timeToMinutes($existingEnd);

        if (
            $startMinutes === null ||
            $endMinutes === null ||
            $existingStartMinutes === null ||
            $existingEndMinutes === null
        ) {
            return false;
        }

        return $startMinutes < $existingEndMinutes
            && $existingStartMinutes < $endMinutes;
    }

    protected static function isSameSharedSitting(
        ExamSchedule $schedule,
        int|string|null $subjectId,
        string $examDate,
        mixed $startTime,
        mixed $endTime,
        mixed $roomNumber
    ): bool {
        $existingSubjectId = $schedule->examSubject?->subject_id;
        $selectedSubject = $subjectId ? ExamSubject::query()->find($subjectId) : null;

        if (! $selectedSubject || ! $existingSubjectId) {
            return false;
        }

        return (string) $existingSubjectId === (string) $selectedSubject->subject_id
            && Carbon::parse($schedule->exam_date)->toDateString() === $examDate
            && self::timeToMinutes($schedule->start_time) === self::timeToMinutes($startTime)
            && self::timeToMinutes($schedule->end_time) === self::timeToMinutes($endTime)
            && (string) $schedule->room_number === (string) $roomNumber;
    }

    protected static function getScheduleConflictMessage(
        ?int $examId,
        ?string $examDate,
        mixed $startTime,
        mixed $endTime,
        int|string|null $branchClassId,
        int|string|null $sectionId,
        int|string|null $examSubjectId,
        mixed $roomNumber,
        int|string|null $invigilatorId,
        string $conflictType = 'all',
        int|string|null $ignoredScheduleId = null
    ): ?string {
        if (blank($examId) || blank($examDate) || blank($startTime) || blank($endTime)) {
            return null;
        }

        $startMinutes = self::timeToMinutes($startTime);
        $endMinutes = self::timeToMinutes($endTime);

        if ($startMinutes === null || $endMinutes === null || $endMinutes <= $startMinutes) {
            return null;
        }

        $examDate = Carbon::parse($examDate)->toDateString();

        $schedules = ExamSchedule::query()
            ->with('examSubject.subject')
            ->where('exam_id', $examId)
            ->whereDate('exam_date', $examDate)
            ->when($ignoredScheduleId, fn ($query) => $query->whereKeyNot($ignoredScheduleId))
            ->get();

        foreach ($schedules as $schedule) {
            if (blank($schedule->start_time) || blank($schedule->end_time)) {
                continue;
            }

            if (! self::timesOverlap($startTime, $endTime, $schedule->start_time, $schedule->end_time)) {
                continue;
            }

            $sameSharedSitting = self::isSameSharedSitting(
                $schedule,
                $examSubjectId,
                $examDate,
                $startTime,
                $endTime,
                $roomNumber
            );

            if ($conflictType === 'class') {
                $sameClass = (string) ($schedule->branch_class_id ?? $schedule->exam?->branch_class_id)
                    === (string) $branchClassId;

                $sameSection = (string) ($schedule->section_id ?? $schedule->exam?->section_id)
                    === (string) $sectionId;

                $sameExamSubject = (string) $schedule->exam_subject_id === (string) $examSubjectId;

                if ($sameClass && $sameSection && ! $sameExamSubject) {
                    return 'This class and section already has another exam during this time.';
                }
            }

            if ($conflictType === 'room') {
                $sameRoom = (string) $schedule->room_number === (string) $roomNumber;

                if ($sameRoom && ! $sameSharedSitting) {
                    return 'This room is already assigned to another exam during this time.';
                }
            }

            if ($conflictType === 'invigilator') {
                $sameInvigilator = filled($invigilatorId)
                    && (string) $schedule->invigilator_id === (string) $invigilatorId;

                if ($sameInvigilator && ! $sameSharedSitting) {
                    return 'This invigilator is already assigned to another exam during this time.';
                }
            }
        }

        return null;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Exam Reference')
                ->schema([
                    Select::make('exam_id')
                        ->label('Exam')
                        ->options(fn () => self::examOptionsQuery(Auth::user()?->branch_id)
                            // ->with('branchClass')
                            ->get()
                            ->mapWithKeys(fn ($exam) => [
                                $exam->id => "{$exam->name}",
                            ]))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $exam = Exam::find($get('exam_id'));

                            $set('branch_class_id', $exam?->branch_class_id);
                            $set('section_id', $exam?->section_id);
                            $set('exam_subject_id', null);
                        }),

                    Select::make('branch_class_id')
                        ->label('Class')
                        ->options(function (Get $get) {
                            $exam = Exam::find($get('exam_id'));
                            return BranchClass::query()
                                ->where('branch_id', $exam?->branch_id)
                                ->pluck('name', 'id')
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('section_id', null)),

                    Select::make('section_id')
                        ->label('Section')
                        ->options(fn (Get $get) => $get('branch_class_id')
                            ? ClassSection::query()
                                ->where('branch_class_id', $get('branch_class_id'))
                                ->with('section')
                                ->get()
                                ->pluck('section.name', 'section_id')
                            : [])
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('exam_subject_id', null)),

                    Select::make('exam_subject_id')
                        ->label('Subject')
                        ->options(function (Get $get) {
                            $examId = $get('exam_id');
                            $branchClassId = $get('branch_class_id');
                            $sectionId = $get('section_id');

                            if (! $examId || ! $branchClassId || ! $sectionId) {
                                return [];
                            }

                            return ExamSubject::query()
                                ->where('exam_id', $examId)
                                ->where('branch_class_id', $branchClassId)
                                ->where('section_id', $sectionId)
                                ->with('subject')
                                ->get()
                                ->mapWithKeys(fn ($examSubject) => [
                                    $examSubject->id => $examSubject->subject
                                        ? $examSubject->subject->name . ' (' . $examSubject->subject->code . ')'
                                        : 'Unknown Subject',
                                ])
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            $examSubject = ExamSubject::with('exam')->find($get('exam_subject_id'));

                            if (! $examSubject) {
                                return;
                            }

                            $exam = $examSubject->exam;
                            $set('exam_date', $exam?->start_date?->toDateString());
                            $set('start_time', $exam?->start_time?->format('H:i'));

                            if ($examSubject->duration_minutes && $exam?->start_time) {
                                $set(
                                    'end_time',
                                    $exam->start_time->copy()->addMinutes((int) $examSubject->duration_minutes)->format('H:i')
                                );
                            }
                        }),
                ]),

            Section::make('Schedule Details')
                ->schema([
                    DatePicker::make('exam_date')
                        ->label('Exam Date')
                        ->required()
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->minDate(fn (Get $get) => Exam::find($get('exam_id'))?->start_date)
                        ->maxDate(fn (Get $get) => Exam::find($get('exam_id'))?->end_date),

                    Select::make('room_number')
                        ->label('Room Number')
                        ->options(RoomNumber::forFilamentSelect())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->helperText('Independent exams cannot overlap in the same room.')
                        ->rules(fn (Get $get): array => [
                            function (string $attribute, $value, Closure $fail) use ($get): void {
                                $message = self::getScheduleConflictMessage(
                                    $get('exam_id'),
                                    $get('exam_date'),
                                    $get('start_time'),
                                    $get('end_time'),
                                    $get('branch_class_id'),
                                    $get('section_id'),
                                    $get('exam_subject_id'),
                                    $value,
                                    $get('invigilator_id'),
                                    'room',
                                    $get('id')
                                );

                                if ($message) {
                                    $fail($message);
                                }
                            },
                        ]),

                    TimePicker::make('start_time')
                        ->label('Start Time')
                        ->required()
                        ->seconds(false)
                        ->live()
                        ->rules(fn (Get $get): array => [
                            function (string $attribute, $value, Closure $fail) use ($get): void {
                                if (
                                    filled($value) &&
                                    filled($get('end_time')) &&
                                    self::timeToMinutes($value) >= self::timeToMinutes($get('end_time'))
                                ) {
                                    $fail('Start time must be earlier than end time.');
                                }
                            },
                        ]),

                    TimePicker::make('end_time')
                        ->label('End Time')
                        ->required()
                        ->seconds(false)
                        ->live()
                        ->after('start_time')
                        ->rules(fn (Get $get): array => [
                            function (string $attribute, $value, Closure $fail) use ($get): void {
                                if (
                                    filled($value) &&
                                    filled($get('start_time')) &&
                                    self::timeToMinutes($value) <= self::timeToMinutes($get('start_time'))
                                ) {
                                    $fail('End time must be later than start time.');
                                }
                            },
                        ]),

                    Select::make('invigilator_id')
                        ->label('Invigilator')
                        ->options(fn (Get $get) => self::invigilatorOptionsQuery(
                            $get('exam_id'),
                            Auth::user()?->branch_id
                        )
                            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['teacher', 'principal']))
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->placeholder('Select invigilator')
                        ->rules(fn (Get $get): array => [
                            function (string $attribute, $value, Closure $fail) use ($get): void {
                                $message = self::getScheduleConflictMessage(
                                    $get('exam_id'),
                                    $get('exam_date'),
                                    $get('start_time'),
                                    $get('end_time'),
                                    $get('branch_class_id'),
                                    $get('section_id'),
                                    $get('exam_subject_id'),
                                    $get('room_number'),
                                    $value,
                                    'invigilator',
                                    $get('id')
                                );

                                if ($message) {
                                    $fail($message);
                                }
                            },
                        ]),

                    Textarea::make('instructions')
                        ->label('Subject-Specific Instructions')
                        ->rows(3)
                        ->columnSpanFull()
                        ->placeholder('Additional instructions for this subject...'),
                ]),
        ]);
    }
}
