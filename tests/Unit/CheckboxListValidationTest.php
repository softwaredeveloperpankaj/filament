<?php

namespace Tests\Unit;

use App\Filament\Imports\StudentImporter;
use Illuminate\Validation\Rules\In;
use PHPUnit\Framework\TestCase;

class CheckboxListValidationTest extends TestCase
{
    public function test_checkbox_list_field_validates_array_of_allowed_values(): void
    {
        $importer = new StudentImporter(new \Filament\Actions\Imports\Models\Import(), [], []);
        $method = new \ReflectionMethod($importer, 'buildFormDataValidation');
        $method->setAccessible(true);

        [$rules] = $method->invoke($importer, [[
            'fields' => [[
                'field_key' => 'documents_submitted',
                'type' => 'checkbox',
                'is_active' => true,
                'is_required' => false,
                'options' => [
                    ['value' => 'birth_certificate'],
                    ['value' => 'aadhar_card'],
                ],
            ]],
        ]], [
            'documents_submitted' => ['birth_certificate'],
        ]);

        $this->assertContains('array', $rules['form_data.documents_submitted']);
        $this->assertArrayHasKey('form_data.documents_submitted.*', $rules);
        $this->assertTrue(collect($rules['form_data.documents_submitted.*'])->contains(fn ($rule) => $rule instanceof In));
    }
}
