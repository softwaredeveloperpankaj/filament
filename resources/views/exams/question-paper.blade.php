<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $exam->name }} - {{ $examSubject->subject->name }} Question Paper</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; }
            @page { size: A4; margin: 15mm; }
        }
    </style>
</head>
<body class="font-sans text-sm text-gray-900 p-6 max-w-3xl mx-auto">

    {{-- Print trigger --}}
    <div class="no-print flex justify-end mb-4">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded">
            Print
        </button>
    </div>

    {{-- Header --}}
    <div class="text-center border-b-2 border-gray-800 pb-3 mb-4">
        <h1 class="text-lg font-bold uppercase">{{ $exam->branch->name }}</h1>
        <p class="text-xs text-gray-600">{{ $exam->branchClass->name }} - {{ $exam->section->name }}</p>
        <h2 class="text-base font-semibold mt-1">{{ $exam->name }}</h2>
        <p class="text-sm font-medium">{{ $examSubject->subject->name }}</p>
    </div>

    {{-- Meta info --}}
    <div class="flex justify-between text-xs mb-4">
        <span><strong>Max Marks:</strong> {{ $paper->total_marks }}</span>
        <span><strong>Duration:</strong> {{ $examSubject->duration_minutes }} minutes</span>
    </div>

    {{-- Instructions --}}
    @if($paper->instructions)
        <div class="border border-gray-400 rounded p-3 mb-5 text-xs bg-gray-50">
            <p class="font-semibold mb-1">Instructions:</p>
            <p>{{ $paper->instructions }}</p>
        </div>
    @endif

    {{-- Sections --}}
    @php $questionNumber = 1; @endphp

    @if($paper->sections)
        @foreach($paper->sections as $sectionKey => $sectionLabel)
            <div class="mb-6">
                <h3 class="font-semibold text-sm border-b border-gray-400 pb-1 mb-3">
                    Section {{ $sectionKey }} — {{ $sectionLabel }}
                </h3>

                @foreach($grouped->flatten() as $question)
                    @continue($loop->parent ?? false)
                @endforeach

                @foreach($questions as $question)
                    <div class="mb-4">
                        <p class="font-medium">
                            {{ $questionNumber++ }}.
                            {{ $question->question_text }}
                            <span class="text-xs text-gray-500">[{{ $question->assigned_marks }} marks]</span>
                        </p>

                        {{-- MCQ / True-False / Matching options --}}
                        @if(in_array($question->question_type->value, ['mcq', 'true_false', 'matching']) && $question->options)
                            <div class="pl-5 mt-1 grid grid-cols-2 gap-1 text-sm">
                                @foreach($question->options as $key => $value)
                                    <span>({{ $key }}) {{ $value }}</span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Fill in the blank --}}
                        @if($question->question_type->value === 'fill_blank')
                            <p class="pl-5 mt-1 text-sm text-gray-500">Answer: _______________________</p>
                        @endif

                        {{-- Short / Long answer space --}}
                        @if(in_array($question->question_type->value, ['short_answer', 'long_answer']))
                            <div class="pl-5 mt-2 border-b border-dotted border-gray-400
                                {{ $question->question_type->value === 'long_answer' ? 'h-24' : 'h-12' }}">
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    @else
        {{-- No sections defined, print flat list --}}
        @foreach($questions as $question)
            <div class="mb-4">
                <p class="font-medium">
                    {{ $questionNumber++ }}.
                    {{ $question->question_text }}
                    <span class="text-xs text-gray-500">[{{ $question->assigned_marks }} marks]</span>
                </p>

                @if(in_array($question->question_type->value, ['mcq', 'true_false', 'matching']) && $question->options)
                    <div class="pl-5 mt-1 grid grid-cols-2 gap-1 text-sm">
                        @foreach($question->options as $key => $value)
                            <span>({{ $key }}) {{ $value }}</span>
                        @endforeach
                    </div>
                @endif

                @if(in_array($question->question_type->value, ['short_answer', 'long_answer']))
                    <div class="pl-5 mt-2 border-b border-dotted border-gray-400
                        {{ $question->question_type->value === 'long_answer' ? 'h-24' : 'h-12' }}">
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    <div class="text-center text-xs text-gray-400 mt-8 border-t pt-2">
        — End of Paper —
    </div>

</body>
</html>