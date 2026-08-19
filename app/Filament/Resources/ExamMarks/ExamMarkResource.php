<?php

namespace App\Filament\Resources\ExamMarks;

use App\Filament\Resources\ExamMarks\Pages\CreateExamMark;
use App\Filament\Resources\ExamMarks\Pages\EditExamMark;
use App\Filament\Resources\ExamMarks\Pages\ListExamMarks;
use App\Filament\Resources\ExamMarks\Pages\ViewExamMark;
use App\Filament\Resources\ExamMarks\Schemas\ExamMarkForm;
use App\Filament\Resources\ExamMarks\Schemas\ExamMarkInfolist;
use App\Filament\Resources\ExamMarks\Tables\ExamMarksTable;
use App\Models\ExamMark;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExamMarkResource extends Resource
{
    protected static ?string $model = ExamMark::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';

    protected static ?int $navigationSort = 8;    

    public static function form(Schema $schema): Schema
    {
        return ExamMarkForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExamMarkInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamMarksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamMarks::route('/'),
            'create' => CreateExamMark::route('/create'),
            'view' => ViewExamMark::route('/{record}'),
            'edit' => EditExamMark::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }    
}
