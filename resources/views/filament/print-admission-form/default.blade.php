<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ !empty($student->registration_number)
            ? 'Admission Form - ' . $student->registration_number
            : 'Blank Admission Form'
        }}
    </title>
    <!-- Tailwind CSS Engine -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background-color: #ffffff !important;
                color: #0f172a !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
        }
        /* Custom print-safe utilities */
        .modern-grid-row:nth-child(odd) {
            background-color: rgba(248, 250, 252, 0.8);
        }
        @media print {
            .modern-grid-row:nth-child(odd) {
                background-color: rgba(241, 245, 249, 0.5) !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased print:bg-white print:text-slate-900 selection:bg-indigo-500 selection:text-white">

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

    <div class="max-w-4xl mx-auto my-8 bg-white border border-slate-200/60 shadow-xl rounded-2xl p-8 md:p-12 print:max-w-none print:mx-0 print:my-0 print:shadow-none print:rounded-none print:border-0 print:p-0">
        
        <!-- Header Branding Block -->
        <div class="flex items-center justify-between gap-6 border-b border-slate-100 pb-6 mb-6 print:break-inside-avoid print:border-slate-200">
            <div class="flex items-center gap-5">
                @if(!empty($student->school?->logo_url))
                    <img src="{{ $student->school->logo_url }}" alt="Logo" class="w-20 h-20 object-contain rounded-lg">
                @else
                    <div class="w-16 h-16 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 print:hidden">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">
                        {{ $student->branch->name ?? 'School Management System' }}
                    </h1>
                    <div class="text-xs text-slate-500 mt-1 space-y-0.5 font-medium">
                        <p class="text-slate-600 font-semibold">{{ $student->school->address ?? 'Main Campus, Academic Square' }}</p>
                        <p>Contact: {{ $student->school->phone ?? 'XXXX-XXXXXX' }} @if(!empty($student->school?->email)) | Email: {{ $student->school->email }} @endif</p>
                    </div>
                </div>
            </div>

            <!-- Passport Photo Canvas -->
            <div class="w-28 h-32 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center overflow-hidden shrink-0 print:border-slate-300">
                @if(!empty($photo))
                    <img src="{{ asset('storage/' . $photo) }}" alt="Student Photo" class="w-full h-full object-cover">
                @else
                    <span class="p-3 text-[10px] text-slate-400 font-semibold uppercase tracking-wider text-center">Paste Photo Here</span>
                @endif
            </div>
        </div>

        <!-- Application Tracking Banner -->
        <div class="bg-slate-900 rounded-xl p-4 mb-8 flex flex-wrap justify-between items-center gap-4 print:bg-slate-100 print:border print:border-slate-200 print:break-inside-avoid">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest print:text-indigo-600">Document Type</span>
                <h2 class="text-lg font-bold text-white tracking-wide print:text-slate-900">OFFICIAL ADMISSION FORM</h2>
            </div>
            <div class="flex gap-6 text-right">
                <div>
                    <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider print:text-slate-500">Submission No</span>
                    <span class="text-sm font-bold text-white font-mono print:text-slate-900">{{ $student->submission_no ?? '—' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider print:text-slate-500">Roll Number</span>
                    <span class="text-sm font-bold text-white font-mono print:text-slate-900">{{ $student->roll_no ?? '—' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wider print:text-slate-500">Date</span>
                    <span class="text-sm font-bold text-white print:text-slate-900">
                        {{ !empty($student->submitted_at) ? \Carbon\Carbon::parse($student->submitted_at)->format('d M Y') : date('d M Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Dynamic Content Engine -->
        @if($template)
            @foreach($template->sections as $section)
                <div class="mb-6 print:mb-4 print:break-inside-avoid">
                    <!-- Modern Section Header -->
                    <div class="flex items-center gap-3 mb-3">
                        <span class="h-5 w-1 bg-indigo-600 rounded-full print:bg-slate-900"></span>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-950 print:text-slate-900">
                            {{ $section->title }}
                        </h3>
                    </div>

                    <!-- Clean Form Rows Layout -->
                    <div class="border border-slate-100 rounded-xl overflow-hidden print:border-slate-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 divide-y divide-slate-100 md:divide-y-0 print:grid-cols-2 print:divide-y-0">
                            @foreach($section->fields as $field)
                                @php
                                    $value = $answers[$field->field_key] ?? '';
                                    $type = $field->type ?? 'text';

                                    if (is_array($value)) {
                                        $value = implode(', ', $value);
                                    }
                                    if ($type === 'date' && !empty($value)) {
                                        try { $value = \Carbon\Carbon::parse($value)->format('d-m-Y'); } catch (\Throwable $e) {}
                                    }
                                    if ($type === 'checkbox') {
                                        $value = !empty($value) ? 'Yes' : 'No';
                                    }
                                    if ($type === 'select' || $type === 'radio') {
                                        $option = $field->options?->firstWhere('value', $value);
                                        $value = $option?->label ?? $value;
                                    }
                                @endphp

                                <div class="modern-grid-row flex justify-between items-center px-4 py-2.5 gap-4 border-b border-slate-100 last:border-0 print:border-slate-200">
                                    <span class="text-xs font-semibold text-slate-500 print:text-slate-600 shrink-0">
                                        {{ $field->label ?? $field->field_key }}
                                    </span>
                                    <span class="text-sm font-medium text-slate-900 text-right break-words max-w-[65%]">
                                        @if($type === 'file')
                                            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded print:bg-transparent print:text-slate-900 print:p-0">
                                                {{ !empty($value) ? '✓ File Attached' : '—' }}
                                            </span>
                                        @else
                                            {{ $value ?: '—' }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center py-8 border-2 border-dashed border-slate-200 rounded-xl">
                <p class="text-slate-400 font-medium text-sm">No structured template layout found mapped to student profile records.</p>
            </div>
        @endif

        <!-- modern Signature Panel -->
        <div class="mt-16 pt-8 border-t border-slate-100 grid grid-cols-3 gap-8 print:mt-20 print:border-slate-200 print:break-inside-avoid">
            <div class="text-center space-y-4">
                <div class="h-10 border-b border-slate-200 print:border-slate-300 mx-auto w-4/5"></div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 print:text-slate-600">Guardian Signature</p>
            </div>
            <div class="text-center space-y-4">
                <div class="h-10 border-b border-slate-200 print:border-slate-300 mx-auto w-4/5"></div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 print:text-slate-600">Verifying Clerk</p>
            </div>
            <div class="text-center space-y-4">
                <div class="h-10 border-b border-slate-200 print:border-slate-300 mx-auto w-4/5"></div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 print:text-slate-600">Principal / Director</p>
            </div>
        </div>
    </div>

    <!-- Automatically open the print prompt window -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>