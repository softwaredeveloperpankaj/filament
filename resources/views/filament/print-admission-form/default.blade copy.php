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
            background-color: rgba(248, 250, 252, 0.7);
        }
        @media print {
            .modern-grid-row:nth-child(odd) {
                background-color: rgba(241, 245, 249, 0.4) !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased print:bg-white print:text-slate-900 selection:bg-indigo-500 selection:text-white p-4 sm:p-8 print:p-0">

    <div class="no-print fixed top-4 right-4 z-50 flex items-center gap-2 bg-white/80 backdrop-blur-md p-2 rounded-xl border border-slate-200 shadow-lg">
        <button onclick="window.print()" class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-all cursor-pointer shadow-sm shadow-indigo-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
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

    @php
        $school_logo = $student->branch->school->logo;
        $school_phone = $student->branch->school->phone;
        $school_email = $student->branch->school->email;
        $school_address = $student->branch->school->address;

        $student_photo = $student->form_data['student_photo'] ?? null;
    @endphp

    <div class="max-w-4xl mx-auto bg-white border border-slate-200/70 shadow-2xl rounded-2xl p-8 md:p-12 print:max-w-none print:mx-0 print:my-0 print:shadow-none print:rounded-none print:border-0 print:p-0">
        
        <!-- Header Branding Block (Modernized & Enlarged Logo) -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 border-b-2 border-slate-100 pb-8 mb-6 print:break-inside-avoid print:border-slate-200">
            <div class="flex flex-col sm:flex-row items-center text-center sm:text-left gap-6">
                <!-- Large Modernized Logo Container -->
                <div class="shrink-0 bg-slate-50 p-3 rounded-2xl border border-slate-100 print:bg-transparent print:border-slate-200 shadow-sm">
                    @if(!empty($school_logo))
                        <img src="{{ asset('storage/'.$school_logo) }}" alt="School Logo" class="w-28 h-28 md:w-32 md:h-32 object-contain rounded-xl">
                    @else
                        <div class="w-28 h-28 md:w-32 md:h-32 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                            <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                        </div>
                    @endif
                </div>
                
                <!-- Highly Highlighted Institutional Details -->
                <div class="space-y-2">
                    <h1 class="text-3xl md:text-4xl font-black tracking-tight text-slate-900 uppercase leading-none">
                        {{ $student->branch->name ?? 'School Management System' }}
                    </h1>
                    {{-- <div class="inline-flex px-3 py-1 bg-indigo-50 print:bg-slate-100 text-indigo-700 print:text-slate-800 text-xs font-bold uppercase tracking-widest rounded-md">
                        Institutional Identity
                    </div> --}}
                    <div class="text-sm text-slate-600 space-y-1 font-medium max-w-xl text-center">
                        <p class="text-slate-900 font-bold text-base tracking-wide">{{ $school_address ?? 'Main Campus, Academic Square' }}</p>
                        <p class="text-slate-500 font-mono text-xs flex flex-wrap gap-x-3 justify-center">
                            <span>Phone: <strong class="text-slate-700 font-sans font-semibold">{{ $school_phone ?? 'XXXX-XXXXXX' }}</strong></span>
                            @if(!empty($school_email))
                                <span class="hidden sm:inline text-slate-300">|</span>
                                <span>Email: <strong class="text-slate-700 font-sans font-semibold text-lowercase">{{ $school_email }}</strong></span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Passport Photo Canvas -->
            <div class="w-28 h-36 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center overflow-hidden shrink-0 shadow-inner print:border-slate-300">
                @if(!empty($student_photo))
                    <img src="{{ asset('storage/' . $student_photo) }}" alt="Student Photo" class="w-full h-full object-cover">
                @else
                    <span class="p-4 text-[10px] text-slate-400 font-bold uppercase tracking-wider text-center block">Affix Passport Photo</span>
                @endif
            </div>
        </div>

        <!-- Application Tracking Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 rounded-xl p-5 mb-8 flex flex-wrap justify-between items-center gap-4 shadow-md print:bg-none print:bg-slate-100 print:border print:border-slate-200 print:break-inside-avoid print:shadow-none">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest print:text-indigo-600">Document Classification</span>
                <h2 class="text-xl font-black text-white tracking-wide print:text-slate-900">OFFICIAL ADMISSION FORM</h2>
            </div>
            <div class="flex gap-6 text-right">
                <div class="border-r border-slate-700/60 pr-6 last:border-0 last:pr-0 print:border-slate-300">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider print:text-slate-500">Registration No</span>
                    <span class="text-sm font-bold text-white font-mono print:text-slate-900">{{ $student->registration_number ?? '—' }}</span>
                </div>
                <div class="border-r border-slate-700/60 pr-6 last:border-0 last:pr-0 print:border-slate-300">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider print:text-slate-500">Roll Number</span>
                    <span class="text-sm font-bold text-white font-mono print:text-slate-900">{{ $student->roll_no ?? '—' }}</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider print:text-slate-500">Date Issued</span>
                    <span class="text-sm font-bold text-white print:text-slate-900">
                        {{ !empty($student->submitted_at) ? \Carbon\Carbon::parse($student->submitted_at)->format('d M Y') : date('d M Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Dynamic Content Engine -->
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
                                                <span class="inline-flex items-center text-[11px] font-bold px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-md border border-emerald-100 print:bg-transparent print:text-slate-900 print:border-0 print:p-0">
                                                    {{ !empty($value) ? '✓ Attached' : '—' }}
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
        @else
            <div class="text-center py-8 border-2 border-dashed border-slate-200 rounded-xl">
                <p class="text-slate-400 font-medium text-sm">No structured template layout found mapped to student profile records.</p>
            </div>    
        @endif

        <!-- modern Signature Panel -->
        <div class="mt-16 pt-8 border-t border-slate-200/80 grid grid-cols-3 gap-8 print:mt-20 print:border-slate-300 print:break-inside-avoid">
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

    {{-- <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script> --}}
</body>
</html>