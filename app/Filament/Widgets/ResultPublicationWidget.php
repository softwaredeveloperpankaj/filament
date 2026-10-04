<?php

namespace App\Filament\Widgets;

use App\Enums\ExamStatus;
use App\Models\Exam;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ResultPublicationWidget extends TableWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Exams Pending Result Publication')
            ->query(
                Exam::query()
                    ->whereIn('status', [ExamStatus::COMPLETED, ExamStatus::ONGOING])
                    ->withCount('results')
                    ->orderBy('end_date', 'desc')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')->label('Exam')->weight('bold'),
                TextColumn::make('branchClass.name')->label('Class'),
                TextColumn::make('results_count')->label('Calculated Results'),
                TextColumn::make('status')->badge(),
            ]);
    }
}