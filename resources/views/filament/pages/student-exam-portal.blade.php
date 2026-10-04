<x-filament-panels::page>
    <div class="space-y-6">
        @if(!$selected_attempt_id)
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Available Online Exam Attempts</h3>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse(\App\Models\OnlineExamAttempt::with(['exam', 'examSubject.subject', 'entry.student'])->limit(10)->get() as $att)
                        <div class="py-3 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-gray-900 dark:text-gray-100">{{ $att->exam?->name }}</span>
                                <span class="text-sm text-gray-500"> — {{ $att->examSubject?->subject?->name }}</span>
                                <div class="text-xs text-gray-400 mt-0.5">Candidate: {{ $att->entry?->student?->name ?? 'Student #' . $att->entry?->student_id }} (Roll: {{ $att->entry?->roll_no }})</div>
                            </div>
                            <x-filament::button wire:click="loadAttempt({{ $att->id }})" size="sm">
                                Open Attempt
                            </x-filament::button>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500 py-4">No online attempts found.</div>
                    @endforelse
                </div>
            </div>
        @else
            <!-- Active Test View -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 space-y-6">
                <div class="flex justify-between items-center border-b pb-4 border-gray-100 dark:border-gray-700">
                    <x-filament::button wire:click="$set('selected_attempt_id', null)" color="gray" size="sm">
                        ← Back to List
                    </x-filament::button>
                    <div>
                        <span class="text-sm text-gray-500 mr-2">Status:</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                            {{ strtoupper($status) }}
                        </span>
                    </div>
                </div>

                @if($status === 'not_started')
                    <div class="text-center py-8">
                        <h4 class="text-xl font-bold mb-2">Ready to begin your exam?</h4>
                        <p class="text-sm text-gray-500 mb-6">Make sure you have a stable internet connection. The timer will start immediately.</p>
                        <x-filament::button wire:click="startExam" color="success" size="lg">
                            Start Exam Now
                        </x-filament::button>
                    </div>
                @elseif($status === 'in_progress')
                    <div class="space-y-6">
                        @foreach($questions as $index => $q)
                            <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg space-y-3">
                                <div class="flex justify-between items-start font-semibold text-gray-900 dark:text-gray-100">
                                    <span>Q{{ $index + 1 }}. {!! strip_tags($q['text']) !!}</span>
                                    <span class="text-xs bg-gray-200 dark:bg-gray-800 px-2 py-0.5 rounded">{{ $q['marks'] }} Marks</span>
                                </div>
                                @if(!empty($q['options']))
                                    <div class="space-y-2 mt-2">
                                        @foreach($q['options'] as $key => $opt)
                                            <label class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 cursor-pointer">
                                                <input type="radio" wire:model.defer="answers.{{ $q['id'] }}" value="{{ $key }}" class="text-primary-600">
                                                <span class="text-sm"><span class="font-bold">{{ $key }}.</span> {{ $opt }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <textarea wire:model.defer="answers.{{ $q['id'] }}" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm" rows="3" placeholder="Type your answer here..."></textarea>
                                @endif
                            </div>
                        @endforeach
                        <div class="flex justify-end pt-4">
                            <x-filament::button wire:click="submitExam" color="primary" size="lg">
                                Submit Exam
                            </x-filament::button>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="inline-flex p-3 bg-green-100 text-green-600 rounded-full mb-3">
                            <x-filament::icon icon="heroicon-o-check-circle" class="w-8 h-8" />
                        </div>
                        <h4 class="text-xl font-bold mb-1">Attempt Completed</h4>
                        <p class="text-sm text-gray-500">Your responses have been recorded.</p>
                        @if($score !== null)
                            <p class="text-2xl font-bold text-primary-600 mt-4">Score: {{ $score }}</p>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>