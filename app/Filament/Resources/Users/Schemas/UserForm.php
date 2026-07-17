<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        TextInput::make('name')
                            ->placeholder('Full name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->placeholder('Email address')
                            ->email()
                            ->required(),
                        Select::make('roles')
                            ->placeholder('Select roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->reactive()
                            ->required()
                            ->dehydrated(true),
                        // DateTimePicker::make('email_verified_at'),
                        TextInput::make('password')
                            ->label('Password')
                            ->placeholder('Password')
                            ->password()
                            ->required()
                            ->revealable()
                            ->hiddenOn('edit'),
                    ]),
                Section::make('Teacher Profile')
                    ->schema([
                        // TextInput::make('teacherProfile.employee_id')
                        //     ->label('Employee ID')
                        //     ->placeholder('e.g. TCH-001')
                        //     ->unique(
                        //         table: 'teacher_profiles',
                        //         column: 'employee_id',
                        //         ignoreRecord: true
                        //     ),
                        Select::make('teacherProfile.branch_id')
                            ->label('Assigned Branch')
                            ->relationship('teacherProfile.branch', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('teacherProfile.subject_id', null))
                            ->required(),

                        Select::make('teacherProfile.subject_id')
                            ->label('Assigned Subject')
                            ->relationship(
                                name: 'teacherProfile.subject',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                    ->when(
                                        $get('teacherProfile.branch_id'),
                                        fn (Builder $query, $branchId) => $query->where('branch_id', $branchId),
                                        fn (Builder $query) => $query->whereRaw('1 = 0')
                                    )
                            )
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Get $get) => blank($get('teacherProfile.branch_id')))
                            ->required(),

                        TextInput::make('teacherProfile.phone')
                            ->label('Phone Number')
                            ->tel()
                            ->required(fn (Get $get) => self::hasTeacherRole($get)),

                        DatePicker::make('teacherProfile.date_of_birth')
                            ->label('Date of Birth')
                            ->helperText('Applicants must be 18 years of age or older.')
                            ->maxDate(now()->subYears(18))
                            ->required(fn (Get $get) => self::hasTeacherRole($get)),
                            
                        Select::make('teacherProfile.gender')
                            ->label('Gender')
                            ->options([
                                'male'   => 'Male',
                                'female' => 'Female',
                                'other'  => 'Other',
                            ])
                            ->required(fn (Get $get) => self::hasTeacherRole($get)),

                        TextInput::make('teacherProfile.qualification')
                            ->label('Qualification')
                            ->placeholder('e.g. B.Ed, M.A.')
                            ->required(fn (Get $get) => self::hasTeacherRole($get)),
                            
                        TextInput::make('teacherProfile.specialization')
                            ->label('Subject Specialization')
                            ->placeholder('e.g. Mathematics')
                            ->required(fn (Get $get) => self::hasTeacherRole($get)),

                        DatePicker::make('teacherProfile.joining_date')
                            ->label('Joining Date')
                            ->default(now())
                            ->required(fn (Get $get) => self::hasTeacherRole($get)),

                        Select::make('teacherProfile.status')
                            ->label('Status')
                            ->options([
                                'active'   => 'Active',
                                'inactive' => 'Inactive',
                                'on_leave' => 'On Leave',
                            ])
                            ->default('active')
                            ->required(fn (Get $get) => self::hasTeacherRole($get)),

                        TextInput::make('teacherProfile.salary')
                            ->label('Monthly Salary')
                            ->numeric()
                            ->prefix('₹'),

                        Textarea::make('teacherProfile.address')
                            ->label('Address')
                            ->placeholder('Address'),

                        FileUpload::make('teacherProfile.profile_photo')
                            ->label('Profile Photo')
                            ->image()
                            ->disk('public')
                            ->directory('teacher-photos'),
                    ])
                    ->visible(function (Get $get): bool {
                        // Get selected role IDs from the form
                        $selectedRoleIds = $get('roles') ?? [];

                        if (empty($selectedRoleIds)) {
                            return false;
                        }

                        // Check if any selected role is named 'teacher'
                        return Role::whereIn('id', $selectedRoleIds)
                            ->where('name', 'teacher')
                            ->exists();
                    }),                
            ]);
    }

    protected static function hasTeacherRole(Get $get): bool
    {
        $selectedRoleIds = $get('roles') ?? [];

        if (empty($selectedRoleIds)) {
            return false;
        }

        return Role::whereIn('id', $selectedRoleIds)
            ->where('name', 'teacher')
            ->exists();
    }    
}
