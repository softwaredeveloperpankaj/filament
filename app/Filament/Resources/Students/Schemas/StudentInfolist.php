<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Filament\Resources\Students\Concerns\BuildsDynamicFormFields;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentInfolist
{
    use BuildsDynamicFormFields;

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(function ($record) {
            return [
                Section::make('Student Details')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),

                        TextEntry::make('branch.name')
                            ->label('Branch')
                            ->placeholder('—'),

                        TextEntry::make('registration_number')
                            ->label('Registration No.')
                            ->placeholder('—'),

                        TextEntry::make('roll_no')
                            ->label('Roll No.')
                            ->placeholder('—'),

                        TextEntry::make('class.name')
                            ->label('Class')
                            ->placeholder('—'),

                        TextEntry::make('section.name')
                            ->label('Section')
                            ->placeholder('—'),

                        TextEntry::make('academic_year')
                            ->label('Academic Year')
                            ->placeholder('—'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'pending' => 'warning',
                                'confirmed' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            })
                            ->placeholder('—'),

                        TextEntry::make('admission_date')
                            ->label('Admission Date')
                            ->date()
                            ->placeholder('—'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime()
                            ->placeholder('—'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                    ...(
                        $record?->formTemplate
                        ? static::getDynamicInfolistEntries($record->formTemplate)
                        : []
                    ),
            ];

        });
    }
}
