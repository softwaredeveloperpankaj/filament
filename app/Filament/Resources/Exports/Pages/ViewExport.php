<?php

namespace App\Filament\Resources\Exports\Pages;

use App\Filament\Resources\Exports\ExportResource;
use App\Models\Export;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

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
                ->url(fn (Export $record): string => route('filament.admin.exports.download', ['export' => $record]))
                ->openUrlInNewTab()
                ->visible(fn (): bool =>
                    ! is_null($this->getRecord()->file_name) &&
                    ! is_null($this->getRecord()->completed_at)
                ),              
        ];
    }
}
