<?php

namespace App\Filament\Resources\Exams\RelationManagers;

use App\Enums\ExamEntryStatus;
use App\Filament\Resources\Students\StudentResource;
use App\Models\BranchClass;
use App\Models\ClassSection;
use App\Models\ExamStudentEntry;
use App\Models\Section;
use App\Models\Student;
use App\Services\AdmitCardService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section as FilamentSection;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;

class ExamStudentEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'studentEntries';
    protected static ?string $title = 'Student Entries';
    protected static ?string $recordTitleAttribute = 'student.name';
    protected static ?string $permissionPrefix = 'exam_student_entry';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FilamentSection::make('Student Enrollment')
                ->schema([
                    Select::make('student_id')
                        ->label('Student')
                        ->options(fn() => Student::query()
                            ->where('branch_id', $this->getOwnerRecord()->branch_id)
                            ->where('branch_class_id', $this->getOwnerRecord()->branch_class_id)
                            ->where('section_id', $this->getOwnerRecord()->section_id)
                            ->whereDoesntHave('examEntries', fn($query) => $query->where('exam_id', $this->getOwnerRecord()->id))
                            ->get()
                            ->mapWithKeys(fn(Student $student) => [$student->id => $student->name])
                            ->all())
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (blank($state)) {
                                return;
                            }

                            $student = Student::query()->find($state);
                            if ($student) {
                                $set('roll_no', $student->roll_no);
                                $set('section_id', $student->section_id);
                            }
                        }),

                    TextInput::make('roll_no')
                        ->label('Roll No')
                        ->required()
                        ->maxLength(20),

                    Select::make('section_id')
                        ->label('Section')
                        ->options(fn() => ClassSection::query()
                            ->where('branch_class_id', $this->getOwnerRecord()->branch_class_id)
                            ->with('section')
                            ->get()
                            ->pluck('section.name', 'section_id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('status')
                        ->label('Status')
                        ->options(ExamEntryStatus::forFilamentSelect())
                        ->default(ExamEntryStatus::ENROLLED)
                        ->required(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student.name')
            ->columns([
                TextColumn::make('student_name')
                    ->label('Student')
                    ->getStateUsing(fn (ExamStudentEntry $record) => $record->student?->getFormValue('student_name'))
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('student', function ($q) use ($search) {
                            $q->where('form_data->student_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy(
                            Student::select('form_data->student_name')
                                ->whereColumn('students.id', 'exam_student_entries.student_id'),
                            $direction
                        );
                    })
                    ->weight('medium')
                    ->url(fn(ExamStudentEntry $record) => StudentResource::getUrl('view', ['record' => $record->student])),

                TextColumn::make('roll_no')
                    ->label('Roll No')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('student.class.name')
                    ->label('Class')
                    ->getStateUsing(fn (ExamStudentEntry $record) => $record->student?->class?->name)
                    ->badge()
                    ->color('warning'),

                TextColumn::make('section.name')
                    ->label('Section')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('marks_count')
                    ->label('Marks Entered')
                    ->counts('marks')
                    ->badge()
                    ->color(fn($state, $record) => $state === $record->exam->subjects->count() ? 'success' : 'warning'),

                TextColumn::make('admit_card_printed_at')
                    ->label('Admit Card Printed')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),
            ])
            ->defaultSort('roll_no')
            ->headerActions([
                // Individual enrollment
                CreateAction::make()
                    ->label('Enroll Student')
                    ->schema([
                        Select::make('student_id')
                            ->label('Student')
                            ->options(fn() => Student::query()
                                ->where('branch_id', $this->getOwnerRecord()->branch_id)
                                ->where('branch_class_id', $this->getOwnerRecord()->branch_class_id)
                                ->where('section_id', $this->getOwnerRecord()->section_id)
                                ->whereDoesntHave('examEntries', fn($query) => $query->where('exam_id', $this->getOwnerRecord()->id))
                                ->get()
                                ->mapWithKeys(fn(Student $student) => [$student->id => $student->roll_no])
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (blank($state)) {
                                    return;
                                }

                                $student = Student::query()->find($state);
                                if ($student) {
                                    $set('roll_no', $student->roll_no);
                                    $set('section_id', $student->section_id);
                                }
                            }),

                        TextInput::make('roll_no')
                            ->label('Roll No')
                            ->required()
                            ->maxLength(20),

                        Select::make('section_id')
                            ->label('Section')
                            ->options(fn() => ClassSection::query()
                                ->where('branch_class_id', $this->getOwnerRecord()->branch_class_id)
                                ->with('section')
                                ->get()
                                ->pluck('section.name', 'section_id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options(ExamEntryStatus::forFilamentSelect())
                            ->default(ExamEntryStatus::ENROLLED)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $studentId = $data['student_id'] ?? null;
                        if (! $studentId) {
                            Notification::make()
                                ->title('No student selected')
                                ->danger()
                                ->send();

                            return;
                        }

                        // Prevent duplicate enrollment
                        $exists = ExamStudentEntry::query()
                            ->where('exam_id', $this->getOwnerRecord()->id)
                            ->where('student_id', $studentId)
                            ->exists();

                        if ($exists) {
                            Notification::make()
                                ->title('Student is already enrolled')
                                ->warning()
                                ->send();

                            return;
                        }

                        $entry = ExamStudentEntry::create([
                            'exam_id' => $this->getOwnerRecord()->id,
                            'student_id' => $studentId,
                            'section_id' => $data['section_id'] ?? null,
                            'roll_no' => $data['roll_no'] ?? null,
                            'status' => $data['status'] ?? ExamEntryStatus::ENROLLED,
                        ]);

                        if ($entry) {
                            Notification::make()
                                ->title('Student enrolled')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failed to enroll student')
                                ->danger()
                                ->send();
                        }
                    }),

                // Bulk enroll by class and section
                CreateAction::make('bulk_enroll')
                    ->label('Bulk Enroll')
                    ->icon('heroicon-o-user-group')
                    ->color('info')
                    ->schema([
                        Select::make('branch_class_ids')
                            ->label('Classes')
                            ->options(function () {
                                return ClassSection::query()
                                    ->where('branch_id', $this->getOwnerRecord()->branch_id)
                                    ->with('branchClass')
                                    ->get()
                                    ->unique('branch_class_id')
                                    ->mapWithKeys(fn(ClassSection $classSection) => [
                                        $classSection->branch_class_id => $classSection->branchClass?->name ?? 'Unknown Class',
                                    ])
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn(callable $set) => $set('section_ids', [])),

                        Select::make('section_ids')
                            ->label('Sections')
                            ->options(function ($get) {
                                $branchClassIds = $get('branch_class_ids') ?? [];

                                if (empty($branchClassIds)) {
                                    return [];
                                }

                                return ClassSection::query()
                                    ->where('branch_id', $this->getOwnerRecord()->branch_id)
                                    ->whereIn('branch_class_id', $branchClassIds)
                                    ->with('section')
                                    ->get()
                                    ->unique('section_id')
                                    ->mapWithKeys(fn(ClassSection $classSection) => [
                                        $classSection->section_id => $classSection->section?->name ?? 'Unknown Section',
                                    ])
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->multiple()
                            ->nullable(),
                    ])
                    ->action(function (array $data) {
                        $branchClassIds = collect($data['branch_class_ids'] ?? [])->filter()->all();
                        $sectionIds = collect($data['section_ids'] ?? [])->filter()->all();

                        $query = Student::query()
                            ->where('branch_id', $this->getOwnerRecord()->branch_id)
                            ->whereIn('branch_class_id', $branchClassIds)
                            ->whereDoesntHave('examEntries', fn($q) => $q->where('exam_id', $this->getOwnerRecord()->id));

                        if (! empty($sectionIds)) {
                            $query->whereIn('section_id', $sectionIds);
                        }

                        $students = $query->get();

                        $rows = $students->map(fn(Student $student) => [
                            'exam_id' => $this->getOwnerRecord()->id,
                            'student_id' => $student->id,
                            'section_id' => $student->section_id,
                            'roll_no' => $student->roll_no,
                            'status' => ExamEntryStatus::ENROLLED,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ])->all();

                        $count = ! empty($rows) ? ExamStudentEntry::insert($rows) : 0;

                        Notification::make()
                            ->title("Enrolled {$count} students")
                            ->success()
                            ->send();
                    }),

                // Generate admit cards for all unprinted entries
                CreateAction::make('generate_admit_cards')
                    ->label('Generate Admit Cards')
                    ->icon('heroicon-o-id-card')
                    ->color('success')
                    ->visible(fn() => $this->getOwnerRecord()->canGenerateAdmitCards())
                    ->requiresConfirmation()
                    ->action(function () {
                        $entries = $this->getOwnerRecord()->studentEntries()
                            ->where('admit_card_printed', false)
                            ->get();

                        foreach ($entries as $entry) {
                            $entry->markAdmitCardPrinted();
                        }

                        Notification::make()
                            ->title("Generated admit cards for {$entries->count()} students")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    // View/Print Admit Card
                    Action::make('view_admit_card')
                        ->label('Admit Card')
                        ->icon('heroicon-o-eye')
                        ->url(fn(ExamStudentEntry $record) => route('admit-cards.print', ['entry' => $record]))
                        ->openUrlInNewTab()
                        ->visible(fn($record) => $record->admit_card_printed),

                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    // Bulk print admit cards
                    Action::make('print_admit_cards')
                        ->label('Print Admit Cards')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->action(fn($records) => AdmitCardService::markPrinted($records)),
                ]),
            ]);
    }
}