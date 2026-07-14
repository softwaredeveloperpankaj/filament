<?php

namespace App\Filament\Resources\SectionSubjects;

use App\Filament\Resources\SectionSubjects\Pages\CreateSectionSubject;
use App\Filament\Resources\SectionSubjects\Pages\EditSectionSubject;
use App\Filament\Resources\SectionSubjects\Pages\ListSectionSubjects;
use App\Filament\Resources\SectionSubjects\Pages\ViewSectionSubject;
use App\Filament\Resources\SectionSubjects\Schemas\SectionSubjectForm;
use App\Filament\Resources\SectionSubjects\Schemas\SectionSubjectInfolist;
use App\Filament\Resources\SectionSubjects\Tables\SectionSubjectsTable;
use App\Models\SectionSubject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SectionSubjectResource extends Resource
{
    protected static ?string $model = SectionSubject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SectionSubjectForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SectionSubjectInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SectionSubjectsTable::configure($table);
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
            'index' => ListSectionSubjects::route('/'),
            'create' => CreateSectionSubject::route('/create'),
            'view' => ViewSectionSubject::route('/{record}'),
            'edit' => EditSectionSubject::route('/{record}/edit'),
        ];
    }
}
