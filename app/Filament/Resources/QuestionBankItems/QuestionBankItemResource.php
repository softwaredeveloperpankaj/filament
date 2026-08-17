<?php

namespace App\Filament\Resources\QuestionBankItems;

use App\Filament\Resources\QuestionBankItems\Pages\CreateQuestionBankItem;
use App\Filament\Resources\QuestionBankItems\Pages\EditQuestionBankItem;
use App\Filament\Resources\QuestionBankItems\Pages\ListQuestionBankItems;
use App\Filament\Resources\QuestionBankItems\Pages\ViewQuestionBankItem;
use App\Filament\Resources\QuestionBankItems\Schemas\QuestionBankItemForm;
use App\Filament\Resources\QuestionBankItems\Schemas\QuestionBankItemInfolist;
use App\Filament\Resources\QuestionBankItems\Tables\QuestionBankItemsTable;
use App\Models\QuestionBankItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class QuestionBankItemResource extends Resource
{
    protected static ?string $model = QuestionBankItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return QuestionBankItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuestionBankItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionBankItemsTable::configure($table);
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
            'index' => ListQuestionBankItems::route('/'),
            'create' => CreateQuestionBankItem::route('/create'),
            'view' => ViewQuestionBankItem::route('/{record}'),
            'edit' => EditQuestionBankItem::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return str($record->question_text)->limit(60);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['question_text', 'tags'];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
