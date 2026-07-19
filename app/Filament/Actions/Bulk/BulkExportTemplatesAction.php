<?php

namespace App\Filament\Actions\Bulk;

use App\Models\FormTemplate;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class BulkExportTemplatesAction
{
    /**
     * Bulk export — template metadata only (no sections/fields).
     */
    public static function make(): BulkAction
    {
        return BulkAction::make('bulkExportTemplates')
            ->label('Export Templates')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('info')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) {
                $data = [
                    '_export_type' => 'bulk_form_templates',
                    '_exported_at' => now()->toISOString(),
                    '_count'       => $records->count(),
                    'templates'    => $records->map(fn ($t) => [
                        'name'                    => $t->name,
                        'slug'                    => $t->slug,
                        'type'                    => $t->type,
                        'status'                  => $t->status,
                        'form_layout'             => $t->form_layout,
                        'rollno_generation_scope' => $t->rollno_generation_scope,
                        'registration_serial'     => $t->registration_serial,
                        'is_active'               => $t->is_active,
                        'settings'                => $t->settings,
                    ])->toArray(),
                ];

                $filename = 'bulk_templates_' . now()->format('Ymd_His') . '.json';

                return response()->streamDownload(
                    fn () => print(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
                    $filename,
                    ['Content-Type' => 'application/json']
                );
            });
    }
}