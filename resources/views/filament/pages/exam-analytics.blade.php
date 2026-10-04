<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Exam</label>
            <select wire:model.live="exam_id" class="w-full md:w-1/3 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                @foreach(\App\Models\Exam::orderByDesc('id')->get() as $exam)
                    <option value="{{ $exam->id }}">{{ $exam->name }} ({{ $exam->branchClass?->name }})</option>
                @endforeach
            </select>
        </div>

        @if(!empty($analytics))
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-xs text-gray-500 uppercase">Candidates</span>
                    <p class="text-2xl font-bold mt-1">{{ $analytics['total_students'] }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-xs text-gray-500 uppercase">Pass Rate</span>
                    <p class="text-2xl font-bold mt-1 text-emerald-600">{{ $analytics['pass_rate'] }}%</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-xs text-gray-500 uppercase">Passed</span>
                    <p class="text-2xl font-bold mt-1 text-blue-600">{{ $analytics['passed'] }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 text-center">
                    <span class="text-xs text-gray-500 uppercase">Failed</span>
                    <p class="text-2xl font-bold mt-1 text-rose-600">{{ $analytics['failed'] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Grade Distribution -->
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-semibold mb-4">Grade Distribution</h3>
                    <div class="space-y-3">
                        @foreach($analytics['grade_distribution'] as $grade => $count)
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-bold">{{ $grade }}</span>
                                    <span class="text-gray-500">{{ $count }} ({{ $analytics['total_students'] > 0 ? round(($count / $analytics['total_students']) * 100) : 0 }}%)</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $analytics['total_students'] > 0 ? round(($count / $analytics['total_students']) * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                    <h3 class="text-base font-semibold mb-4">Top 5 Performers</h3>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($analytics['top_performers'] as $tp)
                            <div class="py-2.5 flex justify-between items-center text-sm">
                                <div>
                                    <span class="font-bold text-amber-600 mr-2">#{{ $tp['rank'] }}</span>
                                    <span class="font-medium">{{ $tp['name'] }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-bold">{{ $tp['percentage'] }}%</span>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded">{{ $tp['grade'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>