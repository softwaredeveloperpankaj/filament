<?php

namespace App\Filament\Resources\ExamQuestionPapers;

use App\Filament\Resources\ExamQuestionPapers\Pages\CreateExamQuestionPaper;
use App\Filament\Resources\ExamQuestionPapers\Pages\EditExamQuestionPaper;
use App\Filament\Resources\ExamQuestionPapers\Pages\ListExamQuestionPapers;
use App\Filament\Resources\ExamQuestionPapers\Pages\ViewExamQuestionPaper;
use App\Filament\Resources\ExamQuestionPapers\Schemas\ExamQuestionPaperForm;
use App\Filament\Resources\ExamQuestionPapers\Schemas\ExamQuestionPaperInfolist;
use App\Filament\Resources\ExamQuestionPapers\Tables\ExamQuestionPapersTable;
use App\Models\ExamQuestionPaper;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ExamQuestionPaperResource extends Resource
{
    protected static ?string $model = ExamQuestionPaper::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';

    protected static ?int $navigationSort = 6;    

    public static function form(Schema $schema): Schema
    {
        return ExamQuestionPaperForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExamQuestionPaperInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamQuestionPapersTable::configure($table);
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
            'index' => ListExamQuestionPapers::route('/'),
            'create' => CreateExamQuestionPaper::route('/create'),
            'view' => ViewExamQuestionPaper::route('/{record}'),
            'edit' => EditExamQuestionPaper::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }    
}
