<?php

namespace App\Filament\Pages;

use App\Models\FormTemplate;
use App\Models\FormSection;
use App\Models\FormField;
use App\Models\FormTemplateVersion;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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
            'field_key'       => strtolower(str_replace(' ', '_', $label)),
            'type'            => $type,
            'sort_order'      => $section->fields()->count() + 1,
        ]);
        $this->loadTemplate();
    }

    public function deleteField(int $fieldId): void
    {
        FormField::destroy($fieldId);
        $this->loadTemplate();
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
        $this->dispatch('open-edit-field-modal');
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
        $this->dispatch('close-edit-field-modal');
        $this->loadTemplate();

        Notification::make()->title('Field updated!')->success()->send();
    }

    // ── Publish ───────────────────────────────────────────────────
    public function publishVersion(): void
    {
        $lastVersion = $this->template->versions()->max('version_number') ?? 0;
        $snapshot = $this->template->sections->map(fn($s) => [
            'title'  => $s->title,
            'fields' => $s->fields->toArray(),
        ])->toArray();

        FormTemplateVersion::create([
            'form_template_id' => $this->templateId,
            'version_number'   => $lastVersion + 1,
            'snapshot'         => $snapshot,
        ]);

        $this->template->update(['status' => 'published']);
        $this->loadTemplate();

        Notification::make()->title('Version ' . ($lastVersion + 1) . ' published!')->success()->send();
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
                ->url(fn() => '#'),

            Action::make('publish')
                ->label('Publish Version')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Publish a new version?')
                ->action('publishVersion'),
        ];
    }
}
