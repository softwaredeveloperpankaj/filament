<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Select Paper to Print</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Exam</label>
                    <select wire:model.live="exam_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">-- Choose Exam --</option>
                        @foreach(\App\Models\Exam::orderByDesc('id')->get() as $exam)
                            <option value="{{ $exam->id }}">{{ $exam->name }} ({{ $exam->branchClass?->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject</label>
                    <select wire:model.live="exam_subject_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" {{ !$exam_id ? 'disabled' : '' }}>
                        <option value="">-- Choose Subject --</option>
                        @if($exam_id)
                            @foreach(\App\Models\ExamSubject::where('exam_id', $exam_id)->with('subject')->get() as $es)
                                <option value="{{ $es->id }}">{{ $es->subject?->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <x-filament::button wire:click="downloadPdf" color="primary" :disabled="!$paperDetails">
                        Download Question Paper PDF
                    </x-filament::button>
                </div>
            </div>
        </div>

        @if($paperDetails)
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 space-y-4">
                <div class="flex items-center justify-between border-b pb-4 border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Paper Overview</h3>
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Ready to Print</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Total Marks</span>
                        <p class="text-xl font-bold">{{ $paperDetails['total_marks'] }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Total Questions</span>
                        <p class="text-xl font-bold">{{ $paperDetails['questions_count'] }}</p>
                    </div>
                </div>
                @if($paperDetails['instructions'])
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-medium text-gray-800 dark:text-gray-200">Instructions:</span>
                        <p>{{ $paperDetails['instructions'] }}</p>
                    </div>
                @endif
            </div>
        @elseif($exam_id && $exam_subject_id)
            <div class="p-8 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-gray-500">
                No question paper generated for this subject yet. Please build the paper first.
            </div>
        @endif
    </div>
</x-filament-panels::page>