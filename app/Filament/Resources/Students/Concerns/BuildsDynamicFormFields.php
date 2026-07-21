<?php

namespace App\Filament\Resources\Students\Concerns;

use App\Models\FormField;
use App\Models\FormTemplate;
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
            'formSections' => fn ($q) => $q->orderBy('order'),
            'formSections.formFields' => fn ($q) => $q->orderBy('order'),
            'formSections.formFields.options',
        ])->find($templateId);

        if (!$template) return [];

        $sections = [];

        foreach ($template->formSections as $section) {
            $fields = collect($section->formFields)
                ->map(fn ($field) => static::makeFormField($field))
                ->toArray();

            if (!empty($fields)) {
                $sections[] = \Filament\Schemas\Components\Section::make($section->title)
                    ->schema($fields)
                    ->columns(2);
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
        $key = $field->field_key ?? Str::slug($field->label, '_');
        $required = (bool) $field->is_required;

        return match ($field->type) {
            'text'     => TextInput::make($key)->label($field->label)->required($required),
            'email'    => TextInput::make($key)->label($field->label)->email()->required($required),
            'number'   => TextInput::make($key)->label($field->label)->numeric()->required($required),
            'phone'    => TextInput::make($key)->label($field->label)->tel()->required($required),
            'textarea' => Textarea::make($key)->label($field->label)->required($required)->columnSpanFull(),
            'date'     => DatePicker::make($key)->label($field->label)->required($required),
            'select'   => Select::make($key)
                            ->label($field->label)
                            ->options($field->options->pluck('label', 'value')->toArray())
                            ->required($required),
            'radio'    => Radio::make($key)
                            ->label($field->label)
                            ->options($field->options->pluck('label', 'value')->toArray())
                            ->required($required),
            'file'     => FileUpload::make($key)->label($field->label)->required($required),
            default    => TextInput::make($key)->label($field->label),
        };
    }
}