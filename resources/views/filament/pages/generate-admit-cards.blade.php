<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Filter Candidates</h3>
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Section (Optional)</label>
                    <select wire:model.live="section_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">-- All Sections --</option>
                        @foreach(\App\Models\Section::all() as $sec)
                            <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <x-filament::button wire:click="generateAll" color="primary" :disabled="!$exam_id || empty($entries)">
                        Generate Data
                    </x-filament::button>
                    <x-filament::button wire:click="downloadPdf" color="success" :disabled="!$exam_id || empty($entries)">
                        Download Bulk PDF
                    </x-filament::button>
                </div>
            </div>
        </div>

        @if(!empty($entries))
            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Students ({{ count($entries) }})</h3>
                </div>
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 font-semibold">
                            <th class="py-3 px-4">Roll No</th>
                            <th class="py-3 px-4">Student Name</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Admit Card Printed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($entries as $e)
                            <tr>
                                <td class="py-3 px-4 font-mono font-medium">{{ $e['roll_no'] }}</td>
                                <td class="py-3 px-4">{{ $e['student_name'] }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700">
                                        {{ $e['status'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($e['admit_card_printed'])
                                        <span class="text-xs text-emerald-600 font-bold">Yes</span>
                                    @else
                                        <span class="text-xs text-amber-600 font-medium">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>