<?php

namespace App\Filament\Resources\Exams\RelationManagers;

use App\Enums\RoomNumber;
use App\Models\BranchClass;
use App\Models\ClassSection;
use App\Models\ExamSubject;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamSchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    protected static ?string $title = 'Exam Schedule';

    protected static ?string $recordTitleAttribute = 'examSubject.subject.name';

    protected static ?string $permissionPrefix = 'exam_schedule';

    /**
     * Get the current schedule ID when editing an existing record.
     *
     * A create action has no persisted record yet, so it should remain null.
     */
    protected function getCurrentScheduleId(): ?int
    {
        $record = $this->getMountedAction()?->getRecord();

        if ($record instanceof Model && $record->exists) {
            return (int) $record->getKey();
        }

        if (method_exists($this, 'getMountedTableActionRecord')) {
            $tableRecord = $this->getMountedAction();

            if ($tableRecord instanceof Model && $tableRecord->exists) {
                return (int) $tableRecord->getKey();
            }
        }

        return null;
    }

    /**
     * Convert a time value to minutes after midnight.
     */
    protected function timeToMinutes(mixed $time): ?int
    {
        if (blank($time)) {
            return null;
        }

        $parsed = Carbon::parse($time);

        return ((int) $parsed->format('H') * 60)
            + (int) $parsed->format('i');
    }

    /**
     * Determine whether two time intervals overlap.
     *
     * Back-to-back schedules are allowed.
     */
    protected function timesOverlap(
        mixed $start,
        mixed $end,
        mixed $existingStart,
        mixed $existingEnd
    ): bool {
        $startMinutes = $this->timeToMinutes($start);
        $endMinutes = $this->timeToMinutes($end);
        $existingStartMinutes = $this->timeToMinutes($existingStart);
        $existingEndMinutes = $this->timeToMinutes($existingEnd);

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


/**
 * Determine whether two schedules represent the same shared exam sitting.
 *
 * A shared sitting requires the same underlying subject, exam date,
 * start time, end time, and room.
 */
protected function isSameSharedSitting(
    Model $schedule,
    int|string|null $subjectId,
    string $examDate,
    mixed $startTime,
    mixed $endTime,
    mixed $roomNumber
): bool {
    if (
        blank($subjectId) ||
        blank($roomNumber) ||
        blank($startTime) ||
        blank($endTime)
    ) {
        return false;
    }

    $existingSubjectId = $schedule->examSubject?->subject_id;

    $selectedSubject = ExamSubject::query()->find($subjectId);

    if (
        ! $selectedSubject ||
        blank($existingSubjectId) ||
        blank($schedule->room_number)
    ) {
        return false;
    }

    return (string) $existingSubjectId === (string) $selectedSubject->subject_id
        && Carbon::parse($schedule->exam_date)->toDateString() === $examDate
        && $this->timeToMinutes($schedule->start_time)
            === $this->timeToMinutes($startTime)
        && $this->timeToMinutes($schedule->end_time)
            === $this->timeToMinutes($endTime)
        && (string) $schedule->room_number === (string) $roomNumber;
}

/**
 * Validate exam schedule conflicts.
 *
 * Rules:
 * 1. End time must be later than start time.
 * 2. The same subject cannot be scheduled twice for the same
 *    class and section on the same date.
 * 3. The same class and section cannot have overlapping exams.
 * 4. A room cannot host independent overlapping exams.
 * 5. An invigilator cannot supervise independent overlapping exams.
 * 6. Different classes may have exams on the same date and time.
 * 7. Shared sittings are allowed when subject, date, time and room match.
 * 8. The current schedule is excluded during editing.
 *
 * Conflict checks are scoped to the current parent Exam record.
 */
protected function getScheduleConflictMessage(
    ?string $examDate,
    mixed $startTime,
    mixed $endTime,
    int|string|null $branchClassId,
    int|string|null $sectionId,
    int|string|null $examSubjectId,
    mixed $roomNumber,
    int|string|null $invigilatorId,
    string $conflictType = 'all'
): ?string {
    if (
        blank($examDate) ||
        blank($startTime) ||
        blank($endTime)
    ) {
        return null;
    }

    $startMinutes = $this->timeToMinutes($startTime);
    $endMinutes = $this->timeToMinutes($endTime);

    if (
        $startMinutes === null ||
        $endMinutes === null ||
        $endMinutes <= $startMinutes
    ) {
        return $conflictType === 'time'
            ? 'End time must be later than start time.'
            : null;
    }

    if (blank($branchClassId)) {
        return null;
    }

    $examDate = Carbon::parse($examDate)->toDateString();

    $selectedExamSubject = filled($examSubjectId)
        ? ExamSubject::query()->find($examSubjectId)
        : null;

    if (! $selectedExamSubject) {
        return null;
    }

    $selectedSubjectId = $selectedExamSubject->subject_id;

    $owner = $this->getOwnerRecord();

    $currentScheduleId = $this->getCurrentScheduleId();

    $schedulesQuery = $owner->schedules()
        ->with('examSubject')
        ->whereDate('exam_date', $examDate);

    if ($currentScheduleId !== null) {
        $schedulesQuery->whereKeyNot($currentScheduleId);
    }

    $schedules = $schedulesQuery->get();

    foreach ($schedules as $schedule) {
        $existingSubjectId = $schedule->examSubject?->subject_id;

        $sameClass = filled($schedule->branch_class_id)
            && (string) $schedule->branch_class_id === (string) $branchClassId;

        $sameSection = filled($sectionId)
            && filled($schedule->section_id)
            && (string) $schedule->section_id === (string) $sectionId;

        $sameSubject = filled($existingSubjectId)
            && (string) $existingSubjectId === (string) $selectedSubjectId;

        if (
            $conflictType === 'subject' &&
            $sameClass &&
            $sameSection &&
            $sameSubject
        ) {
            return 'This subject is already scheduled for this class and section on this date.';
        }

        if (
            blank($schedule->start_time) ||
            blank($schedule->end_time)
        ) {
            continue;
        }

        if (! $this->timesOverlap(
            $startTime,
            $endTime,
            $schedule->start_time,
            $schedule->end_time
        )) {
            continue;
        }

        if (
            $conflictType === 'class' &&
            $sameClass &&
            $sameSection
        ) {
            return 'This class and section already has another exam during this time.';
        }

        $sameSharedSitting = $this->isSameSharedSitting(
            $schedule,
            $examSubjectId,
            $examDate,
            $startTime,
            $endTime,
            $roomNumber
        );

        if ($conflictType === 'room') {
            $sameRoom = filled($roomNumber)
                && filled($schedule->room_number)
                && (string) $schedule->room_number === (string) $roomNumber;

            if ($sameRoom && ! $sameSharedSitting) {
                return 'This room is already assigned to another exam during this time.';
            }
        }

        if ($conflictType === 'invigilator') {
            $sameInvigilator = filled($invigilatorId)
                && filled($schedule->invigilator_id)
                && (string) $schedule->invigilator_id === (string) $invigilatorId;

            if ($sameInvigilator && ! $sameSharedSitting) {
                return 'This invigilator is already assigned to another exam during this time.';
            }
        }
    }

    return null;
}


    /**
     * Validate the selected date against the parent exam date range.
     */
    protected function getExamDateValidationMessage(
        mixed $examDate
    ): ?string {
        if (blank($examDate)) {
            return null;
        }

        $owner = $this->getOwnerRecord();

        $date = Carbon::parse($examDate)->toDateString();

        if (
            $owner->start_date &&
            $date < Carbon::parse($owner->start_date)->toDateString()
        ) {
            return 'The exam date cannot be before the examination start date.';
        }

        if (
            $owner->end_date &&
            $date > Carbon::parse($owner->end_date)->toDateString()
        ) {
            return 'The exam date cannot be after the examination end date.';
        }

        return null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('branch_class_id')
                ->label('Class')
                ->options(fn () => BranchClass::query()
                    ->where('branch_id', $this->getOwnerRecord()->branch_id)
                    ->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(
                    fn (\Filament\Schemas\Components\Utilities\Set $set) =>
                        $set('section_id', null)
                ),

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
                ->default(fn () => $this->getOwnerRecord()->section_id)
                ->required()
                ->live()
                ->afterStateUpdated(
                    fn (\Filament\Schemas\Components\Utilities\Set $set) =>
                        $set('exam_subject_id', null)
                ),

            Select::make('exam_subject_id')
                ->label('Subject')
                ->options(function (Get $get) {
                    $branchClassId = $get('branch_class_id');
                    $sectionId = $get('section_id');

                    if (! $branchClassId || ! $sectionId) {
                        return [];
                    }

                    return ExamSubject::query()
                        ->where('exam_id', $this->getOwnerRecord()->getKey())
                        ->where('branch_class_id', $branchClassId)
                        ->where('section_id', $sectionId)
                        ->with('subject')
                        ->get()
                        ->mapWithKeys(fn ($examSubject) => [
                            $examSubject->id => $examSubject->subject
                                ? $examSubject->subject->name
                                    . ' (' . $examSubject->subject->code . ')'
                                : 'Unknown Subject',
                        ])
                        ->all();
                })
                ->searchable()
                ->preload()
                ->required()
                ->disableOptionWhen(function ($value) {
                    $query = $this->getOwnerRecord()
                        ->schedules()
                        ->where('exam_subject_id', $value);

                    $currentId = $this->getCurrentScheduleId();

                    if ($currentId !== null) {
                        $query->whereKeyNot($currentId);
                    }

                    return $query->exists();
                })
                ->live()
                ->rules(fn (Get $get): array => [
                    function (string $attribute, $value, Closure $fail) use ($get): void {
                        $message = $this->getScheduleConflictMessage(
                            $get('exam_date'),
                            $get('start_time'),
                            $get('end_time'),
                            $get('branch_class_id'),
                            $get('section_id'),
                            $value,
                            $get('room_number'),
                            $get('invigilator_id'),
                            'subject'
                        );

                        if ($message) {
                            $fail($message);
                        }
                    },
                ])
                ->afterStateUpdated(function (
                    $state,
                    \Filament\Schemas\Components\Utilities\Set $set
                ) {
                    $subject = ExamSubject::query()
                        ->where('exam_id', $this->getOwnerRecord()->getKey())
                        ->find($state);

                    if (! $subject) {
                        return;
                    }

                    $owner = $this->getOwnerRecord();

                    $set(
                        'exam_date',
                        $owner->start_date
                            ? Carbon::parse($owner->start_date)->toDateString()
                            : null
                    );

                    $startTime = $owner->start_time;

                    if ($startTime) {
                        $start = Carbon::parse($startTime);

                        $set('start_time', $start->format('H:i'));

                        $set(
                            'end_time',
                            $start->copy()
                                ->addMinutes((int) $subject->duration_minutes)
                                ->format('H:i')
                        );
                    }
                }),

            DatePicker::make('exam_date')
                ->label('Exam Date')
                ->required()
                ->native(false)
                ->displayFormat('d M Y')
                ->minDate(fn () => $this->getOwnerRecord()->start_date)
                ->maxDate(fn () => $this->getOwnerRecord()->end_date)
                ->rules(fn (Get $get): array => [
                    function (
                        string $attribute,
                        $value,
                        Closure $fail
                    ) use ($get): void {
                        $dateMessage = $this->getExamDateValidationMessage($value);

                        if ($dateMessage) {
                            $fail($dateMessage);
                        }
                    },
                ]),

            TimePicker::make('start_time')
                ->label('Start Time')
                ->required()
                ->seconds(false)
                ->live()
                ->rules(fn (Get $get): array => [
                    function (
                        string $attribute,
                        $value,
                        Closure $fail
                    ) use ($get): void {
                        if (
                            filled($value) &&
                            filled($get('end_time')) &&
                            $this->timeToMinutes($value)
                                >= $this->timeToMinutes($get('end_time'))
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
                    function (
                        string $attribute,
                        $value,
                        Closure $fail
                    ) use ($get): void {
                        if (
                            filled($value) &&
                            filled($get('start_time')) &&
                            $this->timeToMinutes($value)
                                <= $this->timeToMinutes($get('start_time'))
                        ) {
                            $fail('End time must be later than start time.');
                        }
                    },
                ]),

            Select::make('room_number')
                ->label('Room Number')
                ->options(RoomNumber::forFilamentSelect())
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->helperText(
                    'Select a room. Independent exams cannot overlap in the same room.'
                )
                ->rules(fn (Get $get): array => [
                    function (string $attribute, $value, Closure $fail) use ($get): void {
                        $message = $this->getScheduleConflictMessage(
                            $get('exam_date'),
                            $get('start_time'),
                            $get('end_time'),
                            $get('branch_class_id'),
                            $get('section_id'),
                            $get('exam_subject_id'),
                            $value,
                            $get('invigilator_id'),
                            'room'
                        );

                        if ($message) {
                            $fail($message);
                        }
                    },
                ]),

            Select::make('invigilator_id')
                ->label('Invigilator')
                ->options(fn () => User::query()
                    ->where('branch_id', $this->getOwnerRecord()->branch_id)
                    ->whereHas('roles', fn (Builder $query) =>
                        $query->whereIn('name', ['teacher', 'principal'])
                    )
                    ->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->live()
                ->placeholder('Select invigilator')
                ->rules(fn (Get $get): array => [
                    function (string $attribute, $value, Closure $fail) use ($get): void {
                        $message = $this->getScheduleConflictMessage(
                            $get('exam_date'),
                            $get('start_time'),
                            $get('end_time'),
                            $get('branch_class_id'),
                            $get('section_id'),
                            $get('exam_subject_id'),
                            $get('room_number'),
                            $value,
                            'invigilator'
                        );

                        if ($message) {
                            $fail($message);
                        }
                    },
                ]),

            Textarea::make('instructions')
                ->label('Subject Instructions')
                ->rows(3)
                ->columnSpanFull()
                ->placeholder('Additional instructions for this subject...'),
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
                    ->formatStateUsing(
                        fn ($state, $record) =>
                            $state?->format('H:i')
                            . ' - '
                            . $record->end_time?->format('H:i')
                    )
                    ->sortable(),

                TextColumn::make('examSubject.subject.name')
                    ->label('Subject')
                    ->getStateUsing(fn ($record) =>
                        $record->examSubject?->subject
                            ? $record->examSubject->subject->name
                                . ' (' . $record->examSubject->subject->code . ')'
                            : null
                    )
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

                IconColumn::make('examSubject.is_graded')
                    ->label('Graded')
                    ->boolean(),
            ])
            ->defaultSort('exam_date')
            ->headerActions([
                CreateAction::make()
                    ->label('Add Schedule'),

                Action::make('printSchedule')
                    ->label('Print Schedule')
                    ->icon('heroicon-o-printer')
                    ->url(fn (): string => route('exams.schedule.print', [
                        'exam' => $this->getOwnerRecord()->getKey(),
                    ]))
                    ->openUrlInNewTab(),
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