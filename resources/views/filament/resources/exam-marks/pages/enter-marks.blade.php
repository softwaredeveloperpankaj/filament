<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Card -->
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Select Exam & Subject</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                <option value="{{ $es->id }}">{{ $es->subject?->name }} (Theory: {{ $es->theory_marks }}, Prac: {{ $es->practical_marks }})</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>

        @if(!empty($marksData))
            <!-- Spreadsheet Table -->
            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Enrolled Students ({{ count($marksData) }})</h3>
                    <x-filament::button wire:click="saveMarks" color="primary">
                        Save All Marks
                    </x-filament::button>
                </div>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-semibold">
                            <th class="py-3 px-4">Roll No</th>
                            <th class="py-3 px-4">Student Name</th>
                            <th class="py-3 px-4">Theory</th>
                            <th class="py-3 px-4">Practical</th>
                            <th class="py-3 px-4">Internal</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4">%</th>
                            <th class="py-3 px-4">Grade</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($marksData as $entryId => $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                <td class="py-3 px-4 font-mono font-medium">{{ $row['roll_no'] }}</td>
                                <td class="py-3 px-4 font-medium">{{ $row['student_name'] }}</td>
                                <td class="py-2 px-4">
                                    <input type="number" wire:model.defer="marksData.{{ $entryId }}.theory_obtained"
                                        class="w-20 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm"
                                        min="0" max="{{ $row['theory_max'] }}" {{ !empty($row['is_locked']) ? 'disabled' : '' }}>
                                    <span class="text-xs text-gray-400">/{{ $row['theory_max'] }}</span>
                                </td>
                                <td class="py-2 px-4">
                                    <input type="number" wire:model.defer="marksData.{{ $entryId }}.practical_obtained"
                                        class="w-20 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm"
                                        min="0" max="{{ $row['practical_max'] }}" {{ !empty($row['is_locked']) ? 'disabled' : '' }}>
                                    <span class="text-xs text-gray-400">/{{ $row['practical_max'] }}</span>
                                </td>
                                <td class="py-2 px-4">
                                    <input type="number" wire:model.defer="marksData.{{ $entryId }}.internal_obtained"
                                        class="w-20 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm"
                                        min="0" {{ !empty($row['is_locked']) ? 'disabled' : '' }}>
                                </td>
                                <td class="py-3 px-4 font-bold">{{ $row['total_obtained'] }}</td>
                                <td class="py-3 px-4 font-medium">{{ $row['percentage'] }}%</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $row['is_passed'] ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' }}">
                                        {{ $row['grade'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if(!empty($row['is_locked']))
                                        <span class="text-xs text-amber-600 font-medium">Locked</span>
                                    @else
                                        <span class="text-xs text-emerald-600 font-medium">Editable</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-6 flex justify-end">
                    <x-filament::button wire:click="saveMarks" color="primary" size="lg">
                        Save All Marks
                    </x-filament::button>
                </div>
            </div>
        @elseif($exam_id && $exam_subject_id)
            <div class="p-8 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-gray-500">
                No enrolled students found for this exam. Please ensure students are enrolled.
            </div>
        @endif
    </div>
</x-filament-panels::page>