<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Filament\Resources\Students\Concerns\BuildsDynamicFormFields;
use App\Models\Branch;
use App\Models\BranchClass;
use App\Models\ClassSection;
use App\Models\FormTemplate;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentForm
{
    use BuildsDynamicFormFields;

    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->components([

                Section::make('Branch & Class')
                    ->schema([
                        Select::make('branch_id')
                            ->label('Branch')
                            ->options(Branch::pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('branch_class_id', null);
                                $set('section_id', null);
                                $set('form_template_id', null);
                            }),

                        Select::make('branch_class_id')
                            ->label('Class')
                            ->options(fn ($get) => 
                                $get('branch_id') 
                                    ? BranchClass::where('branch_id', $get('branch_id'))->pluck('name', 'id') 
                                    : []
                            )
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('section_id', null)),

                        Select::make('section_id')
                            ->label('Section')
                            ->options(fn ($get) => 
                                    $get('branch_class_id') 
                                        ? ClassSection::where('branch_class_id', $get('branch_class_id'))
                                            ->with('section')
                                            ->get()
                                            ->pluck('section.name', 'id') // Adjust relation name if needed
                                        : []
                                )
                            ->required()
                            ->live(),

                        Select::make('form_template_id')
                            ->label('Form Template')
                            ->options(fn ($get) => 
                                $get('branch_id')
                                    ? FormTemplate::query()
                                        ->where('branch_id', $get('branch_id'))
                                        ->where('status', 'published')
                                        ->where('is_active', true)
                                        ->pluck('name', 'id')
                                    : []
                            )
                            ->required()
                            ->live(),

                        TextInput::make('academic_year')
                            ->label('Academic Year')
                            ->default(now()->year . '-' . now()->addYear()->format('y'))
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                // Dynamic student detail fields saved into form_data JSON
                Section::make('More Details')
                    ->key(fn ($get) => 'student-fields-' . ($get('form_template_id') ?? 'none'))
                    ->schema(fn ($get) => static::getDynamicFormComponents($get('form_template_id')) ?: [
                        TextEntry::make('hint')
                            ->hiddenLabel()
                            ->default('Select a branch and form template above to load more fields.'),
                    ])
                    ->statePath('form_data')
                    ->columns(2)
                    ->columnSpanFull(), 

            ]);

    }
}
