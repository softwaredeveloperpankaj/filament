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

            ExportColumn::make('teacher_profile.employee_id')
                ->label('Employee ID'),

            ExportColumn::make('teacher_profile.phone')
                ->label('Phone'),

            ExportColumn::make('teacher_profile.date_of_birth')
                ->label('Date of Birth'),

            ExportColumn::make('teacher_profile.gender')
                ->label('Gender'),

            ExportColumn::make('teacher_profile.profile_photo')
                ->label('Profile Photo'),

            ExportColumn::make('teacher_profile.qualification')
                ->label('Qualification'),

            ExportColumn::make('teacher_profile.specialization')
                ->label('Specialization'),

            ExportColumn::make('teacher_profile.joining_date')
                ->label('Joining Date'),

            ExportColumn::make('teacher_profile.address')
                ->label('Address'),

            ExportColumn::make('teacher_profile.status')
                ->label('Status'),

            ExportColumn::make('teacher_profile.salary')
                ->label('Salary'),

            ExportColumn::make('teacher_profile.branch.name')
                ->label('Branch'),

            ExportColumn::make('teacher_profile.subject.name')
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