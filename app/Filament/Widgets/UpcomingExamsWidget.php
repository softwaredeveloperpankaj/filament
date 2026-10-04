<?php

namespace App\Filament\Widgets;

use App\Models\Exam;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingExamsWidget extends TableWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Exam::query()
                    ->where('end_date', '>=', now()->toDateString())
                    ->orderBy('start_date')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')->label('Exam Name')->weight('bold'),
                TextColumn::make('branchClass.name')->label('Class'),
                TextColumn::make('section.name')->label('Section')->placeholder('All Sections'),
                TextColumn::make('mode')->badge(),
                TextColumn::make('start_date')->date(),
                TextColumn::make('end_date')->date(),
                TextColumn::make('status')->badge(),
            ]);
    }
}