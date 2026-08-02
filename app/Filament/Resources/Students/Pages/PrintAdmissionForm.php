<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\FormTemplate;
use App\Support\PrintDataExtractor;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class PrintAdmissionForm extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected string $view = 'filament.pages.print-admission-form';

    protected ?string $heading = '';

    public string $layoutView = 'filament.pages.print-admission-form.default';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->record->load([
            'branch',
            'class',
            'section',
            'formTemplate',
        ]);

        $layout = $this->record->formTemplate?->form_layout ?: 'default';

        $this->layoutView = view()->exists(
            'filament.pages.print-admission-form.' . $layout
        )
            ? 'filament.pages.print-admission-form.' . $layout
            : 'filament.pages.print-admission-form.default';
    }
   
    public function getViewData(): array
    {
        return [
            'student'    => $this->getRecord(),
            'template'   => $this->getRecord()->formTemplate,
            'layoutView' => $this->layoutView,
        ];
    }

    protected function resolveLayout(?string $layout): string
    {
        if (! $layout) {
            return 'default';
        }

        $filePath = resource_path(
            'views/filament/pages/print-admission-form/' . $layout . '.blade.php'
        );

        return file_exists($filePath) ? $layout : 'default';
    }    

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url(StudentResource::getUrl('view', ['record' => $this->record]))
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),

            Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->action(fn () => null) // JS handles print
                ->extraAttributes(['onclick' => 'window.print()']),
        ];
    }
}