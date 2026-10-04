<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Card -->
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Select Exam</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Exam</label>
                    <select wire:model.live="exam_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">-- Choose Exam --</option>
                        @foreach(\App\Models\Exam::orderByDesc('id')->get() as $exam)
                            <option value="{{ $exam->id }}">{{ $exam->name }} ({{ $exam->branchClass?->name }} - {{ $exam->status?->getLabel() ?? $exam->status }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <x-filament::button wire:click="calculateResults" color="warning" :disabled="!$exam_id">
                        Calculate / Rerank
                    </x-filament::button>
                    <x-filament::button wire:click="publishAll" color="success" :disabled="!$exam_id || empty($resultsList)">
                        Publish All
                    </x-filament::button>
                </div>
            </div>
        </div>

        @if(!empty($summary))
            <!-- Summary Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Total Evaluated</span>
                    <p class="text-2xl font-bold mt-1 text-gray-800 dark:text-gray-100">{{ $summary['total'] }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Passed</span>
                    <p class="text-2xl font-bold mt-1 text-emerald-600">{{ $summary['passed'] }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Failed</span>
                    <p class="text-2xl font-bold mt-1 text-rose-600">{{ $summary['failed'] }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Pass Rate</span>
                    <p class="text-2xl font-bold mt-1 text-blue-600">{{ $summary['pass_percentage'] }}%</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Class Avg</span>
                    <p class="text-2xl font-bold mt-1 text-indigo-600">{{ $summary['avg_percentage'] }}%</p>
                </div>
            </div>
        @endif

        @if(!empty($resultsList))
            <!-- Results Table -->
            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-semibold">
                            <th class="py-3 px-4">Class Rank</th>
                            <th class="py-3 px-4">Section Rank</th>
                            <th class="py-3 px-4">Roll No</th>
                            <th class="py-3 px-4">Student Name</th>
                            <th class="py-3 px-4">Total Score</th>
                            <th class="py-3 px-4">Overall %</th>
                            <th class="py-3 px-4">Grade</th>
                            <th class="py-3 px-4">Result</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($resultsList as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                <td class="py-3 px-4 font-bold text-amber-600">#{{ $row['rank_class'] ?? '—' }}</td>
                                <td class="py-3 px-4 font-semibold text-gray-500">#{{ $row['rank_section'] ?? '—' }}</td>
                                <td class="py-3 px-4 font-mono">{{ $row['roll_no'] }}</td>
                                <td class="py-3 px-4 font-medium">{{ $row['student_name'] }}</td>
                                <td class="py-3 px-4 font-bold">{{ $row['obtained'] }}/{{ $row['maximum'] }}</td>
                                <td class="py-3 px-4 font-medium">{{ $row['percentage'] }}%</td>
                                <td class="py-3 px-4 font-bold">{{ $row['grade'] }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $row['is_passed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $row['is_passed'] ? 'PASS' : 'FAIL' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $row['status'] === 'published' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ strtoupper($row['status']) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>