<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card - {{ $result->student?->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 0; padding: 20px; color: #1a1a1a; font-size: 13px; }
        .card { border: 2px solid #1e293b; padding: 24px; border-radius: 8px; max-width: 800px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #1e293b; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { margin: 0; font-size: 22px; text-transform: uppercase; color: #0f172a; }
        .header h2 { margin: 4px 0; font-size: 14px; font-weight: normal; color: #475569; }
        .header h3 { margin: 6px 0 0; font-size: 16px; font-weight: bold; color: #0284c7; }
        .info-grid { width: 100%; margin-bottom: 16px; }
        .info-grid td { padding: 4px 8px; font-size: 13px; }
        .info-grid td strong { color: #334155; }
        table.marks-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.marks-table th, table.marks-table td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: center; }
        table.marks-table th { background-color: #f1f5f9; color: #1e293b; font-weight: bold; font-size: 12px; }
        table.marks-table td.subject-name { text-align: left; font-weight: 500; }
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 24px; }
        .summary-grid { width: 100%; }
        .summary-grid td { padding: 4px 10px; font-size: 13px; }
        .status-pass { color: #16a34a; font-weight: bold; }
        .status-fail { color: #dc2626; font-weight: bold; }
        .signatures { width: 100%; margin-top: 40px; }
        .signatures td { text-align: center; width: 33.33%; padding-top: 30px; border-top: 1px dashed #94a3b8; font-size: 12px; font-weight: bold; }
        @media print {
            body { padding: 0; }
            .card { border: none; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>{{ $result->exam?->branch?->name ?? 'INSTITUTE OF LEARNING' }}</h1>
            <h2>{{ $result->exam?->branchClass?->name }} — Academic Year {{ $result->exam?->academicYear?->name }}</h2>
            <h3>ACADEMIC PERFORMANCE REPORT CARD</h3>
            <div>{{ $result->exam?->name }}</div>
        </div>

        <table class="info-grid">
            <tr>
                <td><strong>Student Name:</strong> {{ $result->student?->name }}</td>
                <td><strong>Roll No:</strong> {{ $result->entry?->roll_no ?? '—' }}</td>
            </tr>
            <tr>
                <td><strong>Reg. Number:</strong> {{ $result->student?->registration_number }}</td>
                <td><strong>Section:</strong> {{ $result->entry?->section?->name ?? $result->exam?->section?->name ?? '—' }}</td>
            </tr>
        </table>

        <table class="marks-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th style="text-align: left;">Subject</th>
                    <th>Theory</th>
                    <th>Practical</th>
                    <th>Total Obtained</th>
                    <th>Max Marks</th>
                    <th>Grade</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($result->subject_wise_breakdown))
                    @foreach($result->subject_wise_breakdown as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="subject-name">{{ $row['subject_name'] }}</td>
                            <td>{{ $row['theory_obtained'] }}</td>
                            <td>{{ $row['practical_obtained'] }}</td>
                            <td><strong>{{ $row['total_obtained'] }}</strong></td>
                            <td>{{ $row['total_maximum'] }}</td>
                            <td><strong>{{ $row['grade'] }}</strong></td>
                            <td class="{{ $row['is_passed'] ? 'status-pass' : 'status-fail' }}">
                                {{ $row['is_passed'] ? 'PASS' : 'FAIL' }}
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <div class="summary-box">
            <table class="summary-grid">
                <tr>
                    <td><strong>Grand Total:</strong> {{ $result->grand_total_obtained }} / {{ $result->grand_total_maximum }}</td>
                    <td><strong>Overall Percentage:</strong> {{ $result->overall_percentage }}%</td>
                    <td><strong>Overall Grade:</strong> {{ $result->overall_grade }}</td>
                </tr>
                <tr>
                    <td><strong>Class Rank:</strong> #{{ $result->rank_in_class ?? '—' }}</td>
                    <td><strong>Section Rank:</strong> #{{ $result->rank_in_section ?? '—' }}</td>
                    <td><strong>Result Status:</strong> 
                        <span class="{{ $result->is_passed ? 'status-pass' : 'status-fail' }}">
                            {{ $result->is_passed ? 'PASSED' : 'NEEDS IMPROVEMENT' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        <table class="signatures">
            <tr>
                <td>Class Teacher</td>
                <td>Examination Controller</td>
                <td>Principal</td>
            </tr>
        </table>
    </div>
</body>
</html>