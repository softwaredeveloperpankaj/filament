<?php

namespace App\Filament\Pages;

use App\Models\FormTemplate;
use App\Models\FormSection;
use App\Models\FormField;
use App\Models\FormFieldOption;
use App\Models\FormTemplateVersion;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormBuilder extends Page
{
    protected static string|\BackedEnum|null $navigationIcon   = 'heroicon-o-wrench-screwdriver';
    protected string  $view             = 'filament.pages.form-builder';
    protected static bool    $shouldRegisterNavigation = false;

    protected static ?string $slug = 'form-builder/{template}';

    public int             $templateId;
    public ?FormTemplate   $template = null;

    // For "Add Section" inline form
    public string $newSectionTitle = '';

    // For "Edit Field" slide-over
    public ?int    $editingFieldId   = null;
    public array   $editingFieldData = [];

    public ?int $fieldToDelete = null;

    public ?int $editingOptionsFieldId = null;
    public array $fieldOptions = [];
    public string $newOption = '';

    // ── Versions ──────────────────────────────────────────────────
    public array $templateVersions = [];

    public function mount(FormTemplate $template): void
    {
        $this->templateId = $template->id;
        $this->loadTemplate();
    }

    private function loadTemplate(): void
    {
        $this->template = FormTemplate::with([
            'sections' => fn($q) => $q->orderBy('sort_order'),
            'sections.fields' => fn($q) => $q->orderBy('sort_order'),
            'branch'
        ])->findOrFail($this->templateId);
    }

    // ── Sections ──────────────────────────────────────────────────
    public function addSection(): void
    {
        if (!trim($this->newSectionTitle)) return;

        FormSection::create([
            'form_template_id'  => $this->templateId,
            'title'             => $this->newSectionTitle,
            'section_key'       => Str::slug($this->newSectionTitle, '_').'_'.time(),
            'sort_order'       => ($this->template->sections->max('sort_order') ?? 0) + 1,
        ]);
        $this->newSectionTitle = '';
        $this->loadTemplate();
    }

    public function deleteSection(int $sectionId): void
    {
        FormSection::destroy($sectionId);
        $this->loadTemplate();
    }

    public function reorderSections(array $sections): void
    {
        foreach ($sections as $item) {
            FormSection::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }
    }

    // ── Fields ────────────────────────────────────────────────────
    public function addField(int $sectionId, string $type, string $label): void
    {
        $section = FormSection::find($sectionId);
        FormField::create([
            'form_template_id'=>$section->form_template_id,
            'form_section_id' => $sectionId,
            'label'           => $label,
            'field_key'       => strtolower(str_replace(' ', '_', $label)).'_'.time(),
            'type'            => $type,
            'sort_order'      => $section->fields()->count() + 1,
        ]);
        $this->loadTemplate();
    }

    public function confirmDeleteField(int $id): void
    {
        $this->fieldToDelete = $id;

        $this->dispatch('open-modal', id: 'delete-field-modal');
    }
    
    public function deleteField(): void
    {
        FormField::destroy($this->fieldToDelete);

        $this->fieldToDelete = null;

        $this->dispatch('close-modal', id: 'delete-field-modal');

        $this->loadTemplate();
        
        Notification::make()
            ->success()
            ->title('Field deleted')
            ->send();
    }

    public function reorderFields(array $fields): void
    {
        foreach ($fields as $item) {
            FormField::where('id', $item['id'])->update([
                'form_section_id' => $item['form_section_id'],
                'sort_order'      => $item['sort_order'],
            ]);
        }
        $this->loadTemplate();
    }

    // Open Edit Modal
    public function openEditField(int $fieldId): void
    {
        try {
            $field = FormField::findOrFail($fieldId);
            $this->editingFieldId = $field->id;
            $this->editingFieldData = [
                'label'                    => $field->label,
                'field_key'                => $field->field_key,
                'is_required'              => $field->is_required,
                'placeholder'              => $field->placeholder,
                'help_text'                => $field->help_text,
                'option_layout'            => $field->option_layout ?? 'horizontal',
                'validation_rules_input'   => $field->validation_rules
                                            ? implode('|', $field->validation_rules) : '',
                'visibility_conditions_input' => $field->visibility_conditions
                                            ? json_encode($field->visibility_conditions, JSON_PRETTY_PRINT) : '',
                'settings_input'           => $field->settings
                                            ? json_encode($field->settings, JSON_PRETTY_PRINT) : '',
            ];
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Something went wrong')
                ->body($th->getMessage())
                ->danger()
                ->send();            
        }
        $this->dispatch('open-modal', id: 'edit-field-modal');        
    }

    public function saveEditField(): void
    {
        $field = FormField::findOrFail($this->editingFieldId);

        $validationRules = [];
        if (!empty($this->editingFieldData['validation_rules_input'])) {
            $validationRules = explode('|', $this->editingFieldData['validation_rules_input']);
        }

        $visibilityConditions = null;
        if (!empty($this->editingFieldData['visibility_conditions_input'])) {
            $visibilityConditions = json_decode(
                $this->editingFieldData['visibility_conditions_input'], true
            );
        }

        $settings = null;
        if (!empty($this->editingFieldData['settings_input'])) {
            $settings = json_decode($this->editingFieldData['settings_input'], true);
        }

        $field->update([
            'label'                 => $this->editingFieldData['label'],
            'field_key'             => $this->editingFieldData['field_key'],
            'is_required'           => $this->editingFieldData['is_required'] ?? false,
            'placeholder'           => $this->editingFieldData['placeholder'],
            'help_text'             => $this->editingFieldData['help_text'],
            'option_layout'         => $this->editingFieldData['option_layout'],
            'validation_rules'      => $validationRules ?: null,
            'visibility_conditions' => $visibilityConditions,
            'settings'              => $settings,
        ]);

        $this->editingFieldId = null;
        $this->editingFieldData = [];
        $this->dispatch('close-modal', id: 'edit-field-modal');
        $this->loadTemplate();

        Notification::make()->title('Field updated!')->success()->send();
    }

    // ── Publish ───────────────────────────────────────────────────
    public function publishVersion(): void
    {
        $lastVersion = 0;
        DB::transaction(function () {

            $lastVersion = $this->template->versions()->max('version') ?? 0;

            // $snapshot = $this->template->sections->map(function ($section) {
            //     return [
            //         'title'         => $section->title,
            //         'section_key'   => $section->section_key,
            //         'sort_order'    => $section->sort_order,
            //         'fields'        => $section->fields->map(function ($field) {
            //             return [
            //                 'field_key'             => $field->field_key,
            //                 'label'                 => $field->label,
            //                 'type'                  => $field->type,
            //                 'placeholder'           => $field->placeholder,
            //                 'help_text'             => $field->help_text,
            //                 'sort_order'            => $field->sort_order,        
            //                 'is_required'           => $field->is_required,
            //                 'option_layout'         => $field->option_layout,
            //                 'is_active'             => $field->is_active,
            //                 'validation_rules'      => $field->validation_rules,
            //                 'settings'              => $field->settings,
            //                 'visibility_conditions' => $field->visibility_conditions,
            //                 'options'               => $field->options->map(fn ($option) => [
            //                     'label'         => $option->label,
            //                     'value'         => $option->value,
            //                     'sort_order'    => $option->sort_order,
            //                     'is_default'    => $option->is_default,
            //                 ])->values(),
            //             ];
            //         })->values(),
            //     ];
            // })->toArray();

            $snapshot = $this->template->sections->map(function ($section) {
                return [
                    'title'       => $section->title,
                    'section_key' => $section->section_key,
                    'sort_order'  => $section->sort_order,
                    'fields'      => $section->fields->map(function ($field) {

                        $fieldData = [
                            'field_key'             => $field->field_key,
                            'label'                 => $field->label,
                            'type'                  => $field->type,
                            'placeholder'           => $field->placeholder,
                            'help_text'             => $field->help_text,
                            'sort_order'            => $field->sort_order,
                            'is_required'           => $field->is_required,
                            'option_layout'         => $field->option_layout,
                            'is_active'             => $field->is_active,
                            'validation_rules'      => $field->validation_rules,
                            'settings'              => $field->settings,
                            'visibility_conditions' => $field->visibility_conditions,
                        ];

                        if (in_array($field->type, ['select', 'radio', 'checkbox'])) {
                            $fieldData['options'] = $field->options->map(fn ($option) => [
                                'label'      => $option->label,
                                'value'      => $option->value,
                                'sort_order' => $option->sort_order,
                                'is_default' => $option->is_default,
                            ])->values()->toArray();
                        }

                        return $fieldData;

                    })->values()->toArray(),
                ];
            })->toArray();

            // Deactivate previous versions
            $this->template->versions()->update([
                'is_active' => false,
            ]);

            FormTemplateVersion::create([
                'form_template_id' => $this->template->id,
                'user_id'          => $this->template->user_id,
                'version'          => $lastVersion + 1,
                'schema_json'      => $snapshot,
                'is_active'        => true,
                'published_at'     => now(),
            ]);

            $this->template->update([
                'status' => 'published',
            ]);
        });

        $this->loadTemplate();

        Notification::make()
            ->success()
            ->title('Version ' . ($lastVersion + 1) . ' published!')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Templates')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn() => route('filament.admin.resources.form-templates.index')),

            Action::make('versions')
                ->label('View Versions')
                ->icon('heroicon-o-clock')
                ->color('info')
                ->action('openVersionsModal'),

            Action::make('publish')
                ->label('Publish Version')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Publish a new version?')
                ->action('publishVersion'),
        ];
    }

    public function createSection(): void
    {
        $this->validate([
            'newSectionTitle' => ['required', 'string', 'max:255'],
        ]);

        // Your existing section creation logic
        $this->addSection();

        $this->newSectionTitle = '';

        $this->dispatch('close-modal', id: 'add-section-modal');
    }

    public function openOptionsModal(int $fieldId): void
    {
        $field = FormField::with('options')->findOrFail($fieldId);

        $this->editingOptionsFieldId = $fieldId;

        $this->fieldOptions = $field->options
            ->sortBy('sort_order')
            ->map(fn ($option) => [
                'id'         => $option->id,
                'label'      => $option->label,
                'value'      => $option->value,
                'is_default' => $option->is_default,
            ])
            ->values()
            ->toArray();

        if (empty($this->fieldOptions)) {
            $this->fieldOptions[] = [
                'id' => null,
                'label' => '',
                'value' => '',
                'is_default' => false,
            ];
        }

        $this->dispatch('open-modal', id: 'field-options-modal');
    }
    
    public function addOption(): void
    {
        $this->fieldOptions[] = [
            'id' => null,
            'label' => '',
            'value' => '',
            'is_default' => false,
        ];
    }

    public function removeOption(int $index): void
    {
        unset($this->fieldOptions[$index]);

        $this->fieldOptions = array_values($this->fieldOptions);
    }

    // NEW — receives flat array of old indexes in new visual order
    public function reorderOptions(array $orderedIndexes): void
    {
        $reordered = [];
        foreach ($orderedIndexes as $i) {
            if (isset($this->fieldOptions[$i])) {
                $reordered[] = $this->fieldOptions[$i];
            }
        }
        $this->fieldOptions = $reordered;
    }

    public function saveOptions(): void
    {
        $count = count($this->fieldOptions);
        $keepIds = [];
        try {
            foreach ($this->fieldOptions as $index => $option) {
                $data = [
                    'form_field_id' => $this->editingOptionsFieldId, // ← typo fix
                    'label'         => $option['label'],
                    'value'         => $option['value'],
                    'sort_order'    => $index + 1,
                    'is_default'    => $option['is_default'],
                ];

                $result = FormFieldOption::updateOrCreate( ['id' => $option['id'] ?? null], $data);

                $keepIds[] = $result->id;
            }

            // Delete options that were removed from the list
            FormFieldOption::where('form_field_id', $this->editingOptionsFieldId)
                ->whereNotIn(
                    'id', $keepIds
                )
                ->delete();
        } catch (\Throwable $th) {
            Notification::make()
                ->success()
                ->title('Options saved')
                ->body($th->getMessage())
                ->send();
        }

        $this->dispatch('close-modal', id: 'field-options-modal');

        Notification::make()
            ->success()
            ->title('Options saved')
            ->body("$count option(s) have been saved successfully.")
            ->send();
    }

    public function openVersionsModal(): void
    {
        $this->templateVersions = FormTemplateVersion::where('form_template_id', $this->templateId)
            ->orderBy('version', 'desc')
            ->get()
            ->map(fn($v) => [
                'id'             => $v->id,
                'version' => $v->version,
                'is_active'      => (bool) $v->is_active,
                'published_at'   => $v->created_at?->format('d M Y, h:i A'),
            ])
            ->toArray();

        $this->dispatch('open-modal', id: 'versions-modal');
    }

    public function toggleVersionActive(int $versionId): void
    {
        $version = FormTemplateVersion::findOrFail($versionId);

        // Only one version can be active at a time — deactivate all others first
        FormTemplateVersion::where('form_template_id', $this->templateId)
            ->where('id', '!=', $versionId)
            ->update(['is_active' => false]);

        $version->update(['is_active' => !$version->is_active]);

        // Refresh local state
        $this->openVersionsModal();

        Notification::make()
            ->success()
            ->title('Version ' . $version->version_number . ' ' . ($version->fresh()->is_active ? 'activated' : 'deactivated'))
            ->send();
    }    
}
