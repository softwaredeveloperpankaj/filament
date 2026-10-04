<?php

namespace App\Imports;

use App\Models\QuestionBankItem;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class QuestionBankItemsImport extends Importer
{
    protected static ?string $model = QuestionBankItem::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('question_bank_id')->requiredMapping()->numeric(),
            ImportColumn::make('subject_id')->requiredMapping()->numeric(),
            ImportColumn::make('question_type')->requiredMapping(),
            ImportColumn::make('question_text')->requiredMapping(),
            ImportColumn::make('marks')->numeric(),
            ImportColumn::make('difficulty'),
        ];
    }

    public function resolveRecord(): ?QuestionBankItem
    {
        return new QuestionBankItem();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return 'Question bank items import has completed.';
    }
}