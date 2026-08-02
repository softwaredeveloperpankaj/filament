<div class="p-6 bg-white rounded-xl shadow">
    <h2 class="text-xl font-bold mb-4">Admission Form</h2>

    <p><strong>Student:</strong> {{ $student->first_name }} {{ $student->last_name }}</p>
    <p><strong>Branch:</strong> {{ $student->branch?->name }}</p>
    <p><strong>Class:</strong> {{ $student->class?->name }}</p>
    <p><strong>Section:</strong> {{ $student->section?->name }}</p>
</div>