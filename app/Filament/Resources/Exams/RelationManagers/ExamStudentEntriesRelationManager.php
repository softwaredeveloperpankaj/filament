<?php

namespace App\Filament\Resources\Exams\RelationManagers;

use App\Enums\ExamEntryStatus;
use App\Filament\Resources\Students\StudentResource;
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
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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

    public function schema(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Student Enrollment')
                ->schema([
                    Grid::make(2)->schema([

                        // Student (scoped to exam's class/section, not already enrolled)
                        Select::make('student_id')
                            ->label('Student')
                            ->options(fn() => Student::query()
                                ->where('branch_id', $this->getOwnerRecord()->branch_id)
                                ->where('branch_class_id', $this->getOwnerRecord()->branch_class_id)
                                ->where('section_id', $this->getOwnerRecord()->section_id)
                                ->whereDoesntHave('examEntries', fn($query) => $query->where('exam_id', $this->getOwnerRecord()->id))
                                ->pluck('name', 'id'))
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

                        // Roll Number (auto-filled from student)
                        TextInput::make('roll_no')
                            ->label('Roll No')
                            ->required()
                            ->maxLength(20),

                        // Section (auto-filled but editable)
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

                        // Status
                        Select::make('status')
                            ->label('Status')
                            ->options(ExamEntryStatus::class)
                            ->default(ExamEntryStatus::ENROLLED)
                            ->required(),
                    ]),
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

                TextColumn::make('section.name')
                    ->label('Section')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray' => ExamEntryStatus::ENROLLED->value,
                        'info' => ExamEntryStatus::ADMIT_CARD_GENERATED->value,
                        'success' => ExamEntryStatus::APPEARED->value,
                        'warning' => ExamEntryStatus::ABSENT->value,
                        'danger' => ExamEntryStatus::DEBARRED->value,
                        'secondary' => ExamEntryStatus::WITHDRAWN->value,
                    ]),

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
                CreateAction::make()->label('Enroll Student'),

                // Bulk enroll by section
                Action::make('bulk_enroll')
                    ->label('Bulk Enroll Section')
                    ->icon('heroicon-o-user-group')
                    ->color('info')
                    ->schema([
                        Select::make('section_id')
                            ->label('Section')
                            ->options(fn() => ClassSection::query()
                                ->where('branch_class_id', $this->getOwnerRecord()->branch_class_id)
                                ->with('section')
                                ->get()
                                ->pluck('section.name', 'section_id'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $students = Student::query()
                            ->where('branch_id', $this->getOwnerRecord()->branch_id)
                            ->where('branch_class_id', $this->getOwnerRecord()->branch_class_id)
                            ->where('section_id', $data['section_id'])
                            ->whereDoesntHave('examEntries', fn($q) => $q->where('exam_id', $this->getOwnerRecord()->id))
                            ->get();

                        $count = 0;
                        foreach ($students as $student) {
                            ExamStudentEntry::create([
                                'exam_id' => $this->getOwnerRecord()->id,
                                'student_id' => $student->id,
                                'section_id' => $student->section_id,
                                'roll_no' => $student->roll_no,
                                'status' => ExamEntryStatus::ENROLLED,
                            ]);
                            $count++;
                        }

                        Notification::make()
                            ->title("Enrolled {$count} students")
                            ->success()
                            ->send();
                    }),

                // Generate admit cards for all unprinted entries
                Action::make('generate_admit_cards')
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
                        ->url(fn(ExamStudentEntry $record) => route('exams.admit-card', ['exam' => $this->getOwnerRecord(), 'entry' => $record]))
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
                        ->action(fn($records) => AdmitCardService::markPrinted($records)),
                ]),
            ]);
    }
}