<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Filament\Resources\Students\Concerns\BuildsDynamicFormFields;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentInfolist
{
    use BuildsDynamicFormFields;

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(function ($record): array {
            $dynamicSections = $record?->formTemplate
                ? static::getDynamicInfolistEntries($record->formTemplate)
                : [];

            return [
                Section::make('Student Information')
                    ->description('Student identification, academic placement, admission status, and dynamic form details.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Section::make('Reference Details')
                            ->description('Core enrollment identifiers, branch, and admission status.')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    TextEntry::make('registration_number')
                                        ->label('Registration No.')
                                        ->icon('heroicon-o-hashtag')
                                        ->weight('bold')
                                        ->size('lg')
                                        ->copyable()
                                        ->copyMessage('Registration number copied')
                                        ->placeholder('-'),

                                    TextEntry::make('roll_no')
                                        ->label('Roll No.')
                                        ->icon('heroicon-o-user-circle')
                                        ->weight('medium')
                                        ->copyable()
                                        ->copyMessage('Roll number copied')
                                        ->placeholder('-'),

                                    TextEntry::make('status')
                                        ->label('Admission Status')
                                        ->badge()
                                        ->formatStateUsing(fn (?string $state): string => $state ? str($state)->headline()->toString() : '-')
                                        ->icon(fn (?string $state): ?string => match ($state) {
                                            'pending' => 'heroicon-o-clock',
                                            'confirmed' => 'heroicon-o-check-circle',
                                            'rejected' => 'heroicon-o-x-circle',
                                            default => null,
                                        })
                                        ->color(fn (?string $state): string => match ($state) {
                                            'pending' => 'warning',
                                            'confirmed' => 'success',
                                            'rejected' => 'danger',
                                            default => 'gray',
                                        })
                                        ->placeholder('-'),

                                    TextEntry::make('branch.name')
                                        ->label('Branch')
                                        ->icon('heroicon-o-building-office')
                                        ->weight('medium')
                                        ->placeholder('-'),

                                    TextEntry::make('class.name')
                                        ->label('Class')
                                        ->icon('heroicon-o-book-open')
                                        ->badge()
                                        ->color('info')
                                        ->placeholder('-'),

                                    TextEntry::make('section.name')
                                        ->label('Section')
                                        ->icon('heroicon-o-squares-2x2')
                                        ->badge()
                                        ->color('primary')
                                        ->placeholder('-'),

                                    TextEntry::make('academic_year')
                                        ->label('Academic Year')
                                        ->icon('heroicon-o-calendar')
                                        ->badge()
                                        ->color('info')
                                        ->placeholder('-'),

                                    TextEntry::make('admission_date')
                                        ->label('Admission Date')
                                        ->icon('heroicon-o-calendar-days')
                                        ->date('d M Y')
                                        ->placeholder('-')
                                        ->columnSpan([
                                            'sm' => 1,
                                            'lg' => 2,
                                        ]),
                                ]),
                            ])
                            ->collapsible(),

                        ...$dynamicSections,

                        Section::make('Record Information')
                            ->description('System timestamps and student record history.')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    TextEntry::make('created_at')
                                        ->label('Created')
                                        ->icon('heroicon-o-clock')
                                        ->dateTime('d M Y, h:i A')
                                        ->placeholder('-'),

                                    TextEntry::make('updated_at')
                                        ->label('Last Updated')
                                        ->icon('heroicon-o-arrow-path')
                                        ->dateTime('d M Y, h:i A')
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ];
        });
    }
}