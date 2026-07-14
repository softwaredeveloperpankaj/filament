<?php

namespace App\Filament\Resources\BranchClasses\RelationManagers;

use App\Filament\Resources\BranchClasses\BranchClassResource;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherProfile;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SectionSubjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'sectionSubjects';

    // protected static ?string $relatedResource = BranchClassResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject.name')
            ->columns([
                TextColumn::make('section.name')
                    ->label('Section'),
                TextColumn::make('subject.name')
                    ->label('Subject'),
                TextColumn::make('subject.code')
                    ->label('Code')->badge(),
                TextColumn::make('teacher.user.name')
                    ->label('Teacher')
                    ->default('— Not Assigned —'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('section_id')
                    ->label('Section')
                    ->options(fn () => Section::where('branch_id', $this->getOwnerRecord()->branch_id)
                        ->pluck('name', 'id'))
                    ->required(),

                Select::make('subject_id')
                    ->label('Subject')
                    ->options(fn () => Subject::where('branch_id', $this->getOwnerRecord()->branch_id)
                        ->pluck('name', 'id'))
                    ->required(),

                Select::make('teacher_profile_id')
                    ->label('Teacher')
                    ->options(fn () => TeacherProfile::where('branch_id', $this->getOwnerRecord()->branch_id)
                        ->with('user')->get()->pluck('user.name', 'id'))
                    ->nullable(),

                Hidden::make('branch_id')
                    ->default(fn () => $this->getOwnerRecord()->branch_id),

                Hidden::make('branch_class_id')
                    ->default(fn () => $this->getOwnerRecord()->id),
            ]);
    }    
}
