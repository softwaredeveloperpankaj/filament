<?php

namespace App\Filament\Actions;

use App\Models\FormTemplate;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class FormTemplateExportAction
{
    /**
     * Export the FormTemplate record (name, type, slug, settings etc.) — NO sections/fields.
     */
    public static function exportTemplate(): Action
    {
        return Action::make('exportTemplate')
            ->label('Export Template')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('info')
            ->action(function ($record) {
                $data = [
                    '_export_type' => 'form_template',
                    '_exported_at' => now()->toISOString(),
                    'name'                    => $record->name,
                    'slug'                    => $record->slug,
                    'type'                    => $record->type,
                    'status'                  => $record->status,
                    'form_layout'             => $record->form_layout,
                    'rollno_generation_scope' => $record->rollno_generation_scope,
                    'registration_serial'     => $record->registration_serial,
                    'is_active'               => $record->is_active,
                    'settings'                => $record->settings,
                ];

                $filename = 'template_' . $record->slug . '_' . now()->format('Ymd_His') . '.json';

                return response()->streamDownload(
                    fn () => print(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
                    $filename,
                    ['Content-Type' => 'application/json']
                );
            });
    }

    /**
     * Export the full Form Builder structure — sections + fields + options.
     * Requires template to exist (called from record row action).
     */
    public static function exportForm(): Action
    {
        return Action::make('exportForm')
            ->label('Export Form (Builder)')
            ->icon('heroicon-o-document-arrow-down')
            ->color('success')
            ->action(function ($record) {
                $template = FormTemplate::with([
                    'sections' => fn ($q) => $q->orderBy('sort_order'),
                    'sections.fields' => fn ($q) => $q->orderBy('sort_order'),
                    'sections.fields.options' => fn ($q) => $q->orderBy('sort_order'),
                ])->findOrFail($record->id);

                $sections = $template->sections->map(fn ($section) => [
                    'title'       => $section->title,
                    'section_key' => $section->section_key,
                    'sort_order'  => $section->sort_order,
                    'fields'      => $section->fields->map(fn ($field) => [
                        'label'                => $field->label,
                        'field_key'            => $field->field_key,
                        'type'                 => $field->type,
                        'is_required'          => $field->is_required,
                        'placeholder'          => $field->placeholder,
                        'help_text'            => $field->help_text,
                        'option_layout'        => $field->option_layout,
                        'validation_rules'     => $field->validation_rules,
                        'visibility_conditions'=> $field->visibility_conditions,
                        'settings'             => $field->settings,
                        'sort_order'           => $field->sort_order,
                        'options'              => $field->options->map(fn ($opt) => [
                            'label'      => $opt->label,
                            'value'      => $opt->value,
                            'is_default' => $opt->is_default,
                            'sort_order' => $opt->sort_order,
                        ])->toArray(),
                    ])->toArray(),
                ])->toArray();

                $export = [
                    '_export_type' => 'form_builder',
                    '_exported_at' => now()->toISOString(),
                    'template_name' => $template->name,
                    'sections'      => $sections,
                ];

                $filename = 'form_' . $template->slug . '_' . now()->format('Ymd_His') . '.json';

                return response()->streamDownload(
                    fn () => print(json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
                    $filename,
                    ['Content-Type' => 'application/json']
                );
            });
    }
}