<?php

namespace App\Filament\Resources\FormTemplates\Pages;

use App\Filament\Pages\ExportFormTemplate;
use App\Filament\Resources\FormTemplates\FormTemplateResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFormTemplates extends ListRecords
{
    protected static string $resource = FormTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportedFiles')
                ->label('Exported Form Files')
                ->icon('heroicon-o-folder-arrow-down')
                ->color('warning')
                ->url(fn ($record): string => ExportFormTemplate::getUrl()),
            CreateAction::make(),
        ];
    }
}
