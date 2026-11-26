<?php

namespace App\Filament\Resources\FormTemplates\Schemas;

use App\Models\FormTemplate;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FormTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Template Information')
                    ->description('Template identification, ownership, status, and printable layout details.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Section::make('Reference Details')
                            ->description('Basic information about this admission form template.')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    TextEntry::make('name')
                                        ->label('Template Name')
                                        ->icon('heroicon-o-document-text')
                                        ->weight('bold')
                                        ->size('lg')
                                        ->copyable()
                                        ->copyMessage('Template name copied')
                                        ->placeholder('-'),

                                    TextEntry::make('branch.name')
                                        ->label('Branch')
                                        ->icon('heroicon-o-building-office')
                                        ->weight('medium')
                                        ->placeholder('-'),

                                    TextEntry::make('user.name')
                                        ->label('Created By')
                                        ->icon('heroicon-o-user')
                                        ->placeholder('-'),

                                    TextEntry::make('registration_serial')
                                        ->label('Registration Prefix')
                                        ->badge()
                                        ->color('primary')
                                        ->icon('heroicon-o-hashtag')
                                        ->placeholder('-'),

                                    TextEntry::make('type')
                                        ->label('Template Type')
                                        ->badge()
                                        ->color('info')
                                        ->placeholder('-'),

                                    TextEntry::make('status')
                                        ->label('Template Status')
                                        ->badge()
                                        ->color(fn (?string $state): string => match ($state) {
                                            'published' => 'success',
                                            'draft' => 'warning',
                                            'archived' => 'gray',
                                            default => 'gray',
                                        })
                                        ->placeholder('-'),

                                    IconEntry::make('is_active')
                                        ->label('Template Active')
                                        ->boolean()
                                        ->trueIcon('heroicon-o-check-circle')
                                        ->falseIcon('heroicon-o-x-circle')
                                        ->trueColor('success')
                                        ->falseColor('gray'),

                                    TextEntry::make('slug')
                                        ->label('Form Slug')
                                        ->copyable()
                                        ->copyMessage('Slug copied')
                                        ->icon('heroicon-o-link')
                                        ->placeholder('-')
                                        ->columnSpan([
                                            'sm' => 1,
                                            'lg' => 2,
                                        ]),
                                ]),
                            ])
                            ->collapsible(),

                        Section::make('Form Configuration')
                            ->description('Printing and roll number generation settings.')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    TextEntry::make('form_layout')
                                        ->label('Print Layout')
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-o-printer')
                                        ->placeholder('-'),

                                    Actions::make([
                                        Action::make('view_print_layout')
                                            ->label('View Print Layout')
                                            ->icon('heroicon-o-printer')
                                            ->color('info')
                                            ->button()
                                            ->url(fn ($record) => route('students.print-layout.blank', ['form_template' => $record->id]))
                                            ->openUrlInNewTab(),
                                        ])
                                        ->label('Print Layout'),
                                    TextEntry::make('rollno_generation_scope')
                                        ->label('Roll Number Generated By')
                                        ->formatStateUsing(
                                            fn (?string $state): string => match ($state) {
                                                'class' => 'Class',
                                                'section' => 'Section',
                                                default => $state ?? '-',
                                            }
                                        )
                                        ->badge()
                                        ->color('primary')
                                        ->icon('heroicon-o-hashtag')
                                        ->placeholder('-'),

                                    TextEntry::make('active_version_id')
                                        ->label('Active Version')
                                        ->numeric()
                                        ->badge()
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->collapsible(),

                        Section::make('Record Information')
                            ->description('System timestamps and template history.')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    TextEntry::make('created_at')
                                        ->label('Created')
                                        ->dateTime('d M Y, h:i A')
                                        ->placeholder('-'),

                                    TextEntry::make('updated_at')
                                        ->label('Last Updated')
                                        ->dateTime('d M Y, h:i A')
                                        ->placeholder('-'),
                                ]),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}