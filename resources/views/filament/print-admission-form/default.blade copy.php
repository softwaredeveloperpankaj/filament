<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Form - {{ $student->registration_number }}</title>
    <!-- Tailwind CSS Play CDN (Ensure your PDF engine supports external CSS or compile it locally) -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        @media print {
            body {
                background-color: #fff;
                color: #000;
            }
            .no-print {
                display: none;
            }
        }
        /* Custom print optimization fixes */
        .pdf-page {
            page-break-after: always;
        }
        .pdf-page:last-child {
            page-break-after: avoid;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans p-4 sm:p-8">

    <!-- Container wrapper optimized for A4 print dimensions -->
    <div class="max-w-4xl mx-auto bg-white p-8 border border-slate-200 shadow-sm rounded-xl pdf-page relative">
        
        <!-- Top Accent Header Bar -->
        <div class="absolute top-0 left-0 right-0 h-2 bg-indigo-600 rounded-t-xl"></div>

        <!-- Document Header Area -->
        <div class="flex justify-between items-start border-b border-slate-200 pb-6 mb-8 mt-2">
            <div>
                <span class="text-xs font-bold tracking-wider uppercase text-indigo-600">Official Document</span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">ADMISSION APPLICATION</h1>
                <p class="text-sm text-slate-500 mt-1">Academic Year: <span class="font-semibold text-slate-700">{{ $student->academic_year ?? '2026-27' }}</span></p>
            </div>
            
            <!-- Dynamic Passport Photo Slot (Flips to placeholder if none uploaded) -->
            <div class="flex flex-col items-center">
                @if(!empty($student->form_data['file_upload_1785565636']))
                    <!-- Replace with storage_path() if generating via backend PDF library -->
                    <img src="{{ asset('storage/' . $student->form_data['file_upload_1785565636']) }}" 
                         alt="Student Photo" 
                         class="w-28 h-32 object-cover rounded-lg border border-slate-300 shadow-sm" />
                @else
                    <div class="w-28 h-32 bg-slate-100 border-2 border-dashed border-slate-300 rounded-lg flex flex-col items-center justify-center text-center p-2">
                        <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Affix Recent Passport Photo</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Institutional Metadata Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 p-4 rounded-xl mb-8 border border-slate-100">
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide">Registration No.</label>
                <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $student->registration_number }}</p>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide">Roll Number</label>
                <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $student->roll_no ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide">Admission Date</label>
                <p class="text-sm font-semibold text-slate-800 mt-0.5">
                    {{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('d M, Y') : 'N/A' }}
                </p>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wide">Status</label>
                <p class="mt-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                        {{ $student->status }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Dynamic Form Section Template -->
        {{-- @foreach($student->formTemplate->activeVersion ?? [] as $section)
            <div class="mb-8">
                <!-- Section Head -->
                <div class="flex items-center space-x-2 border-b border-slate-100 pb-2 mb-4">
                    <div class="h-4 w-1 bg-indigo-600 rounded"></div>
                    <h2 class="text-md font-bold text-slate-900 tracking-wide uppercase">{{ $section['title'] ?? 'Student Information' }}</h2>
                </div>

                <!-- Form Fields Data Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    @foreach(collect($section['fields'])->sortBy('sort_order') as $field)
                        @php 
                            $key = $field['field_key'];
                            $value = $student->form_data[$key] ?? null;
                        @endphp

                        @if($field['is_active'] ?? true)
                            <div class="border-b border-slate-100 pb-2">
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wide">
                                    {{ $field['label'] }}
                                    @if($field['is_required']) <span class="text-rose-500">*</span> @endif
                                </label>
                                
                                <div class="mt-1 text-sm text-slate-900 font-medium">
                                    @if($field['type'] === 'checkbox')
                                        <span class="inline-flex items-center">
                                            <span class="w-4 h-4 rounded border border-indigo-600 flex items-center justify-center text-xs mr-2 {{ $value ? 'bg-indigo-600 text-white' : 'bg-white' }}">
                                                {!! $value ? '✓' : '&nbsp;' !!}
                                            </span>
                                            <span class="text-slate-700">{{ $value ? 'Acknowledged / True' : 'No' }}</span>
                                        </span>
                                    @elseif($field['type'] === 'file')
                                        <span class="text-xs text-slate-500 font-mono bg-slate-100 px-2 py-1 rounded border border-slate-200 block truncate">
                                            {{ $value ?? 'No file submitted' }}
                                        </span>
                                    @elseif($field['type'] === 'date')
                                        <span>{{ $value ? \Carbon\Carbon::parse($value)->format('F d, Y') : '—' }}</span>
                                    @elseif($field['type'] === 'number' && $key === 'm_no')
                                        <!-- Handles scientific notation rendering bug cleanly -->
                                        <span>+{{ sprintf('%.0f', $value) }}</span>
                                    @else
                                        <span class="capitalize">{{ $value ?? '—' }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach --}}
<!-- Dynamic Form Section Template -->
@if(($version = $student->formTemplate->activeVersion) && $version->sections)
    @foreach($version->sections as $section)
        @if($section->is_active ?? true)
            <div class="mb-8">
                <!-- Section Head -->
                <div class="flex items-center space-x-2 border-b border-slate-100 pb-2 mb-4">
                    <div class="h-4 w-1 bg-indigo-600 rounded"></div>
                    <h2 class="text-md font-bold text-slate-900 tracking-wide uppercase">
                        {{ $section->title ?? 'Information Section' }}
                    </h2>
                </div>

                <!-- Form Fields Data Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                    @foreach(collect($section->fields)->sortBy('sort_order') as $field)
                        @if($field->is_active ?? true)
                            @php 
                                $key = $field->field_key;
                                // Handle both object and array form_data structures safely
                                $value = is_array($student->form_data) 
                                    ? ($student->form_data[$key] ?? null) 
                                    : ($student->form_data->$key ?? null);
                            @endphp

                            <div class="border-b border-slate-100 pb-2">
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wide">
                                    {{ $field->label }}
                                    @if($field->is_required) <span class="text-rose-500">*</span> @endif
                                </label>
                                
                                <div class="mt-1 text-sm text-slate-900 font-medium">
                                    @if($field->type === 'checkbox')
                                        <span class="inline-flex items-center">
                                            <span class="w-4 h-4 rounded border border-indigo-600 flex items-center justify-center text-xs mr-2 {{ $value ? 'bg-indigo-600 text-white' : 'bg-white' }}">
                                                {!! $value ? '✓' : '&nbsp;' !!}
                                            </span>
                                            <span class="text-slate-700">{{ $value ? 'Yes' : 'No' }}</span>
                                        </span>
                                    @elseif($field->type === 'file')
                                        <span class="text-xs text-slate-500 font-mono bg-slate-100 px-2 py-1 rounded border border-slate-200 block truncate">
                                            {{ $value ?? 'No file submitted' }}
                                        </span>
                                    @elseif($field->type === 'date')
                                        <span>{{ $value ? \Carbon\Carbon::parse($value)->format('F d, Y') : '—' }}</span>
                                    @elseif($field->type === 'number' && $key === 'm_no')
                                        <span>+{{ sprintf('%.0f', $value) }}</span>
                                    @else
                                        <span class="capitalize">{{ $value ?? '—' }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
@endif
        <!-- Declaration & Signatures Block Area -->
        <div class="mt-16 pt-8 border-t border-slate-200">
            <p class="text-[11px] text-slate-400 text-justify leading-relaxed">
                <strong>Declaration:</strong> I hereby certify that the information provided in this admission document is true, accurate, and complete to the best of my knowledge. Any deliberate misrepresentation of facts may result in immediate cancellation of the student's admission status.
            </p>

            <div class="grid grid-cols-2 gap-12 mt-16">
                <div class="text-center">
                    <div class="border-b border-slate-300 h-8"></div>
                    <p class="text-xs font-semibold text-slate-500 mt-2 uppercase tracking-wider">Parent / Guardian Signature</p>
                </div>
                <div class="text-center">
                    <div class="border-b border-slate-300 h-8"></div>
                    <p class="text-xs font-semibold text-slate-500 mt-2 uppercase tracking-wider">Authorized Officer Stamp</p>
                </div>
            </div>
        </div>

        <!-- System Footer -->
        <div class="mt-12 text-center text-[10px] text-slate-400 font-mono tracking-wider border-t border-slate-100 pt-4">
            Generated via Portal System on {{ now()->format('Y-m-d H:i:s') }} | ID Ref: {{ $student->id }}
        </div>
    </div>

</body>
</html>