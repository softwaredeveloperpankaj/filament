<?php

namespace App\Filament\Actions\Bulk;

use App\Models\FormField;
use App\Models\FormFieldOption;
use App\Models\FormSection;
use App\Models\FormTemplate;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class BulkImportAction
{
    /**
     * Toolbar action — auto-detects file type and runs correct import.
     * Handles both bulk_form_templates and bulk_form_builders exports.
     */
    public static function make(): Action
    {
        return Action::make('bulkImport')
            ->label('Bulk Import')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('warning')
            ->form([
                FileUpload::make('import_file')
                    ->label('Bulk JSON File')
                    ->acceptedFileTypes(['application/json', 'text/plain'])
                    ->required()
                    ->helperText(
                        'Upload a file from "Export Templates" (bulk metadata) ' .
                        'or "Export Forms" (bulk form builder). Type is detected automatically.'
                    ),
            ])
            ->action(function (array $data) {
                try {
                    $path = storage_path('app/public/' . $data['import_file']);
                    $json = json_decode(file_get_contents($path), true);

                    $type = $json['_export_type'] ?? '';

                    match ($type) {
                        'bulk_form_templates' => self::importBulkTemplates($json),
                        'bulk_form_builders'  => self::importBulkForms($json),
                        default               => Notification::make()->danger()
                            ->title('Unrecognised file')
                            ->body("Expected a bulk export file. Got type: \"{$type}\"")
                            ->send(),
                    };

                    @unlink($path);

                } catch (\Throwable $e) {
                    Notification::make()->danger()
                        ->title('Bulk import failed')
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }

    // ── Private helpers ────────────────────────────────────────────

    private static function importBulkTemplates(array $json): void
    {
        $count = 0;

        foreach ($json['templates'] ?? [] as $t) {
            FormTemplate::create([
                'name'                    => $t['name'] . ' (Imported)',
                'slug'                    => Str::slug($t['name']) . '-' . now()->format('YmdHis') . '-' . $count,
                'type'                    => $t['type'] ?? 'admission',
                'status'                  => 'draft',
                'form_layout'             => $t['form_layout'] ?? null,
                'rollno_generation_scope' => $t['rollno_generation_scope'] ?? null,
                'registration_serial'     => $t['registration_serial'] ?? null,
                'is_active'               => false,
                'settings'                => $t['settings'] ?? null,
            ]);
            $count++;
        }

        Notification::make()->success()
            ->title('Bulk import complete')
            ->body("{$count} template(s) imported as drafts.")
            ->send();
    }

    private static function importBulkForms(array $json): void
    {
        $templateCount = 0;
        $sectionCount  = 0;
        $fieldCount    = 0;

        foreach ($json['forms'] ?? [] as $formData) {
            // Create a new template for each form in the bulk file
            $template = FormTemplate::create([
                'name'   => ($formData['template_name'] ?? 'Imported Form') . ' (Imported)',
                'slug'   => Str::slug($formData['template_name'] ?? 'imported-form') . '-' . now()->format('YmdHis') . '-' . $templateCount,
                'type'   => 'admission',
                'status' => 'draft',
                'is_active' => false,
            ]);
            $templateCount++;

            foreach ($formData['sections'] ?? [] as $sectionData) {
                $section = FormSection::create([
                    'form_template_id' => $template->id,
                    'title'            => $sectionData['title'],
                    'section_key'      => ($sectionData['section_key'] ?? Str::slug($sectionData['title'], '_')) . '_' . time(),
                    'sort_order'       => $sectionData['sort_order'] ?? 1,
                ]);
                $sectionCount++;

                foreach ($sectionData['fields'] ?? [] as $fieldData) {
                    $field = FormField::create([
                        'form_template_id'      => $template->id,
                        'form_section_id'       => $section->id,
                        'label'                 => $fieldData['label'],
                        'field_key'             => $fieldData['field_key'] . '_' . time(),
                        'type'                  => $fieldData['type'],
                        'is_required'           => $fieldData['is_required'] ?? false,
                        'placeholder'           => $fieldData['placeholder'] ?? null,
                        'help_text'             => $fieldData['help_text'] ?? null,
                        'option_layout'         => $fieldData['option_layout'] ?? 'horizontal',
                        'validation_rules'      => $fieldData['validation_rules'] ?? null,
                        'visibility_conditions' => $fieldData['visibility_conditions'] ?? null,
                        'settings'              => $fieldData['settings'] ?? null,
                        'sort_order'            => $fieldData['sort_order'] ?? 1,
                    ]);
                    $fieldCount++;

                    foreach ($fieldData['options'] ?? [] as $optData) {
                        FormFieldOption::create([
                            'form_field_id' => $field->id,
                            'label'         => $optData['label'],
                            'value'         => $optData['value'],
                            'is_default'    => $optData['is_default'] ?? false,
                            'sort_order'    => $optData['sort_order'] ?? 1,
                        ]);
                    }
                }
            }
        }

        Notification::make()->success()
            ->title('Bulk form import complete')
            ->body("{$templateCount} template(s), {$sectionCount} section(s), {$fieldCount} field(s) imported.")
            ->send();
    }
}