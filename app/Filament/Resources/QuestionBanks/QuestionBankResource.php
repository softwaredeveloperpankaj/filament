<?php

namespace App\Filament\Resources\QuestionBanks;

use App\Filament\Resources\QuestionBanks\RelationManagers\QuestionBankItemsRelationManager;
use App\Filament\Resources\QuestionBanks\Pages\CreateQuestionBank;
use App\Filament\Resources\QuestionBanks\Pages\EditQuestionBank;
use App\Filament\Resources\QuestionBanks\Pages\ListQuestionBanks;
use App\Filament\Resources\QuestionBanks\Pages\ViewQuestionBank;
use App\Filament\Resources\QuestionBanks\Schemas\QuestionBankForm;
use App\Filament\Resources\QuestionBanks\Schemas\QuestionBankInfolist;
use App\Filament\Resources\QuestionBanks\Tables\QuestionBanksTable;
use App\Models\QuestionBank;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class QuestionBankResource extends Resource
{
    protected static ?string $model = QuestionBank::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Examinations';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return QuestionBankForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuestionBankInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionBanksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            QuestionBankItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuestionBanks::route('/'),
            'create' => CreateQuestionBank::route('/create'),
            'view' => ViewQuestionBank::route('/{record}'),
            'edit' => EditQuestionBank::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->with(['branch', 'subject', 'creator'])
    //         ->when(
    //             Auth::user()?->branch_id && !Auth::user()->hasRole('super_admin'),
    //             fn($q) => $q->where('branch_id', Auth::user()->branch_id)
    //         );
    // }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }    
}
