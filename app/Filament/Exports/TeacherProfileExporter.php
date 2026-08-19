<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class TeacherProfileExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('User ID'),

            ExportColumn::make('name')
                ->label('Name'),

            ExportColumn::make('email')
                ->label('Email'),

            ExportColumn::make('teacherProfile.employee_id')
                ->label('Employee ID'),

            ExportColumn::make('teacherProfile.phone')
                ->label('Phone'),

            ExportColumn::make('teacherProfile.date_of_birth')
                ->label('Date of Birth'),

            ExportColumn::make('teacherProfile.gender')
                ->label('Gender'),

            ExportColumn::make('teacherProfile.profile_photo')
                ->label('Profile Photo'),

            ExportColumn::make('teacherProfile.qualification')
                ->label('Qualification'),

            ExportColumn::make('teacherProfile.specialization')
                ->label('Specialization'),

            ExportColumn::make('teacherProfile.joining_date')
                ->label('Joining Date'),

            ExportColumn::make('teacherProfile.address')
                ->label('Address'),

            ExportColumn::make('teacherProfile.status')
                ->label('Status'),

            ExportColumn::make('teacherProfile.salary')
                ->label('Salary'),

            ExportColumn::make('teacherProfile.branch.name')
                ->label('Branch'),

            ExportColumn::make('teacherProfile.subject.name')
                ->label('Subject'),

            ExportColumn::make('created_at')
                ->label('User Created At'),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query
            ->with([
                'teacherProfile.branch',
                'teacherProfile.subject',
            ])
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', 'teacher');
            })
            ->whereHas('teacherProfile');
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your teacher profile export has completed and '
            . Number::format($export->successful_rows)
            . ' '
            . str('row')->plural($export->successful_rows)
            . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '
                . Number::format($failedRowsCount)
                . ' '
                . str('row')->plural($failedRowsCount)
                . ' failed to export.';
        }

        return $body;
    }
}