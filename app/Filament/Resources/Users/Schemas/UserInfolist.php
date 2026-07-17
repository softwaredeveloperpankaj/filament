<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'md' => 12,
                ])
                ->columnSpanFull()
                ->schema([
                    
                    // ==========================================
                    // LEFT COLUMN: Core Details (8 of 12 Columns)
                    // ==========================================
                    Grid::make(1)
                        ->schema([
                            // --- User Account Information ---
                            Section::make('User Information')
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('name')
                                        ->weight('semibold'),
                                    TextEntry::make('email')
                                        ->label('Email address')
                                        ->icon('heroicon-m-envelope'),
                                    
                                    TextEntry::make('roles')
                                        ->label('Roles')
                                        ->badge()
                                        ->getStateUsing(fn ($record) => $record->getRoleNames()->first() ?? 'User')
                                        ->color(fn ($state) => match ($state) {
                                            'super_admin' => 'danger',
                                            'admin' => 'success',
                                            'teacher' => 'info',
                                            default => 'secondary',
                                        })
                                        ->formatStateUsing(fn ($state) => str($state)->replace('_', ' ')->title()),

                                    TextEntry::make('created_at')
                                        ->label('Account Created')
                                        ->dateTime('d M Y, h:i A')
                                        ->placeholder('-'),
                                ]),

                            // --- Teacher Workspace Profile Details ---
                            Section::make('Teacher Profile Details')
                                ->columns(3)
                                ->visible(fn ($record) => $record?->hasRole('teacher') ?? false)
                                ->schema([
                                    TextEntry::make('teacherProfile.employee_id')
                                        ->label('Employee ID')
                                        ->icon('heroicon-m-identification')
                                        ->weight('bold')
                                        ->color('primary')
                                        ->iconColor('primary')
                                        ->placeholder('-'),

                                    TextEntry::make('teacherProfile.branch.name')
                                        ->label('Branch')
                                        ->weight('bold')
                                        ->color('primary')
                                        ->placeholder('-'),

                                    TextEntry::make('teacherProfile.subject.name')
                                        ->label('Subject')
                                        ->weight('bold')
                                        ->color('primary')
                                        ->placeholder('-'),

                                        TextEntry::make('teacherProfile.phone')
                                        ->label('Phone Number')
                                        ->icon('heroicon-m-phone')
                                        ->placeholder('-'),

                                    TextEntry::make('teacherProfile.date_of_birth')
                                        ->label('Date of Birth')
                                        ->date('d M Y')
                                        ->placeholder('-'),

                                    TextEntry::make('teacherProfile.gender')
                                        ->label('Gender')
                                        ->formatStateUsing(fn ($state) => ucfirst($state))
                                        ->placeholder('-'),

                                    TextEntry::make('teacherProfile.specialization')
                                        ->label('Subject Specialization')
                                        ->placeholder('-'),

                                    TextEntry::make('teacherProfile.qualification')
                                        ->label('Qualification')
                                        ->placeholder('-'),

                                    TextEntry::make('teacherProfile.joining_date')
                                        ->label('Joining Date')
                                        ->date('d M Y')
                                        ->placeholder('-'),

                                    TextEntry::make('teacherProfile.salary')
                                        ->label('Monthly Salary')
                                        ->money('INR')
                                        ->placeholder('-'),
                                ]),
                        ])
                        ->columnSpan(['md' => 9]),

                    // ==========================================
                    // RIGHT COLUMN: Sidebar Metadata (4 of 12 Columns)
                    // ==========================================
                    Grid::make(1)
                        ->visible(fn ($record) => $record?->hasRole('teacher') ?? false)
                        ->schema([
                            
                            // Dedicated Profile Picture Section
                            Section::make('Profile Photo')
                                ->columns(1)
                                ->schema([
                                    ImageEntry::make('teacherProfile.profile_photo')
                                        ->disk('public')
                                        ->imageHeight(160)
                                        ->imageWidth(160)
                                        ->square()
                                        ->state(function ($record) {
                                            $photo = $record->teacherProfile?->profile_photo;
                                            // If photo exists, return it. If not, generate a dynamic UI initial avatar line
                                            return $photo ?: 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF';
                                        }),
                                ]),

                            // Status Section consolidated right underneath the photo
                            Section::make('Employment Status')
                                ->columns(1)
                                ->schema([
                                    TextEntry::make('teacherProfile.status')
                                        ->badge()
                                        ->extraAttributes(['class' => 'w-full flex justify-center text-center'])
                                        ->color(fn ($state) => match ($state) {
                                            'active' => 'success',
                                            'inactive' => 'danger',
                                            'on_leave' => 'warning',
                                            default => 'secondary',
                                        })
                                        ->formatStateUsing(fn ($state) => str($state)->replace('_', ' ')->title()),
                                ]),
                        ])
                        ->columnSpan(['md' => 3]),

                ]),
            ]);
    }
}