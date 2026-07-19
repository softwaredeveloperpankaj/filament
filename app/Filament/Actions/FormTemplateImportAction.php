<?php

namespace App\Filament\Actions;

use App\Models\FormField;
use App\Models\FormFieldOption;
use App\Models\FormSection;
use App\Models\FormTemplate;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class FormTemplateImportAction
{
    /**
     * Import a FormTemplate record from a template export JSON.
     * Creates a brand new FormTemplate — does NOT require an existing one.
     * Available as a header/toolbar action.
     */
    public static function importTemplate(): Action
    {
        return Action::make('importTemplate')
            ->label('Import Template')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('info')
            ->form([
                FileUpload::make('template_file')
                    ->label('Template JSON File')
                    ->acceptedFileTypes(['application/json', 'text/plain'])
                    ->required()
                    ->helperText('Upload a file exported via "Export Template"'),
            ])
            ->action(function (array $data) {
                try {
                    $path = storage_path('app/public/' . $data['template_file']);
                    $json = json_decode(file_get_contents($path), true);

                    if (($json['_export_type'] ?? '') !== 'form_template') {
                        Notification::make()->danger()
                            ->title('Invalid file')
                            ->body('This file is not a Template export. Use "Import Form" for form builder exports.')
                            ->send();
                        return;
                    }

                    // Generate a unique slug
                    $slug = Str::slug($json['name']) . '-' . now()->format('YmdHis');

                    FormTemplate::create([
                        'name'                    => $json['name'] . ' (Imported)',
                        'slug'                    => $slug,
                        'type'                    => $json['type'] ?? 'admission',
                        'status'                  => 'draft', // Always start as draft
                        'form_layout'             => $json['form_layout'] ?? null,
                        'rollno_generation_scope' => $json['rollno_generation_scope'] ?? null,
                        'registration_serial'     => $json['registration_serial'] ?? null,
                        'is_active'               => false,
                        'settings'                => $json['settings'] ?? null,
                    ]);

                    // Clean up uploaded file
                    @unlink($path);

                    Notification::make()->success()
                        ->title('Template imported')
                        ->body('Template "' . $json['name'] . '" has been imported as a draft.')
                        ->send();

                } catch (\Throwable $e) {
                    Notification::make()->danger()
                        ->title('Import failed')
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }

    /**
     * Import Form Builder (sections + fields + options) into an EXISTING template.
     * Available as a row action — requires $record (the template).
     */
    public static function importForm(): Action
    {
        return Action::make('importForm')
            ->label('Import Form (Builder)')
            ->icon('heroicon-o-document-arrow-up')
            ->color('warning')
            ->form([
                FileUpload::make('form_file')
                    ->label('Form Builder JSON File')
                    ->acceptedFileTypes(['application/json', 'text/plain'])
                    ->required()
                    ->helperText('Upload a file exported via "Export Form (Builder)". ⚠️ This will ADD to existing sections.'),
            ])
            ->requiresConfirmation()
            ->modalHeading('Import Form into this Template')
            ->modalDescription('Sections and fields from the JSON will be added to this template. Existing data will NOT be deleted.')
            ->action(function ($record, array $data) {
                try {
                    $path = storage_path('app/public/' . $data['form_file']);
                    $json = json_decode(file_get_contents($path), true);

                    if (($json['_export_type'] ?? '') !== 'form_builder') {
                        Notification::make()->danger()
                            ->title('Invalid file')
                            ->body('This file is not a Form Builder export. Use "Import Template" for template exports.')
                            ->send();
                        return;
                    }

                    $existingMaxOrder = FormSection::where('form_template_id', $record->id)
                        ->max('sort_order') ?? 0;

                    $sectionCount = 0;
                    $fieldCount   = 0;

                    foreach ($json['sections'] as $sectionData) {
                        $section = FormSection::create([
                            'form_template_id' => $record->id,
                            'title'            => $sectionData['title'],
                            'section_key'      => ($sectionData['section_key'] ?? Str::slug($sectionData['title'], '_')) . '_' . time(),
                            'sort_order'       => $existingMaxOrder + ($sectionData['sort_order'] ?? 1),
                        ]);
                        $sectionCount++;

                        foreach ($sectionData['fields'] ?? [] as $fieldData) {
                            $field = FormField::create([
                                'form_template_id'     => $record->id,
                                'form_section_id'      => $section->id,
                                'label'                => $fieldData['label'],
                                'field_key'            => $fieldData['field_key'] . '_' . time(),
                                'type'                 => $fieldData['type'],
                                'is_required'          => $fieldData['is_required'] ?? false,
                                'placeholder'          => $fieldData['placeholder'] ?? null,
                                'help_text'            => $fieldData['help_text'] ?? null,
                                'option_layout'        => $fieldData['option_layout'] ?? 'horizontal',
                                'validation_rules'     => $fieldData['validation_rules'] ?? null,
                                'visibility_conditions'=> $fieldData['visibility_conditions'] ?? null,
                                'settings'             => $fieldData['settings'] ?? null,
                                'sort_order'           => $fieldData['sort_order'] ?? 1,
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

                    @unlink($path);

                    Notification::make()->success()
                        ->title('Form imported successfully')
                        ->body("Added {$sectionCount} section(s) and {$fieldCount} field(s) to \"{$record->name}\".")
                        ->send();

                } catch (\Throwable $e) {
                    Notification::make()->danger()
                        ->title('Import failed')
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }
}