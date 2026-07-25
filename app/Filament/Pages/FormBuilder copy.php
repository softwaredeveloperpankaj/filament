<?php

namespace App\Filament\Pages;

use App\Models\FormTemplate;
use App\Models\FormSection;
use App\Models\FormField;
use App\Models\FormFieldOption;
use App\Models\FormTemplateVersion;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormBuilder extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected string $view = 'filament.pages.form-builder';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'form-builder/{template}';

    public int $templateId;
    public ?FormTemplate $template = null;
    public ?FormTemplateVersion $workingVersion = null;

    // Add Section
    public string $newSectionTitle = '';

    // Edit Field
    public ?int $editingFieldId = null;
    public array $editingFieldData = [];
    public ?int $fieldToDelete = null;

    // Options
    public ?int $editingOptionsFieldId = null;
    public array $fieldOptions = [];
    public string $newOption = '';

    // Versions
    public array $templateVersions = [];

    // Section CRUD
    public ?int $sectionToDelete = null;
    public ?int $editingSectionId = null;
    public string $editingSectionTitle = '';

    public function mount(FormTemplate $template): void
    {
        $this->templateId = $template->id;
        $this->loadTemplate();
    }

    // ── Core Load ────────────────────────────────────────────────

    private function loadTemplate(): void
    {
        $this->template = FormTemplate::with([
            'branch',
            'sections.fields.options',
        ])->findOrFail($this->templateId);

        $this->loadWorkingVersion();
    }

/**
 * Resolve which version to edit:
 * 1. Any version exists?
 *    a. Active version found  → load it with its sections/fields/options
 *    b. No active but has versions → load the latest version (draft state)
 * 2. No versions at all → auto-create version 1 as draft and load it
 */
private function loadWorkingVersion(): void
{
    // Step 1: check if ANY version exists for this template
    $latestVersion = $this->template->versions()
        ->orderByDesc('version')
        ->first();

    if ($latestVersion) {
        // Step 1a: prefer the active version; fall back to latest if none is active
        $targetVersion = $this->template->versions()
            ->where('is_active', true)
            ->orderByDesc('version')
            ->first() ?? $latestVersion;

        $this->workingVersion = $targetVersion;
        $this->workingVersion->setRelation(
            'sections',
            FormSection::with(['fields.options'])
                ->where('form_template_version_id', $targetVersion->id)
                ->orderBy('sort_order')
                ->get()
        );

        return;
    }

    // Step 2: no versions at all → auto-create a draft version 1
    $this->workingVersion = FormTemplateVersion::create([
        'form_template_id' => $this->templateId,
        'version'          => 1,
        'is_active'        => false,   // draft — not published yet
        'schema_json'      => [],
        'published_at'     => null,
    ]);

    // Fresh version has no sections yet — set empty relation
    $this->workingVersion->setRelation('sections', collect());
}

    /**
     * Returns the sections to render in the builder:
     * - Active version's sections if version is active
     * - Template-level sections if no active version (draft mode)
     */
    public function getBuilderSections()
    {
        return $this->workingVersion?->sections ?? collect();
    }

    // ── Sections ─────────────────────────────────────────────────

    public function addSection(): void
    {
        if (!trim($this->newSectionTitle)) return;

        $versionId = $this->workingVersion?->id;
        $maxSort = $this->workingVersion
            ? $this->workingVersion->sections()->max('sort_order')
            : $this->template->sections()->whereNull('form_template_version_id')->max('sort_order');

        FormSection::create([
            'form_template_id'         => $this->templateId,
            'form_template_version_id' => $versionId,
            'title'                    => $this->newSectionTitle,
            'section_key'              => Str::slug($this->newSectionTitle, '_') . '_' . time(),
            'sort_order'               => ($maxSort ?? 0) + 1,
        ]);

        $this->newSectionTitle = '';
        $this->loadTemplate();
        $this->markAsDraft();
    }

    public function reorderSections(array $sections): void
    {
        foreach ($sections as $item) {
            FormSection::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }
        $this->loadTemplate();
    }

    public function openEditSection(int $sectionId): void
    {
        $section = FormSection::findOrFail($sectionId);

        $this->editingSectionId    = $section->id;
        $this->editingSectionTitle = $section->title ?? '';

        $this->dispatch('open-modal', id: 'edit-section-modal');
    }

    public function saveEditSection(): void
    {
        $this->validate([
            'editingSectionTitle' => ['required', 'string', 'max:255'],
        ]);

        $section = FormSection::findOrFail($this->editingSectionId);
        $section->update(['title' => $this->editingSectionTitle]);

        $this->editingSectionId    = null;
        $this->editingSectionTitle = '';

        $this->loadTemplate();
        $this->markAsDraft();

        $this->dispatch('close-modal', id: 'edit-section-modal');

        Notification::make()->title('Section updated successfully')->success()->send();
    }

    public function confirmDeleteSection(int $sectionId): void
    {
        $this->sectionToDelete = $sectionId;
        $this->dispatch('open-modal', id: 'delete-section-modal');
    }

    public function deleteSection(): void
    {
        $section = FormSection::findOrFail($this->sectionToDelete);
        $section->delete();

        $this->sectionToDelete = null;
        $this->loadTemplate();
        $this->markAsDraft();

        $this->dispatch('close-modal', id: 'delete-section-modal');

        Notification::make()->title('Section deleted successfully')->success()->send();
    }

    // ── Fields ────────────────────────────────────────────────────

    public function addField(int $sectionId, string $type, string $label): void
    {
        $section = FormSection::find($sectionId);
        if (!$section) return;

        FormField::create([
            'form_section_id'  => $sectionId,
            'label'            => $label,
            'field_key'        => strtolower(str_replace(' ', '_', $label)) . '_' . time(),
            'type'             => $type,
            'sort_order'       => $section->fields()->count() + 1,
        ]);

        $this->loadTemplate();
        $this->markAsDraft();
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
        $this->markAsDraft();

        Notification::make()->success()->title('Field deleted')->send();
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
        $this->markAsDraft();
    }

    public function openEditField(int $fieldId): void
    {
        try {
            $field = FormField::findOrFail($fieldId);
            $this->editingFieldId = $field->id;
            $this->editingFieldData = [
                'label'                       => $field->label,
                'field_key'                   => $field->field_key,
                'is_required'                 => $field->is_required,
                'placeholder'                 => $field->placeholder,
                'help_text'                   => $field->help_text,
                'option_layout'               => $field->option_layout ?? 'horizontal',
                'validation_rules_input'      => $field->validation_rules
                                                    ? implode('|', $field->validation_rules) : '',
                'visibility_conditions_input' => $field->visibility_conditions
                                                    ? json_encode($field->visibility_conditions, JSON_PRETTY_PRINT) : '',
                'settings_input'              => $field->settings
                                                    ? json_encode($field->settings, JSON_PRETTY_PRINT) : '',
            ];
        } catch (\Throwable $th) {
            Notification::make()->title('Something went wrong')->body($th->getMessage())->danger()->send();
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
            $visibilityConditions = json_decode($this->editingFieldData['visibility_conditions_input'], true);
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
        $this->markAsDraft();

        Notification::make()->title('Field updated!')->success()->send();
    }

    // ── Options ───────────────────────────────────────────────────

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
            $this->fieldOptions[] = ['id' => null, 'label' => '', 'value' => '', 'is_default' => false];
        }

        $this->dispatch('open-modal', id: 'field-options-modal');
    }

    public function addOption(): void
    {
        $this->fieldOptions[] = ['id' => null, 'label' => '', 'value' => '', 'is_default' => false];
    }

    public function removeOption(int $index): void
    {
        unset($this->fieldOptions[$index]);
        $this->fieldOptions = array_values($this->fieldOptions);
        $this->markAsDraft();
    }

    public function reorderOptions(array $orderedIndexes): void
    {
        $reordered = [];
        foreach ($orderedIndexes as $i) {
            if (isset($this->fieldOptions[$i])) {
                $reordered[] = $this->fieldOptions[$i];
            }
        }
        $this->fieldOptions = $reordered;
        $this->markAsDraft();
    }

    public function saveOptions(): void
    {
        $count   = count($this->fieldOptions);
        $keepIds = [];

        try {
            foreach ($this->fieldOptions as $index => $option) {
                $data = [
                    'form_field_id' => $this->editingOptionsFieldId,
                    'label'         => $option['label'],
                    'value'         => $option['value'],
                    'sort_order'    => $index + 1,
                    'is_default'    => $option['is_default'],
                ];

                $result    = FormFieldOption::updateOrCreate(['id' => $option['id'] ?? null], $data);
                $keepIds[] = $result->id;
            }

            FormFieldOption::where('form_field_id', $this->editingOptionsFieldId)
                ->whereNotIn('id', $keepIds)
                ->delete();
        } catch (\Throwable $th) {
            Notification::make()->danger()->title('Error saving options')->body($th->getMessage())->send();
            return;
        }

        $this->loadTemplate();
        $this->markAsDraft();
        $this->dispatch('close-modal', id: 'field-options-modal');

        Notification::make()->success()->title('Options saved')->body("$count option(s) saved.")->send();
    }

    // ── Publish / Versions ────────────────────────────────────────

public function publishVersion(string $mode = 'create_new'): void
{
    $hasAnyVersion = $this->template->versions()->exists();
    $activeVersion = $this->template->versions()
        ->where('is_active', true)
        ->orderByDesc('version')
        ->first();

    if ($hasAnyVersion && ! $activeVersion) {
        Notification::make()
            ->warning()
            ->title('No active version')
            ->body('Please activate a version from "View Versions" before publishing.')
            ->persistent()
            ->send();
        return;
    }

    DB::transaction(function () use ($activeVersion, $mode) {
        $sections = $this->getBuilderSections();

        $snapshot = $sections->map(function ($section) {
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
                        $fieldData['options'] = $field->options->map(fn ($o) => [
                            'label'      => $o->label,
                            'value'      => $o->value,
                            'sort_order' => $o->sort_order,
                            'is_default' => $o->is_default,
                        ])->values()->toArray();
                    }

                    return $fieldData;
                })->values()->toArray(),
            ];
        })->toArray();

        if ($activeVersion && $mode === 'update_existing') {
            $activeVersion->update([
                'schema_json'  => $snapshot,
                'published_at' => now(),
            ]);

            $this->template->update(['status' => 'published']);

            Notification::make()
                ->success()
                ->title("Version {$activeVersion->version} updated successfully")
                ->send();

            return;
        }

        $lastVersion = $this->template->versions()->max('version') ?? 0;
        $nextVersion = $lastVersion + 1;

        $this->template->versions()->update(['is_active' => false]);

        $newVersion = FormTemplateVersion::create([
            'form_template_id' => $this->template->id,
            'user_id'          => $this->template->user_id,
            'version'          => $nextVersion,
            'schema_json'      => $snapshot,
            'is_active'        => true,
            'published_at'     => now(),
        ]);

        $this->cloneSectionsIntoVersion($sections, $newVersion);

        $this->template->update(['status' => 'published']);

        Notification::make()
            ->success()
            ->title("Version {$nextVersion} published!")
            ->send();
    });

    $this->loadTemplate();
}

    /**
     * Deep-clone sections + fields + options into a specific version.
     * Called on every publish so the new version owns its own DB records.
     */
    private function cloneSectionsIntoVersion($sections, FormTemplateVersion $version): void
    {
        foreach ($sections as $section) {
            $newSection = $section->replicate(['id', 'created_at', 'updated_at']);
            $newSection->form_template_version_id = $version->id;

            $sectionBaseKey = Str::beforeLast($section->section_key, '_');
            $newSection->section_key = $sectionBaseKey . '_' . time();

            $newSection->save();

            foreach ($section->fields as $field) {
                $newField = $field->replicate(['id', 'created_at', 'updated_at']);
                $newField->form_section_id = $newSection->id;

                $fieldBaseKey = Str::beforeLast($field->field_key, '_');
                $newField->field_key = $fieldBaseKey . '_' . time() . rand(10, 99);

                $newField->save();

                foreach ($field->options as $option) {
                    $newOption = $option->replicate(['id', 'created_at', 'updated_at']);
                    $newOption->form_field_id = $newField->id;
                    $newOption->save();
                }
            }
        }
    }

    public function openVersionsModal(): void
    {
        $this->templateVersions = FormTemplateVersion::where('form_template_id', $this->templateId)
            ->orderBy('version', 'desc')
            ->get()
            ->map(fn ($v) => [
                'id'           => $v->id,
                'version'      => $v->version,
                'is_active'    => (bool) $v->is_active,
                'published_at' => $v->created_at?->format('d M Y, h:i A'),
            ])
            ->toArray();

        $this->dispatch('open-modal', id: 'versions-modal');
    }

    public function toggleVersionActive(int $versionId): void
    {
        DB::transaction(function () use ($versionId) {
            // Deactivate all versions first
            FormTemplateVersion::where('form_template_id', $this->templateId)
                ->update(['is_active' => false]);

            // Toggle the selected one
            $version = FormTemplateVersion::findOrFail($versionId);
            $version->update(['is_active' => true]);
        });

        // Reload builder to reflect active version's sections/fields/options
        $this->loadTemplate();
        $this->openVersionsModal();

        $version = FormTemplateVersion::findOrFail($versionId);
        Notification::make()
            ->success()
            ->title("Version {$version->version} is now active — builder loaded.")
            ->send();
    }

    // ── Header Actions ────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Templates')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => route('filament.admin.resources.form-templates.index')),

            Action::make('versions')
                ->label('View Versions')
                ->icon('heroicon-o-clock')
                ->color('info')
                ->action('openVersionsModal'),

            Action::make('publish')
                ->label('Publish Version')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->modalWidth('md')
                ->modalHeading('Publish Version')
                ->modalDescription(function () {
                    return $this->workingVersion?->is_active
                        ? 'An active version is currently live. Choose your target destination below:'
                        : 'No active version exists yet. Publishing will establish this as the active version.';
                })
                ->schema(function (): array {
                    if (! $this->workingVersion?->is_active) {
                        return [];
                    }

                    return [
                        Radio::make('mode')
                            ->label('Publishing Strategy')
                            ->options([
                                'update_existing' => 'Overwrite Existing Active Version',
                                'create_new'      => 'Publish as New Version',
                            ])
                            ->descriptions([
                                'update_existing' => 'Updates the current active version directly with your changes.',
                                'create_new'      => 'Preserves current active version history and creates a new release.',
                            ])
                            ->default('update_existing')
                            ->required(),
                    ];
                })
                ->modalSubmitActionLabel(function () {
                    return $this->workingVersion?->is_active
                        ? 'Confirm & Publish'
                        : 'Publish Initial Version';
                })
                ->modalCancelActionLabel('Cancel')
                ->action(function (array $data): void {
                    $mode = $data['mode'] ?? 'create_new';

                    $this->publishVersion($mode);
                }),

        ];
    }

    public function createSection(): void
    {
        $this->validate([
            'newSectionTitle' => ['required', 'string', 'max:255'],
        ]);

        $this->addSection();
        $this->newSectionTitle = '';
        $this->dispatch('close-modal', id: 'add-section-modal');
    }

    protected function markAsDraft(): void
    {
        if ($this->template->status !== 'draft') {
            $this->template->update(['status' => 'draft']);
            $this->template->refresh();
        }
    }
}