<?php

namespace App\Filament\Actions\Bulk;

use App\Models\FormTemplate;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class BulkExportFormsAction
{
    /**
     * Bulk export — full form builder structure (sections + fields + options).
     */
    public static function make(): BulkAction
    {
        return BulkAction::make('bulkExportForms')
            ->label('Export Forms (Builder)')
            ->icon('heroicon-o-document-arrow-up')
            ->color('success')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) {
                $ids = $records->pluck('id')->toArray();

                $templates = FormTemplate::with([
                    'sections'               => fn ($q) => $q->orderBy('sort_order'),
                    'sections.fields'        => fn ($q) => $q->orderBy('sort_order'),
                    'sections.fields.options'=> fn ($q) => $q->orderBy('sort_order'),
                ])->whereIn('id', $ids)->get();

                $export = [
                    '_export_type' => 'bulk_form_builders',
                    '_exported_at' => now()->toISOString(),
                    '_count'       => $templates->count(),
                    'forms'        => $templates->map(fn ($template) => [
                        'template_name' => $template->name,
                        'template_slug' => $template->slug,
                        'sections'      => $template->sections->map(fn ($section) => [
                            'title'       => $section->title,
                            'section_key' => $section->section_key,
                            'sort_order'  => $section->sort_order,
                            'fields'      => $section->fields->map(fn ($field) => [
                                'label'                 => $field->label,
                                'field_key'             => $field->field_key,
                                'type'                  => $field->type,
                                'is_required'           => $field->is_required,
                                'placeholder'           => $field->placeholder,
                                'help_text'             => $field->help_text,
                                'option_layout'         => $field->option_layout,
                                'validation_rules'      => $field->validation_rules,
                                'visibility_conditions' => $field->visibility_conditions,
                                'settings'              => $field->settings,
                                'sort_order'            => $field->sort_order,
                                'options'               => $field->options->map(fn ($opt) => [
                                    'label'      => $opt->label,
                                    'value'      => $opt->value,
                                    'is_default' => $opt->is_default,
                                    'sort_order' => $opt->sort_order,
                                ])->toArray(),
                            ])->toArray(),
                        ])->toArray(),
                    ])->toArray(),
                ];

                $filename = 'bulk_forms_' . now()->format('Ymd_His') . '.json';

                return response()->streamDownload(
                    fn () => print(json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
                    $filename,
                    ['Content-Type' => 'application/json']
                );
            });
    }
}