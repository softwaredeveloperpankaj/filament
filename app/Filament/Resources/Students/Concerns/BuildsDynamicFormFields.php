<?php

namespace App\Filament\Resources\Students\Concerns;

use App\Models\FormField;
use App\Models\FormTemplate;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;

trait BuildsDynamicFormFields
{
    /**
     * Returns Filament form components from a FormTemplate's fields.
     * Used inside statePath('form_data') section.
     */
    public static function getDynamicFormComponents(?int $templateId): array
    {
        if (!$templateId) return [];

        $template = FormTemplate::with([
            'sections' => fn ($q) => $q->orderBy('sort_order'),
            'sections.fields' => fn ($q) => $q->orderBy('sort_order'),
            'sections.fields.options' => fn ($q) => $q->orderBy('sort_order'),
        ])->find($templateId);

        if (!$template) return [];

        $sections = [];

        foreach ($template->sections as $section) {
            $fields = collect($section->fields)
                ->map(fn ($field) => static::makeFormField($field))
                ->toArray();

            if (!empty($fields)) {
                $sections[] = Section::make($section->title)
                    ->schema($fields)
                    ->columns(2)
                    ->columnSpanFull();
            }
        }

        return $sections;
    }

    /**
     * Returns Filament infolist entries from a FormTemplate's fields.
     */
    public static function getDynamicInfolistEntries(FormTemplate $template): array
    {
        $entries = [];

        foreach ($template->formSections()->orderBy('order')->get() as $section) {
            $sectionEntries = $section->formFields()
                ->orderBy('order')
                ->get()
                ->map(fn ($field) => TextEntry::make(
                    'form_data.' . ($field->field_key ?? Str::slug($field->label, '_'))
                )->label($field->label)->placeholder('—'))
                ->toArray();

            if (!empty($sectionEntries)) {
                $entries[] = Section::make($section->title)
                    ->schema($sectionEntries)
                    ->columns(2);
            }
        }

        return $entries;
    }

    protected static function makeFormField(FormField $field): Component
    {
        $component = match ($field->type) {
            'text' => static::makeTextField($field),
            'email' => static::makeEmailField($field),
            'number' => static::makeNumberField($field),
            'date' => static::makeDateField($field),
            'textarea' => static::makeTextareaField($field),
            'select' => static::makeSelectField($field),
            'radio' => static::makeRadioField($field),
            'checkbox' => static::makeCheckboxField($field),
            'file' => static::makeFileField($field),
            default => static::makeTextField($field),
        };

        return static::applyCommonConfig($component, $field);
    }

    protected static function applyCommonConfig(Component $component, FormField $field): Component
    {
        $settings = $field->settings ?? [];
        $rules = $field->validation_rules ?? [];

        $component
            ->label($field->label)
            ->required((bool) $field->is_required);

        if (! empty($field->placeholder) && method_exists($component, 'placeholder')) {
            $component->placeholder($field->placeholder);
        }

        if (! empty($field->help_text) && method_exists($component, 'helperText')) {
            $component->helperText($field->help_text);
        }

        if (! empty($rules)) {
            $component->rules($rules);
        }

        if (array_key_exists('default', $settings) && method_exists($component, 'default')) {
            $component->default($settings['default']);
        }

        if (! empty($settings['column_span']) && method_exists($component, 'columnSpan')) {
            $component->columnSpan($settings['column_span']);
        }

        return $component;
    }

    protected static function fieldKey(FormField $field): string
    {
        return $field->field_key ?: Str::slug($field->label, '_');
    }

    protected static function fieldOptions(FormField $field): array
    {
        return $field->options?->pluck('label', 'value')->toArray() ?? [];
    }

    protected static function makeTextField(FormField $field): Component
    {
        $settings = $field->settings ?? [];

        $component = TextInput::make(static::fieldKey($field));

        if (! empty($settings['min_length'])) {
            $component->minLength((int) $settings['min_length']);
        }

        if (! empty($settings['max_length'])) {
            $component->maxLength((int) $settings['max_length']);
        }

        return $component;
    }

    protected static function makeEmailField(FormField $field): Component
    {
        $settings = $field->settings ?? [];

        $component = TextInput::make(static::fieldKey($field))
            ->email();

        if (! empty($settings['min_length'])) {
            $component->minLength((int) $settings['min_length']);
        }

        if (! empty($settings['max_length'])) {
            $component->maxLength((int) $settings['max_length']);
        }

        return $component;
    }

    protected static function makeNumberField(FormField $field): Component
    {
        $settings = $field->settings ?? [];

        $component = TextInput::make(static::fieldKey($field))
            ->numeric();

        if (isset($settings['min_value'])) {
            $component->minValue($settings['min_value']);
        }

        if (isset($settings['max_value'])) {
            $component->maxValue($settings['max_value']);
        }

        if (isset($settings['step'])) {
            $component->step($settings['step']);
        }

        return $component;
    }

    protected static function makeDateField(FormField $field): Component
    {
        return DatePicker::make(static::fieldKey($field));
    }

    protected static function makeTextareaField(FormField $field): Component
    {
        $settings = $field->settings ?? [];

        $component = Textarea::make(static::fieldKey($field))
            ->columnSpanFull();

        if (! empty($settings['rows'])) {
            $component->rows((int) $settings['rows']);
        }

        return $component;
    }

    protected static function makeSelectField(FormField $field): Component
    {
        $settings = $field->settings ?? [];

        $component = Select::make(static::fieldKey($field))
            ->options(static::fieldOptions($field));

        if (! empty($settings['searchable'])) {
            $component->searchable();
        }

        if (! empty($settings['multiple'])) {
            $component->multiple();
        }

        return $component;
    }

    protected static function makeRadioField(FormField $field): Component
    {
        return Radio::make(static::fieldKey($field))
            ->options(static::fieldOptions($field))
            ->inline(($field->option_layout ?? 'vertical') === 'horizontal');
    }

        protected static function makeCheckboxField(FormField $field): Component
        {
            $settings = $field->settings ?? [];
            $options = static::fieldOptions($field);

            if (! empty($options)) {
                $component = CheckboxList::make(static::fieldKey($field))
                    ->options($options);

                if (($field->option_layout ?? 'vertical') === 'horizontal') {
                    $component->columns(count($options) > 4 ? 4 : count($options));
                }

                return $component;
            }

            $component = Checkbox::make(static::fieldKey($field));

            if (! empty($settings['accepted'])) {
                $component->accepted();
            }

            return $component;
        }

    protected static function makeFileField(FormField $field): Component
    {
        $settings = $field->settings ?? [];

        $component = FileUpload::make(static::fieldKey($field));

        if (! empty($settings['directory'])) {
            $component->directory($settings['directory']);
        }

        if (! empty($settings['disk'])) {
            $component->disk($settings['disk']);
        }

        if (! empty($settings['visibility'])) {
            $component->visibility($settings['visibility']);
        }

        if (! empty($settings['accepted_file_types'])) {
            $component->acceptedFileTypes($settings['accepted_file_types']);
        }

        if (! empty($settings['max_size'])) {
            $component->maxSize((int) $settings['max_size']);
        }

        if (! empty($settings['multiple'])) {
            $component->multiple();
        }

        return $component;
    }

    
}