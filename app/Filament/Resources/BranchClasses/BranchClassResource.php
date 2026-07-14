<?php

namespace App\Filament\Resources\BranchClasses;

use App\Filament\Resources\BranchClasses\Pages\CreateBranchClass;
use App\Filament\Resources\BranchClasses\Pages\EditBranchClass;
use App\Filament\Resources\BranchClasses\Pages\ListBranchClasses;
use App\Filament\Resources\BranchClasses\Pages\ViewBranchClass;
use App\Filament\Resources\BranchClasses\Schemas\BranchClassForm;
use App\Filament\Resources\BranchClasses\Schemas\BranchClassInfolist;
use App\Filament\Resources\BranchClasses\Tables\BranchClassesTable;
use App\Models\BranchClass;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BranchClassResource extends Resource
{
    protected static ?string $model = BranchClass::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'class';
    protected static ?string $modelLabel = 'Class';
    protected static ?string $pluralModelLabel = 'Classes';

    public static function form(Schema $schema): Schema
    {
        return BranchClassForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BranchClassInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchClassesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ClassSectionsRelationManager::class,
            RelationManagers\SectionSubjectsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBranchClasses::route('/'),
            'create' => CreateBranchClass::route('/create'),
            'view' => ViewBranchClass::route('/{record}'),
            'edit' => EditBranchClass::route('/{record}/edit'),
        ];
    }
}
