<?php

namespace App\Filament\Resources\Exports;

use App\Filament\Resources\Exports\Pages\CreateExport;
use App\Filament\Resources\Exports\Pages\EditExport;
use App\Filament\Resources\Exports\Pages\ListExports;
use App\Filament\Resources\Exports\Pages\ViewExport;
use App\Filament\Resources\Exports\RelationManagers\FailedRowsRelationManager;
use App\Filament\Resources\Exports\Schemas\ExportForm;
use App\Filament\Resources\Exports\Schemas\ExportInfolist;
use App\Filament\Resources\Exports\Tables\ExportsTable;
use App\Models\Export;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExportResource extends Resource
{
    protected static ?string $model = Export::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // public static function form(Schema $schema): Schema
    // {
    //     return ExportForm::configure($schema);
    // }

    public static function infolist(Schema $schema): Schema
    {
        return ExportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // FailedRowsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExports::route('/'),
            // 'create' => CreateExport::route('/create'),
            'view' => ViewExport::route('/{record}'),
            // 'edit' => EditExport::route('/{record}/edit'),
        ];
    }
}
