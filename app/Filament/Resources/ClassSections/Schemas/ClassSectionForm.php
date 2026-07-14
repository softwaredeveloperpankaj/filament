<?php

namespace App\Filament\Resources\ClassSections\Schemas;

use App\Models\BranchClass;
use App\Models\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ClassSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->live()->required(),

                Select::make('branch_class_id')
                    ->label('Class')
                    ->options(fn(Get $get) => BranchClass::where('branch_id', $get('branch_id'))->pluck('name','id'))
                    ->live()->required()
                    ->disabled(fn(Get $get) => !$get('branch_id')),

                Select::make('section_id')
                    ->label('Section')
                    ->options(fn(Get $get) => Section::where('branch_id', $get('branch_id'))->pluck('name','id'))
                    ->required()
                    ->disabled(fn(Get $get) => !$get('branch_class_id')),            

            ]);
    }
}
