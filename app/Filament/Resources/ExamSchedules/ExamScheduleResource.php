<?php

namespace App\Filament\Resources\ExamSchedules;

use App\Filament\Resources\ExamSchedules\Pages\CreateExamSchedule;
use App\Filament\Resources\ExamSchedules\Pages\EditExamSchedule;
use App\Filament\Resources\ExamSchedules\Pages\ListExamSchedules;
use App\Filament\Resources\ExamSchedules\Pages\ViewExamSchedule;
use App\Filament\Resources\ExamSchedules\Schemas\ExamScheduleForm;
use App\Filament\Resources\ExamSchedules\Schemas\ExamScheduleInfolist;
use App\Filament\Resources\ExamSchedules\Tables\ExamSchedulesTable;
use App\Models\ExamSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExamScheduleResource extends Resource
{
    protected static ?string $model = ExamSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';

    protected static ?string $navigationLabel = 'Exam Schedule';

    protected static ?string $modelLabel = 'Exam Schedule';

    protected static ?string $pluralModelLabel = 'Exam Schedules';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'exam_date';

    protected static ?string $permissionPrefix = 'exam_schedule';

    // protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return ExamScheduleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExamScheduleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamSchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListExamSchedules::route('/'),
            'create' => CreateExamSchedule::route('/create'),
            'view'   => ViewExamSchedule::route('/{record}'),
            'edit'   => EditExamSchedule::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['exam.branch', 'examSubject.subject', 'invigilator']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
