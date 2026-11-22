<?php

namespace App\Filament\Resources\ExamSubjects\Schemas;

use App\Models\BranchClass;
use App\Models\ClassSection;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Subject;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ExamSubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('exam_id')
                    ->relationship('exam', 'name')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        $exam = Exam::find($state);
                        $set('branch_class_id', $exam?->branch_class_id);
                        $set('section_id', $exam?->section_id);
                    }),
                Select::make('branch_class_id')
                    ->label('Class')
                    ->options(fn (Get $get) => BranchClass::query()
                        ->where('branch_id', Exam::find($get('exam_id'))?->branch_id ?? Auth::user()?->branch_id)
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
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
                    ->required(),
                Select::make('subject_id')
                    ->label('Subject')
                    ->options(function (Get $get) {
                        $branchClassId = $get('branch_class_id');
                        $sectionId = $get('section_id');

                        if (! $branchClassId || ! $sectionId) {
                            return [];
                        }

                        return Subject::query()
                            ->where('branch_id', Exam::find($get('exam_id'))?->branch_id ?? Auth::user()?->branch_id)
                            ->whereHas('sectionSubjects', fn ($query) => $query
                                ->where('branch_class_id', $branchClassId)
                                ->where('section_id', $sectionId)
                            )
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (Subject $subject) => [
                                $subject->id => "{$subject->name} ({$subject->code})",
                            ])
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disableOptionWhen(function ($value, Get $get, $record) {
                        $examId = $get('exam_id');
                        $branchClassId = $get('branch_class_id');
                        $sectionId = $get('section_id');

                        if (! $examId || ! $branchClassId || ! $sectionId || ! $value) {
                            return false;
                        }

                        $query = ExamSubject::query()
                            ->where('exam_id', $examId)
                            ->where('branch_class_id', $branchClassId)
                            ->where('section_id', $sectionId)
                            ->where('subject_id', $value);

                        if ($record instanceof ExamSubject) {
                            $query->whereKeyNot($record->getKey());
                        }

                        return $query->exists();
                    }),
                TextInput::make('max_marks')
                    ->required()
                    ->numeric()
                    ->default(100),
                TextInput::make('pass_marks')
                    ->required()
                    ->numeric()
                    ->default(33),
                TextInput::make('theory_marks')
                    ->required()
                    ->numeric()
                    ->default(80),
                TextInput::make('practical_marks')
                    ->required()
                    ->numeric()
                    ->default(20),
                TextInput::make('duration_minutes')
                    ->required()
                    ->numeric()
                    ->default(180),
                TextInput::make('paper_structure')
                    ->label('Paper Structure (JSON)')
                    ->json()
                    ->nullable()
                    ->placeholder('{ "mcq": 20, "short": 30, "long": 50 }')
                    ->helperText('e.g. { "mcq": 20, "short": 30, "long": 50 }'),
                TextInput::make('display_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_graded')
                    ->label('Is Graded')
                    ->default(false)
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
