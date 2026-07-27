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

    // State properties
    public string $newSectionTitle = '';
    public ?int $editingFieldId = null;
    public array $editingFieldData = [];
    public ?int $fieldToDelete = null;

    public ?int $editingOptionsFieldId = null;
    public array $fieldOptions = [];
    public string $newOption = '';

    public array $templateVersions = [];
    public ?int $sectionToDelete = null;
    public ?int $editingSectionId = null;
    public string $editingSectionTitle = '';

    public function mount(FormTemplate $template): void
    {
        $this->templateId = $template->id;
        $this->loadTemplate();
    }

    // ── Core Loaders ────────────────────────────────────────────────

    private function loadTemplate(): void
    {
        $this->template = FormTemplate::with(['branch', 'sections.fields.options'])
            ->findOrFail($this->templateId);

        $this->loadWorkingVersion();
    }

    private function loadWorkingVersion(): void
    {
        $versions = $this->template->versions()->orderByDesc('version')->get();

        if ($versions->isNotEmpty()) {
            $this->workingVersion = $versions->firstWhere('is_active', true) ?? $versions->first();
            $this->workingVersion->setRelation(
                'sections',
                FormSection::with(['fields.options'])
                    ->where('form_template_version_id', $this->workingVersion->id)
                    ->orderBy('sort_order')
                    ->get()
            );
            return;
        }

        // Initialize draft V1 if no versions exist
        $this->workingVersion = FormTemplateVersion::create([
            'form_template_id' => $this->templateId,
            'version'          => 1,
            'is_active'        => false,
            'schema_json'      => [],
        ]);

        FormTemplate::where('id', $this->templateId)->update([
            'active_version_id' => $this->workingVersion->id,
            'status' => 'draft',
            'is_active' => false,
        ]);

        $this->workingVersion->setRelation('sections', collect());
    }

    public function getBuilderSections()
    {
        return $this->workingVersion?->sections ?? collect();
    }

    // ── Section Management ──────────────────────────────────────────

    public function addSection(): void
    {
        if (!trim($this->newSectionTitle)) return;

        $maxSort = $this->workingVersion
            ? $this->workingVersion->sections()->max('sort_order')
            : $this->template->sections()->whereNull('form_template_version_id')->max('sort_order');

        FormSection::create([
            'form_template_id'         => $this->templateId,
            'form_template_version_id' => $this->workingVersion?->id,
            'title'                    => $this->newSectionTitle,
            'section_key'              => Str::slug($this->newSectionTitle, '_') . '_' . time(),
            'sort_order'               => ($maxSort ?? 0) + 1,
        ]);

        $this->newSectionTitle = '';
        $this->loadTemplate();
        $this->markAsDraft();
    }

    public function createSection(): void
    {
        $this->validate(['newSectionTitle' => ['required', 'string', 'max:255']]);
        $this->addSection();
        $this->dispatch('close-modal', id: 'add-section-modal');
    }

    public function reorderSections(array $sections): void
    {
        foreach ($sections as $item) {
            FormSection::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
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
        $this->validate(['editingSectionTitle' => ['required', 'string', 'max:255']]);

        FormSection::where('id', $this->editingSectionId)
            ->update(['title' => $this->editingSectionTitle]);

        $this->editingSectionId = null;
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
        FormSection::destroy($this->sectionToDelete);
        $this->sectionToDelete = null;

        $this->loadTemplate();
        $this->markAsDraft();
        $this->dispatch('close-modal', id: 'delete-section-modal');

        Notification::make()->title('Section deleted successfully')->success()->send();
    }

    // ── Field Management ───────────────────────────────────────────

    public function addField(int $sectionId, string $type, string $label): void
    {
        if (!$section = FormSection::find($sectionId)) return;

        FormField::create([
            'form_section_id' => $sectionId,
            'label'           => $label,
            'field_key'       => strtolower(str_replace(' ', '_', $label)) . '_' . time(),
            'type'            => $type,
            'sort_order'      => $section->fields()->count() + 1,
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
                'validation_rules_input'      => $field->validation_rules ? implode('|', $field->validation_rules) : '',
                'visibility_conditions_input' => $field->visibility_conditions ? json_encode($field->visibility_conditions, JSON_PRETTY_PRINT) : '',
                'settings_input'              => $field->settings ? json_encode($field->settings, JSON_PRETTY_PRINT) : '',
            ];
            if (in_array($field->type, ['radio', 'checkbox'])) {
                $this->editingFieldData['option_layout'] = $field->option_layout ?? 'horizontal';
            }
            $this->dispatch('open-modal', id: 'edit-field-modal');
        } catch (\Throwable $th) {
            Notification::make()->title('Error opening field')->body($th->getMessage())->danger()->send();
        }
    }

    public function saveEditField(): void
    {
        $field = FormField::findOrFail($this->editingFieldId);

        $rules = !empty($this->editingFieldData['validation_rules_input'])
            ? explode('|', $this->editingFieldData['validation_rules_input'])
            : null;

        $visibility = !empty($this->editingFieldData['visibility_conditions_input'])
            ? json_decode($this->editingFieldData['visibility_conditions_input'], true)
            : null;

        $settings = !empty($this->editingFieldData['settings_input'])
            ? json_decode($this->editingFieldData['settings_input'], true)
            : null;

        $option_layout = !empty($this->editingFieldData['option_layout'])
            ? $this->editingFieldData['option_layout']
            : null;

        $field->update([
            'label'                 => $this->editingFieldData['label'],
            'field_key'             => $this->editingFieldData['field_key'],
            'is_required'           => $this->editingFieldData['is_required'] ?? false,
            'placeholder'           => $this->editingFieldData['placeholder'],
            'help_text'             => $this->editingFieldData['help_text'],
            'option_layout'         => $option_layout,
            'validation_rules'      => $rules,
            'visibility_conditions' => $visibility,
            'settings'              => $settings,
        ]);

        $this->editingFieldId = null;
        $this->editingFieldData = [];
        $this->dispatch('close-modal', id: 'edit-field-modal');

        $this->loadTemplate();
        $this->markAsDraft();

        Notification::make()->title('Field updated!')->success()->send();
    }

    // ── Field Options Management ────────────────────────────────────

    public function openOptionsModal(int $fieldId): void
    {
        $field = FormField::with('options')->findOrFail($fieldId);
        $this->editingOptionsFieldId = $fieldId;

        $this->fieldOptions = $field->options
            ->sortBy('sort_order')
            ->map(fn ($opt) => [
                'id'         => $opt->id,
                'label'      => $opt->label,
                'value'      => $opt->value,
                'is_default' => $opt->is_default,
            ])
            ->values()
            ->toArray();

        if (empty($this->fieldOptions)) {
            $this->addOption();
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
        $this->fieldOptions = array_values(array_intersect_key(
            array_replace(array_flip($orderedIndexes), $this->fieldOptions),
            $this->fieldOptions
        ));
        $this->markAsDraft();
    }

    public function saveOptions(): void
    {
        try {
            $keepIds = [];
            foreach ($this->fieldOptions as $index => $option) {
                $result = FormFieldOption::updateOrCreate(
                    ['id' => $option['id'] ?? null],
                    [
                        'form_field_id' => $this->editingOptionsFieldId,
                        'label'         => $option['label'],
                        'value'         => $option['value'],
                        'sort_order'    => $index + 1,
                        'is_default'    => $option['is_default'],
                    ]
                );
                $keepIds[] = $result->id;
            }

            FormFieldOption::where('form_field_id', $this->editingOptionsFieldId)
                ->whereNotIn('id', $keepIds)
                ->delete();

        } catch (\Throwable $th) {
            Notification::make()->danger()->title('Error saving options')->body($th->getMessage())->send();
            return;
        }

        $count = count($this->fieldOptions);
        $this->loadTemplate();
        $this->markAsDraft();
        $this->dispatch('close-modal', id: 'field-options-modal');

        Notification::make()->success()->title('Options saved')->body("{$count} option(s) saved.")->send();
    }

    // ── Publishing & Versions ──────────────────────────────────────

    public function publishVersion(string $mode = 'create_new'): void
    {
        $hasAnyVersion = $this->template->versions()->exists();
        $activeVersion = $this->template->versions()->where('is_active', true)->first();

        if ($hasAnyVersion && !$activeVersion) {
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

            // Build schema snapshot
            $snapshot = $sections->map(fn ($section) => [
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
            ])->toArray();

            // Handle overwrite mode
            if ($activeVersion && $mode === 'update_existing') {
                $activeVersion->update([
                    'schema_json'  => $snapshot,
                    'published_at' => now(),
                ]);

                $this->template->update(['status' => 'published', 'active_version_id' => $activeVersion->id]);
                Notification::make()->success()->title("Version {$activeVersion->version} updated successfully")->send();
                return;
            }

            // Create new version release
            $nextVersion = ($this->template->versions()->max('version') ?? 0) + 1;
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
            $this->template->update(['status' => 'published', 'active_version_id' => $newVersion->id]);

            Notification::make()->success()->title("Version {$nextVersion} published!")->send();
        });

        $this->loadTemplate();
    }

    private function cloneSectionsIntoVersion($sections, FormTemplateVersion $version): void
    {
        foreach ($sections as $section) {
            $newSection = $section->replicate(['id', 'created_at', 'updated_at']);
            $newSection->form_template_version_id = $version->id;
            $newSection->section_key = Str::beforeLast($section->section_key, '_') . '_' . time();
            $newSection->save();

            foreach ($section->fields as $field) {
                $newField = $field->replicate(['id', 'created_at', 'updated_at']);
                $newField->form_section_id = $newSection->id;
                $newField->field_key = Str::beforeLast($field->field_key, '_') . '_' . time() . rand(10, 99);
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
            ->orderByDesc('version')
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
            FormTemplateVersion::where('form_template_id', $this->templateId)->update(['is_active' => false]);
            FormTemplateVersion::where('id', $versionId)->update(['is_active' => true]);
        });

        $this->loadTemplate();
        $this->openVersionsModal();

        $version = FormTemplateVersion::findOrFail($versionId);
        Notification::make()->success()->title("Version {$version->version} active — builder loaded.")->send();
    }

    // ── Header Actions ─────────────────────────────────────────────

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
                ->modalDescription(fn () => $this->workingVersion?->is_active
                    ? 'An active version is live. Choose target destination:'
                    : 'No active version exists. Publishing sets this as active.'
                )
                ->schema(function (): array {
                    if (!$this->workingVersion?->is_active) return [];

                    return [
                        Radio::make('mode')
                            ->label('Publishing Strategy')
                            ->options([
                                'update_existing' => 'Overwrite Existing Active Version',
                                'create_new'      => 'Publish as New Version',
                            ])
                            ->descriptions([
                                'update_existing' => 'Updates the current active version directly.',
                                'create_new'      => 'Preserves history and creates a new release.',
                            ])
                            ->default('update_existing')
                            ->required(),
                    ];
                })
                ->modalSubmitActionLabel(fn () => $this->workingVersion?->is_active ? 'Confirm & Publish' : 'Publish Initial Version')
                ->modalCancelActionLabel('Cancel')
                ->action(fn (array $data) => $this->publishVersion($data['mode'] ?? 'create_new')),
        ];
    }

    protected function markAsDraft(): void
    {
        if ($this->template->status !== 'draft') {
            $this->template->update(['status' => 'draft']);
            $this->template->refresh();
        }
    }
}