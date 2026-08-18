<?php

namespace App\Filament\Resources\ExamQuestionPapers\Pages;

use App\Filament\Resources\ExamQuestionPapers\ExamQuestionPaperResource;
use App\Models\QuestionBank;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateExamQuestionPaper extends CreateRecord
{
    protected static string $resource = ExamQuestionPaperResource::class;

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->hidden(true);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['total_marks'] = collect($data['selected_questions'] ?? [])->sum();
        return $data;
    }

    public function mount(): void
    {
        parent::mount();

        if ($bankId = request()->query('question_bank_id')) {
            $bank = QuestionBank::find($bankId);
            if ($bank) {
                $this->form->fill([
                    'question_bank_id' => $bank->id,
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }    
}
