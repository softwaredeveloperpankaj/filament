<?php

namespace App\Filament\Resources\SectionSubjects\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SectionSubjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Assignment Details')
                    ->description('Class, section and subject assignment information.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([
                                TextEntry::make('branch.name')
                                    ->label('Branch')
                                    ->icon('heroicon-o-building-office-2')
                                    ->weight('medium'),

                                TextEntry::make('branchClass.name')
                                    ->label('Class')
                                    ->icon('heroicon-o-academic-cap')
                                    ->weight('medium'),

                                TextEntry::make('section.name')
                                    ->label('Section')
                                    ->icon('heroicon-o-rectangle-group')
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('subject.name')
                                    ->label('Subject')
                                    ->icon('heroicon-o-book-open')
                                    ->weight('medium'),

                                TextEntry::make('subject.code')
                                    ->label('Subject Code')
                                    ->badge()
                                    ->icon('heroicon-o-hashtag')
                                    ->color('primary'),

                                TextEntry::make('teacher.user.name')
                                    ->label('Teacher')
                                    ->icon('heroicon-o-user')
                                    ->default('— Not Assigned —')
                                    ->badge()
                                    ->color(fn (?string $state): string =>
                                        $state === '— Not Assigned —'
                                            ? 'warning'
                                            : 'success'
                                    ),
                            ]),
                    ]),

                Section::make('Assignment Information')
                    ->description('Details about when this subject was assigned.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Assigned On')
                                    ->dateTime('d M Y, h:i A')
                                    ->placeholder('—'),

                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('d M Y, h:i A')
                                    ->placeholder('—'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}