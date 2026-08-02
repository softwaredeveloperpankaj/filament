<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ !empty($student->submission_no)
            ? 'Admission Form - ' . $student->submission_no
            : 'Blank Admission Form'
        }}
    </title>
</head>
<body class="bg-gray-100 text-slate-800 font-sans">
    @php
        $answers = $student->form_data ?? [];
        $template = $student->formTemplate;
        $photo = null;

        if ($template) {
            foreach ($template->sections as $section) {
                foreach ($section->fields as $field) {
                    $value = $answers[$field->field_key] ?? null;

                    if (($field->type ?? '') === 'file' && !empty($value)) {
                        $photo = $value;
                        break 2;
                    }
                }
            }
        }
    @endphp

    <div class="max-w-[900px] mx-auto my-8 bg-white border border-slate-200 shadow-lg rounded-lg p-6 md:p-10 print:max-w-none print:mx-0 print:my-0 print:shadow-none print:rounded-none print:border-0 print:p-0">
        <div class="print:hidden flex justify-center gap-3 mb-6">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-md bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                Print Form
            </button>
            <button onclick="window.close()" class="px-5 py-2.5 rounded-md bg-slate-600 text-white font-semibold hover:bg-slate-700 transition">
                Close
            </button>
        </div>

        <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-4 mb-4">
            <div class="w-28 shrink-0">
                @if(!empty($student->school?->logo_url))
                    <img src="{{ $student->school->logo_url }}" alt="Logo" class="max-w-[100px] max-h-[100px] object-contain">
                @endif
            </div>

            <div class="flex-1 text-center">
                <h1 class="text-2xl font-extrabold uppercase text-indigo-900">
                    {{ $student->branch->name ?? 'School Management System' }}
                </h1>

                <p class="text-sm text-slate-700">
                    {{ $student->school->address ?? 'Main Campus, Academic Square' }}
                </p>

                <p class="text-sm text-slate-700">
                    Contact: {{ $student->school->phone ?? 'XXXX-XXXXXX' }}
                </p>

                @if(!empty($student->school?->email))
                    <p class="text-sm text-slate-700">
                        Email: {{ $student->school->email }}
                    </p>
                @endif
            </div>

            <div class="w-32 h-36 border-2 border-slate-800 flex items-center justify-center overflow-hidden text-xs text-center shrink-0">
                @if(!empty($photo))
                    <img src="{{ asset('storage/' . $photo) }}" alt="Student Photo" class="w-full h-full object-cover">
                @else
                    Paste Passport Size Photo
                @endif
            </div>
        </div>

        <div class="flex flex-wrap justify-between gap-4 mb-4 text-sm font-semibold">
            <div>
                Admission No:
                <span class="text-red-700">{{ $student->submission_no ?? '' }}</span>
            </div>

            <div>
                Roll No:
                <span class="text-indigo-900">{{ $student->roll_no ?? '' }}</span>
            </div>

            <div>
                Date:
                <span>
                    {{ !empty($student->submitted_at)
                        ? \Carbon\Carbon::parse($student->submitted_at)->format('d-m-Y')
                        : ''
                    }}
                </span>
            </div>
        </div>

        <div class="mb-5 rounded-md bg-indigo-900 px-3 py-2 text-center text-lg font-bold text-white">
            ADMISSION FORM
        </div>

        @if($template)
            @foreach($template->sections as $section)
                <div class="mt-6 mb-3 text-lg font-bold uppercase text-indigo-900 underline underline-offset-4">
                    {{ strtoupper($section->title) }}
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach($section->fields as $field)
                        @php
                            $value = $answers[$field->field_key] ?? '';
                            $type = $field->type ?? 'text';

                            if (is_array($value)) {
                                $value = implode(', ', $value);
                            }

                            if ($type === 'date' && !empty($value)) {
                                try {
                                    $value = \Carbon\Carbon::parse($value)->format('d-m-Y');
                                } catch (\Throwable $e) {
                                    //
                                }
                            }

                            if ($type === 'checkbox') {
                                $value = !empty($value) ? 'Yes' : 'No';
                            }

                            if ($type === 'select' || $type === 'radio') {
                                $option = $field->options->firstWhere('value', $value);
                                $value = $option?->label ?? $value;
                            }
                        @endphp

                        <div class="flex items-baseline gap-3">
                            <span class="min-w-[160px] text-sm font-semibold text-slate-800">
                                {{ $field->label ?? $field->field_key }}:
                            </span>

                            <span class="min-h-[24px] flex-1 border-b border-dotted border-slate-700 px-1 text-base text-slate-900 break-words">
                                @if($type === 'file')
                                    @if(!empty($value))
                                        Uploaded File
                                    @else
                                        —
                                    @endif
                                @else
                                    {{ $value ?: '—' }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @else
            <p class="text-slate-500">No form template assigned.</p>
        @endif

        <div class="mt-14 grid grid-cols-1 gap-8 md:grid-cols-3 print:mt-20">
            <div class="border-t border-slate-800 pt-2 text-center font-semibold">
                Guardian Signature
            </div>
            <div class="border-t border-slate-800 pt-2 text-center font-semibold">
                Clerk Signature
            </div>
            <div class="border-t border-slate-800 pt-2 text-center font-semibold">
                Principal Signature
            </div>
        </div>
    </div>
</body>
</html>