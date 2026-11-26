@php
    /*
    |--------------------------------------------------------------------------
    | Optional admission form data
    |--------------------------------------------------------------------------
    | This view can be rendered with:
    |   1. A student:      ['student' => $student]
    |   2. A blank form:   ['formTemplate' => $formTemplate]
    |   3. No data:       view('...layout1')
    */

    $student = $student ?? null;
    $formTemplate = $formTemplate ?? ($student?->formTemplate ?? null);
    $version = $formTemplate?->activeVersion;

    $branch = $student?->branch ?? ($formTemplate?->branch ?? null);
    $school = $branch?->school;

    $schoolLogo = data_get($school, 'logo');
    $schoolPhone = data_get($school, 'phone');
    $schoolEmail = data_get($school, 'email');
    $schoolAddress = data_get($school, 'address');
    $schoolName = data_get($branch, 'name', 'School Management System');

    $formData = $student?->form_data ?? [];
    if (is_string($formData)) {
        $decodedFormData = json_decode($formData, true);
        $formData = is_array($decodedFormData) ? $decodedFormData : [];
    } elseif (is_object($formData)) {
        $formData = (array) $formData;
    }
    if (! is_array($formData)) {
        $formData = [];
    }

    $studentPhoto = $formData['student_photo'] ?? null;

    $sections = data_get($version, 'sections', []);
    if ($sections instanceof \Illuminate\Support\Collection) {
        // Keep the collection as-is.
    } elseif (is_array($sections)) {
        $sections = collect($sections);
    } elseif ($sections instanceof \Traversable) {
        $sections = collect($sections);
    } else {
        $sections = collect();
    }

    $registrationNumber = data_get($student, 'registration_number');
    $rollNumber = data_get($student, 'roll_no');
    $submittedAt = data_get($student, 'submitted_at');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $registrationNumber
            ? 'Admission Form - ' . $registrationNumber
            : 'Blank Admission Form'
        }}
    </title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            body {
                background-color: #ffffff !important;
                color: #0f172a !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .print-avoid-break {
                break-inside: avoid-page !important;
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 font-sans antialiased print:bg-white print:text-slate-900 selection:bg-indigo-500 selection:text-white p-4 sm:p-8 pt-20 sm:pt-24 print:p-0">

    <!-- Print & Close Action Panel -->
    <div class="no-print fixed top-4 right-4 z-50 flex items-center gap-2 bg-white/80 backdrop-blur-md p-2 rounded-xl border border-slate-200 shadow-lg">
        <button onclick="window.print()" class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-all cursor-pointer shadow-sm shadow-indigo-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H9a2 2 0 00-2 2v4zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Form
        </button>

        <button onclick="window.close()" class="flex items-center gap-1.5 px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wider rounded-lg transition-all cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Close
        </button>
    </div>

    <div class="max-w-4xl mx-auto bg-white border border-slate-200/70 shadow-2xl rounded-2xl p-8 md:p-12 print:max-w-none print:mx-0 print:my-0 print:shadow-none print:rounded-none print:border-0 print:p-0">

        <!-- Header Branding Block -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 border-b-2 border-slate-100 pb-8 mb-6 print:break-inside-avoid print:border-slate-200">
            <div class="flex flex-col sm:flex-row items-center text-center sm:text-left gap-6">
                <div class="shrink-0 bg-slate-50 p-3 rounded-2xl border border-slate-100 print:bg-transparent print:border-slate-200 shadow-sm">
                    @if(!empty($schoolLogo))
                        <img src="{{ asset('storage/' . $schoolLogo) }}" alt="School Logo" class="w-28 h-28 md:w-32 md:h-32 object-contain rounded-xl">
                    @else
                        <div class="w-28 h-28 md:w-32 md:h-32 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                            <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="12 14l9-5-9-5-9 5 9 5z"/>
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            </svg>
                        </div>
                    @endif
                </div>

                <div class="space-y-2">
                    <h1 class="text-3xl md:text-4xl font-black tracking-tight text-slate-900 uppercase leading-none">
                        {{ $schoolName }}
                    </h1>

                    <div class="text-sm text-slate-600 space-y-1 font-medium max-w-xl text-center sm:text-left">
                        <p class="text-slate-900 font-bold text-base tracking-wide">
                            {{ $schoolAddress ?: 'Main Campus, Academic Square' }}
                        </p>

                        <p class="text-slate-500 font-mono text-xs flex flex-wrap gap-x-3 justify-center sm:justify-start">
                            <span>
                                Phone:
                                <strong class="text-slate-700 font-sans font-semibold">{{ $schoolPhone ?: 'XXXX-XXXXXX' }}</strong>
                            </span>

                            @if($schoolEmail)
                                <span class="hidden sm:inline text-slate-300">|</span>
                                <span>
                                    Email:
                                    <strong class="text-slate-700 font-sans font-semibold lowercase">{{ $schoolEmail }}</strong>
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Passport Photo Canvas -->
            <div class="w-28 h-36 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center overflow-hidden shrink-0 shadow-inner print:border-slate-300">
                @if($studentPhoto)
                    <img src="{{ asset('storage/' . $studentPhoto) }}" alt="Student Photo" class="w-full h-full object-cover">
                @else
                    <span class="p-4 text-[10px] text-slate-400 font-bold uppercase tracking-wider text-center block">
                        Affix Passport Photo
                    </span>
                @endif
            </div>
        </div>

        <!-- Application Tracking Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-xl p-5 mb-8 flex flex-wrap justify-between items-center gap-4 shadow-md print:!bg-slate-900 print:text-white print:[color-adjust:exact] print:-webkit-color-adjust:exact print:break-inside-avoid print:shadow-none">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest print:text-indigo-400">Document Classification</span>
                <h2 class="text-xl font-black text-white tracking-wide print:text-white">OFFICIAL ADMISSION FORM</h2>
            </div>

            <div class="flex gap-6 text-right">
                <div class="border-r border-slate-700/60 pr-6 last:border-0 last:pr-0 print:border-slate-700">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider print:text-slate-400">Registration No</span>
                    <span class="text-sm font-bold text-white font-mono print:text-white">{{ $registrationNumber ?: '—' }}</span>
                </div>

                <div class="border-r border-slate-700/60 pr-6 last:border-0 last:pr-0 print:border-slate-700">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider print:text-slate-400">Roll Number</span>
                    <span class="text-sm font-bold text-white font-mono print:text-white">{{ $rollNumber ?: '—' }}</span>
                </div>

                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider print:text-slate-400">Date Issued</span>
                    <span class="text-sm font-bold text-white print:text-white">
                        {{ $submittedAt ? \Illuminate\Support\Carbon::parse($submittedAt)->format('d M Y') : now()->format('d M Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Dynamic Content Engine -->
        @forelse($sections as $section)
            @php
                $sectionActive = data_get($section, 'is_active', true);
                $sectionTitle = data_get($section, 'title', 'Information Section');
                $fields = data_get($section, 'fields', []);

                if ($fields instanceof \Illuminate\Support\Collection) {
                    $fields = $fields->sortBy(fn ($field) => data_get($field, 'sort_order', 0));
                } else {
                    $fields = collect(is_array($fields) ? $fields : [])->sortBy(fn ($field) => data_get($field, 'sort_order', 0));
                }
            @endphp

            @if($sectionActive)
                <div class="mb-8 print:mb-6 print-avoid-break">
                    <!-- Section Heading -->
                    <div class="flex items-center space-x-2 border-b border-slate-100 pb-2 mb-4 print:border-slate-200">
                        <div class="h-4 w-1 bg-indigo-600 rounded print:bg-indigo-600"></div>
                        <h2 class="text-md font-bold text-slate-900 tracking-wide uppercase">
                            {{ $sectionTitle }}
                        </h2>
                    </div>

                    <!-- Form Fields Data Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 print:grid print:grid-cols-2 print:gap-x-6 print:gap-y-4">
                        @foreach($fields as $field)
                            @if(data_get($field, 'is_active', true))
                                @php
                                    $key = data_get($field, 'field_key');
                                    $fieldType = data_get($field, 'type');
                                    $fieldLabel = data_get($field, 'label', 'Field');
                                    $isRequired = data_get($field, 'is_required', false);
                                    $value = $key ? data_get($formData, $key) : null;
                                @endphp

                                <div class="border-b border-slate-100 pb-2 print:border-slate-200">
                                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wide print:text-slate-400">
                                        {{ $fieldLabel }}
                                        @if($isRequired)
                                            <span class="text-rose-500">*</span>
                                        @endif
                                    </label>

                                    <div class="mt-1 text-sm text-slate-900 font-medium print:text-slate-900">
                                        @if($fieldType === 'checkbox')
                                            @php
                                                $isChecked = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                                            @endphp

                                            <span class="inline-flex items-center">
                                                <span class="w-4 h-4 rounded border border-indigo-600 flex items-center justify-center text-xs mr-2 print:border-indigo-600 {{ $isChecked ? 'bg-indigo-600 text-white print:bg-indigo-600' : 'bg-white' }}">
                                                    {!! $isChecked ? '✓' : '&nbsp;' !!}
                                                </span>
                                                <span class="text-slate-700 print:text-slate-700">{{ $isChecked ? 'Yes' : 'No' }}</span>
                                            </span>

                                        @elseif($fieldType === 'file')
                                            <span class="inline-flex items-center text-[11px] font-bold px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-md border border-emerald-100 print:bg-emerald-50 print:text-emerald-700 print:border-emerald-100">
                                                {{ !empty($value) ? '✓ Attached' : '—' }}
                                            </span>

                                        @elseif($fieldType === 'date')
                                            <span>
                                                {{ $value ? \Illuminate\Support\Carbon::parse($value)->format('F d, Y') : '—' }}
                                            </span>

                                        @elseif($fieldType === 'number' && $key === 'm_no')
                                            <span>
                                                {{ $value !== null && $value !== '' ? '+' . sprintf('%.0f', $value) : '—' }}
                                            </span>

                                        @else
                                            <span class="capitalize">
                                                {{ is_array($value) ? implode(', ', $value) : ($value ?? '—') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @empty
            <div class="text-center py-8 border-2 border-dashed border-slate-200 rounded-xl">
                <p class="text-slate-400 font-medium text-sm">
                    No structured template layout found.
                </p>
            </div>
        @endforelse

        <!-- Signature Panel -->
        <div class="mt-16 pt-8 border-t border-slate-200/80 grid grid-cols-3 gap-8 print:mt-20 print:border-slate-300 print-avoid-break">
            <div class="text-center space-y-3">
                <div class="h-10 border-b border-slate-200 print:border-slate-300 mx-auto w-4/5"></div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 print:text-slate-500">Guardian Signature</p>
            </div>

            <div class="text-center space-y-3">
                <div class="h-10 border-b border-slate-200 print:border-slate-300 mx-auto w-4/5"></div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 print:text-slate-500">Verifying Clerk</p>
            </div>

            <div class="text-center space-y-3">
                <div class="h-10 border-b border-slate-200 print:border-slate-300 mx-auto w-4/5"></div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 print:text-slate-500">Principal / Director</p>
            </div>
        </div>
    </div>
</body>
</html>
