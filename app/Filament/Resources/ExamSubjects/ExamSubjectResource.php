<?php

namespace App\Filament\Resources\ExamSubjects;

use App\Filament\Resources\ExamSubject\RelationManagers\ExamSchedulesRelationManager;
use App\Filament\Resources\ExamSubjects\Pages\CreateExamSubject;
use App\Filament\Resources\ExamSubjects\Pages\EditExamSubject;
use App\Filament\Resources\ExamSubjects\Pages\ListExamSubjects;
use App\Filament\Resources\ExamSubjects\Pages\ViewExamSubject;
use App\Filament\Resources\ExamSubjects\Schemas\ExamSubjectForm;
use App\Filament\Resources\ExamSubjects\Schemas\ExamSubjectInfolist;
use App\Filament\Resources\ExamSubjects\Tables\ExamSubjectsTable;
use App\Models\ExamSubject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExamSubjectResource extends Resource
{
    protected static ?string $model = ExamSubject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ExamSubjectForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExamSubjectInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamSubjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ExamSchedulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamSubjects::route('/'),
            'create' => CreateExamSubject::route('/create'),
            'view' => ViewExamSubject::route('/{record}'),
            'edit' => EditExamSubject::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }    
}
