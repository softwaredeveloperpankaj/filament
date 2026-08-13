<?php

namespace App\Filament\Resources\ExamSubjects\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExamSubjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('exam.name')
                    ->label('Exam'),
                TextEntry::make('subject.name')
                    ->label('Subject'),
                TextEntry::make('max_marks')
                    ->numeric(),
                TextEntry::make('pass_marks')
                    ->numeric(),
                TextEntry::make('theory_marks')
                    ->numeric(),
                TextEntry::make('practical_marks')
                    ->numeric(),
                TextEntry::make('duration_minutes')
                    ->numeric(),
                IconEntry::make('is_graded')
                    ->boolean(),
                TextEntry::make('display_order')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
