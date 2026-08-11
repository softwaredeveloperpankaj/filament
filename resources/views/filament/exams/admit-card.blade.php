<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admit Card - {{ $data['student_name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body class="font-sans p-6">
    <div class="max-w-md mx-auto border-2 border-gray-800 p-6">
        <!-- Header -->
        <div class="text-center mb-6 border-b-2 border-gray-800 pb-4">
            <h1 class="text-xl font-bold uppercase">{{ $data['branch'] }}</h1>
            <p class="text-sm text-gray-600">{{ $data['branch'] }} - {{ $data['class'] }}</p>
            <h2 class="text-lg font-semibold mt-2">ADMIT CARD</h2>
            <p class="text-sm">{{ $data['exam_name'] }}</p>
        </div>

        <!-- Student Info -->
        <div class="grid grid-cols-2 gap-2 mb-4 text-sm">
            <div><span class="font-medium">Name:</span> {{ $data['student_name'] }}</div>
            <div><span class="font-medium">Roll No:</span> {{ $data['roll_no'] }}</div>
            <div><span class="font-medium">Class:</span> {{ $data['class'] }}</div>
            <div><span class="font-medium">Section:</span> {{ $data['section'] }}</div>
        </div>

        <!-- Schedule Table -->
        <table class="w-full text-sm border-collapse mb-4">
            <thead>
                <tr class="bg-gray-100 border-b border-gray-400">
                    <th class="p-2 border border-gray-400">Subject</th>
                    <th class="p-2 border border-gray-400">Date</th>
                    <th class="p-2 border border-gray-400">Time</th>
                    <th class="p-2 border border-gray-400">Room</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['schedule'] as $s)
                <tr class="border-b border-gray-200">
                    <td class="p-2 border border-gray-300">{{ $s['subject'] }}</td>
                    <td class="p-2 border border-gray-300">{{ $s['date'] }}</td>
                    <td class="p-2 border border-gray-300">{{ $s['time'] }}</td>
                    <td class="p-2 border border-gray-300">{{ $s['room'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Instructions -->
        <div class="text-xs text-gray-700 border-t border-gray-400 pt-2">
            <p class="font-medium mb-1">Instructions:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>Report 30 minutes before exam time.</li>
                <li>Bring this admit card and valid ID.</li>
                <li>No electronic devices allowed.</li>
                @foreach($data['instructions'] as $inst)
                    <li>{{ $inst }}</li>
                @endforeach
            </ul>
        </div>

        <!-- Signature -->
        <div class="mt-6 flex justify-between text-sm">
            <div>Student Signature</div>
            <div>Principal Signature</div>
        </div>

        <div class="mt-2 text-xs text-right text-gray-500 no-print">
            Generated: {{ $data['generated_at'] }}
        </div>
    </div>
</body>
</html>