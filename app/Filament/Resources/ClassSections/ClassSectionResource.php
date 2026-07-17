<?php

namespace App\Filament\Resources\ClassSections;

use App\Filament\Resources\ClassSections\Pages\CreateClassSection;
use App\Filament\Resources\ClassSections\Pages\EditClassSection;
use App\Filament\Resources\ClassSections\Pages\ListClassSections;
use App\Filament\Resources\ClassSections\Pages\ViewClassSection;
use App\Filament\Resources\ClassSections\Schemas\ClassSectionForm;
use App\Filament\Resources\ClassSections\Schemas\ClassSectionInfolist;
use App\Filament\Resources\ClassSections\Tables\ClassSectionsTable;
use App\Models\ClassSection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClassSectionResource extends Resource
{
    protected static ?string $model = ClassSection::class;
    protected static ?string $modelLabel = 'Class Section Relation';
    protected static ?string $pluralModelLabel = 'Class Section Relation';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|\UnitEnum|null $navigationGroup = 'Academics';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ClassSectionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClassSectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassSectionsTable::configure($table);
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
            'index' => ListClassSections::route('/'),
            'create' => CreateClassSection::route('/create'),
            'view' => ViewClassSection::route('/{record}'),
            'edit' => EditClassSection::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }    
}
