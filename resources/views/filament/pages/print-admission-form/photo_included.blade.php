@php
    // Find the photo/image field in answers
    $photoKey   = null;
    $photoValue = null;
    foreach ($sections as $section) {
        foreach ($section->fields as $f) {
            if ($f->type === 'image') {
                $photoKey   = $f->field_key ?: \Illuminate\Support\Str::slug($f->label, '_');
                $photoValue = data_get($answers, $photoKey);
                break 2;
            }
        }
    }
@endphp

<div class="admission-form mx-auto max-w-3xl p-6 bg-white text-gray-900">

    {{-- Header with photo box --}}
    <div class="flex justify-between items-start mb-6 border-b-2 border-gray-800 pb-4">
        <div>
            <h1 class="text-2xl font-bold uppercase tracking-wide">
                {{ $student->branch?->name }}
            </h1>
            <h2 class="text-lg font-semibold mt-1">Admission Form</h2>
            <div class="mt-2 text-sm text-gray-600">
                Registration No: <strong>{{ $student->registration_number ?? '—' }}</strong><br>
                Academic Year: <strong>{{ $student->academic_year ?? '—' }}</strong>
            </div>
        </div>
        <div class="border-2 border-gray-400 flex items-center justify-center bg-gray-50"
             style="width: 110px; height: 130px; flex-shrink: 0;">
            @if ($photoValue)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($photoValue) }}"
                     alt="Student Photo"
                     style="width: 110px; height: 130px; object-fit: cover;">
            @else
                <span class="text-xs text-gray-400 text-center px-2">Photo</span>
            @endif
        </div>
    </div>

    {{-- Reuse sections rendering --}}
    @include('filament.print-layouts.default', [
        'student'  => $student,
        'template' => $template,
        'sections' => $sections,
        'answers'  => $answers,
    ])
</div>