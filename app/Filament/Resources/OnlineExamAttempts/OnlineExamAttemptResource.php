<?php

namespace App\Filament\Resources\OnlineExamAttempts;

use App\Filament\Resources\OnlineExamAttempts\Pages\CreateOnlineExamAttempt;
use App\Filament\Resources\OnlineExamAttempts\Pages\EditOnlineExamAttempt;
use App\Filament\Resources\OnlineExamAttempts\Pages\ListOnlineExamAttempts;
use App\Filament\Resources\OnlineExamAttempts\Pages\ViewOnlineExamAttempt;
use App\Filament\Resources\OnlineExamAttempts\Schemas\OnlineExamAttemptForm;
use App\Filament\Resources\OnlineExamAttempts\Schemas\OnlineExamAttemptInfolist;
use App\Filament\Resources\OnlineExamAttempts\Tables\OnlineExamAttemptsTable;
use App\Models\OnlineExamAttempt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OnlineExamAttemptResource extends Resource
{
    protected static ?string $model = OnlineExamAttempt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return OnlineExamAttemptForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OnlineExamAttemptInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OnlineExamAttemptsTable::configure($table);
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
            'index' => ListOnlineExamAttempts::route('/'),
            // 'create' => CreateOnlineExamAttempt::route('/create'),
            'view' => ViewOnlineExamAttempt::route('/{record}'),
            'edit' => EditOnlineExamAttempt::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function canCreate(): bool
    {
        return false;
    }    
}
