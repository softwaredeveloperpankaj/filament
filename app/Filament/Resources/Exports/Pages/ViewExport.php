<?php

namespace App\Filament\Resources\Exports\Pages;

use App\Filament\Resources\Exports\ExportResource;
use Filament\Actions\Action;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\URL;

class ViewExport extends ViewRecord
{
    protected static string $resource = ExportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),

            Action::make('download')
                ->label('Download Export File')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (): string => URL::signedRoute(
                    'filament.exports.download',
                    [
                        'export'    => $this->getRecord()->getKey(),
                        'format'    => ExportFormat::Csv->value,  // or Xlsx
                        'authGuard' => config('filament.auth.guard', 'web'),
                    ],
                    absolute: false
                ))
                ->openUrlInNewTab()
                ->visible(fn (): bool =>
                    ! is_null($this->getRecord()->file_name) &&
                    ! is_null($this->getRecord()->completed_at)
                ),
        ];
    }
}