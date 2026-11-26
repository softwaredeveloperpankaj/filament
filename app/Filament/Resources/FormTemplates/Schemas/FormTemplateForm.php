<?php

namespace App\Filament\Resources\FormTemplates\Schemas;

use App\Models\FormTemplate;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class FormTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Template Information')
                    ->description('Manage the admission form template, owner, status, and printable configuration.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Section::make('Assignment Details')
                            ->description('Select the branch and responsible user for this template.')
                            ->icon('heroicon-o-building-office-2')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    Select::make('branch_id')
                                        ->label('Select Branch')
                                        ->relationship('branch', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(fn (Set $set) => $set('user_id', null))
                                        ->required()
                                        ->rule(function ($record) {
                                            return Rule::unique('form_templates', 'branch_id')
                                                ->ignore($record?->id);
                                        })
                                        ->validationMessages([
                                            'unique' => 'This branch already has a form template.',
                                        ]),

                                    Select::make('user_id')
                                        ->label('Select User')
                                        ->options(function (Get $get): array {
                                            $branchId = $get('branch_id');

                                            if (! $branchId) {
                                                return [];
                                            }

                                            return User::whereHas('branch', function ($query) use ($branchId) {
                                                    $query->where('branches.id', $branchId);
                                                })
                                                ->pluck('name', 'id')
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->placeholder('Select a user'),
                                ]),
                            ])
                            ->collapsible(),

                        Section::make('Template Details')
                            ->description('Set the template name, registration prefix, type, and status.')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    TextInput::make('registration_serial')
                                        ->required(),

                                    TextInput::make('name')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Set $set, ?string $state, ?FormTemplate $record) {
                                            $set('slug', self::generateUniqueSlug($state ?? '', $record?->id));
                                        }),

                                    TextInput::make('slug')
                                        ->required()
                                        ->readOnly()
                                        ->dehydrated(),

                                    Select::make('type')
                                        ->options(['admission' => 'Admission'])
                                        ->default('admission')
                                        ->required(),

                                    Select::make('status')
                                        ->options(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'])
                                        ->default('draft')
                                        ->required(),
                                ]),
                            ])
                            ->collapsible(),

                        Section::make('Print & Roll Number Settings')
                            ->description('Choose the printable layout and how roll numbers are generated.')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'lg' => 3,
                                ])->schema([
                                    Select::make('form_layout')
                                        ->label('Print Layout')
                                        ->options(self::printLayoutOptions())
                                        ->default('default')
                                        ->required()
                                        ->helperText('Choose the printable admission form layout for this branch.'),

                                    Select::make('rollno_generation_scope')
                                        ->label('Roll Number Generated By')
                                        ->options(['class' => 'Class', 'section' => 'Section'])
                                        ->default('section')
                                        ->required(),

                                    Toggle::make('is_active')
                                        ->required()
                                        ->helperText('Controls whether this form template is active.'),
                                ]),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (
            FormTemplate::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    public static function printLayoutOptions(): array
    {
        $viewPath = resource_path('views/filament/print-admission-form');

        if (! is_dir($viewPath)) {
            return ['default' => 'default'];
        }

        $files = glob($viewPath . '/*.blade.php') ?: [];

        return collect($files)
            ->mapWithKeys(function ($filePath) {
                $filename = basename($filePath, '.blade.php');

                return [$filename => ucfirst($filename)];
            })
            ->sortKeysUsing(fn ($a) => $a === 'default' ? -1 : 1)
            ->all() ?: ['default' => 'default'];
    }
}
