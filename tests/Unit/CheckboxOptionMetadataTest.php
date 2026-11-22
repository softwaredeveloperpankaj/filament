<?php

namespace Tests\Unit;

use App\Filament\Resources\Students\Concerns\BuildsDynamicFormFields;
use App\Models\FormField;
use App\Models\FormFieldOption;
use PHPUnit\Framework\TestCase;

class CheckboxOptionMetadataTest extends TestCase
{
    public function test_blank_checkbox_option_values_are_generated_from_labels(): void
    {
        $field = new FormField();
        $field->setRelation('options', collect([
            new FormFieldOption(['label' => 'Yes', 'value' => '']),
            new FormFieldOption(['label' => 'No', 'value' => '']),
        ]));

        $options = DynamicFormFieldsHarness::fieldOptionsFor($field);

        $this->assertSame([
            'yes' => 'Yes',
            'no' => 'No',
        ], $options);
    }
}

class DynamicFormFieldsHarness
{
    use BuildsDynamicFormFields;

    public static function fieldOptionsFor(FormField $field): array
    {
        return self::fieldOptions($field);
    }
}
