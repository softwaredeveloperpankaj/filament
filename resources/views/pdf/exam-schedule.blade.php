<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Exam Schedule - {{ $exam->name }}</title>

    <style>
        :root {
            --primary: #173b70;
            --primary-dark: #102b50;
            --accent: #0d9488;
            --text: #1e293b;
            --muted: #64748b;
            --border: #dbe3ee;
            --surface: #f4f7fb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: var(--surface);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            line-height: 1.5;
        }

        .page {
            max-width: 1150px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgb(15 23 42 / 7%);
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            background: #fff;
            border-bottom: 1px solid var(--border);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border: 0;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-secondary {
            background: #e9eef5;
            color: var(--primary-dark);
        }

        .document {
            padding: 32px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding-bottom: 24px;
            border-bottom: 3px solid var(--primary);
        }

        .school-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .school-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 58px;
            height: 58px;
            border-radius: 12px;
            background: var(--primary);
            color: #fff;
            font-size: 25px;
            font-weight: 800;
        }

        .school-name {
            margin: 0 0 4px;
            color: var(--primary-dark);
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -.5px;
        }

        .school-subtitle {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
        }

        .document-label {
            text-align: right;
        }

        .document-label h1 {
            margin: 0;
            color: var(--primary);
            font-size: 19px;
            letter-spacing: 1.5px;
        }

        .document-label p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 11px;
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 26px 0 14px;
            color: var(--primary-dark);
            font-size: 15px;
            font-weight: 700;
        }

        .section-heading::before {
            width: 4px;
            height: 19px;
            border-radius: 4px;
            background: var(--accent);
            content: "";
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .info-card {
            min-width: 0;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
        }

        .info-label {
            display: block;
            margin-bottom: 6px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
        }

        .info-value {
            overflow-wrap: anywhere;
            color: var(--text);
            font-size: 14px;
            font-weight: 700;
        }

        .schedule-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin: 22px 0 12px;
        }

        .schedule-summary h2 {
            margin: 0;
            color: var(--primary-dark);
            font-size: 17px;
        }

        .count-badge {
            padding: 5px 10px;
            border-radius: 20px;
            background: #e6f5f3;
            color: #0f766e;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        thead {
            display: table-header-group;
        }

        thead th {
            padding: 12px 10px;
            background: var(--primary);
            color: #fff;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .date {
            white-space: nowrap;
            font-weight: 700;
        }

        .time {
            white-space: nowrap;
            color: var(--primary);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .subject-name {
            display: block;
            color: var(--text);
            font-weight: 700;
        }

        .subject-code {
            display: inline-block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 10px;
        }

        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 10px;
            font-weight: 700;
        }

        .status-yes {
            background: #dcfce7;
            color: #166534;
        }

        .status-no {
            background: #f1f5f9;
            color: #475569;
        }

        .empty-state {
            padding: 35px 15px;
            color: var(--muted);
            text-align: center;
        }

        .empty-state strong {
            display: block;
            margin-bottom: 5px;
            color: var(--text);
            font-size: 14px;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 35px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 10px;
        }

        .signature {
            min-width: 170px;
            padding-top: 24px;
            border-bottom: 1px solid #94a3b8;
            color: var(--text);
            text-align: center;
        }

        @media (max-width: 700px) {
            body {
                padding: 8px;
            }

            .document {
                padding: 18px;
            }

            .header {
                align-items: flex-start;
                flex-direction: column;
            }

            .document-label {
                text-align: left;
            }

            .school-name {
                font-size: 18px;
            }

            .info-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .toolbar {
                padding: 12px;
            }

            .footer {
                flex-direction: column;
            }
        }

        @page {
            size: A4 landscape;
            margin: 12mm;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
                font-size: 10px;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page {
                max-width: none;
                border: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .toolbar {
                display: none !important;
            }

            .document {
                padding: 0;
            }

            .info-card {
                padding: 10px;
            }

            .section-heading {
                margin-top: 18px;
            }

            .table-wrapper {
                overflow: visible;
            }

            table {
                width: 100%;
                font-size: 10px;
            }

            thead th {
                padding: 9px 7px;
            }

            tbody td {
                padding: 8px 7px;
            }

            .footer {
                margin-top: 25px;
            }

            a {
                color: inherit;
                text-decoration: none;
            }
        }
    </style>
</head>

<body>
@php
    $schedules = $exam->schedules()
        ->with(['examSubject.subject', 'invigilator'])
        ->orderBy('exam_date', 'asc')
        ->orderBy('start_time', 'asc')
        ->get();
@endphp

<div class="page">

    <div class="toolbar">
        <button type="button" class="btn btn-secondary" onclick="window.print()">
            Print / Save as PDF
        </button>
    </div>

    <main class="document">

        <header class="header">
            <div class="school-brand">
                <div class="school-logo" aria-hidden="true">S</div>

                <div>
                    <p class="school-name">
                        {{ $exam->branch->name ?? 'School Name' }}
                    </p>

                    <p class="school-subtitle">
                        Official Examination Schedule
                    </p>
                </div>
            </div>

            <div class="document-label">
                <h1>EXAM SCHEDULE</h1>
                <p>Academic Examination Document</p>
            </div>
        </header>

        <section>
            <h2 class="section-heading">Examination Details</h2>

            <div class="info-grid">
                <div class="info-card">
                    <span class="info-label">Examination</span>
                    <div class="info-value">{{ $exam->name }}</div>
                </div>

                <div class="info-card">
                    <span class="info-label">Academic Year</span>
                    <div class="info-value">
                        {{ $exam->academicYear->name ?? 'N/A' }}
                    </div>
                </div>

                <div class="info-card">
                    <span class="info-label">Branch</span>
                    <div class="info-value">
                        {{ $exam->branch->name ?? 'N/A' }}
                    </div>
                </div>

                <div class="info-card">
                    <span class="info-label">Start Date</span>
                    <div class="info-value">
                        {{ $exam->start_date?->format('d M Y') ?? 'N/A' }}
                    </div>
                </div>

                <div class="info-card">
                    <span class="info-label">End Date</span>
                    <div class="info-value">
                        {{ $exam->end_date?->format('d M Y') ?? 'N/A' }}
                    </div>
                </div>

                <div class="info-card">
                    <span class="info-label">Scheduled Papers</span>
                    <div class="info-value">{{ $schedules->count() }}</div>
                </div>
            </div>
        </section>

        <section>
            <div class="schedule-summary">
                <h2>Subject-wise Examination Timetable</h2>

                <span class="count-badge">
                    {{ $schedules->count() }} {{ \Illuminate\Support\Str::plural('Paper', $schedules->count()) }}
                </span>
            </div>


            @php
                $groupedSchedules = $schedules->groupBy(function ($schedule) {
                    return implode('|', [
                        $schedule->examSubject?->subject?->id ?? '',
                        $schedule->exam_date?->format('Y-m-d') ?? '',
                        $schedule->start_time?->format('H:i') ?? '',
                        $schedule->end_time?->format('H:i') ?? '',
                    ]);
                });
            @endphp

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Exam Date &amp; Time</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Examination Room</th>
                            <th>Student Strength</th>
                            <th>Assigned Invigilator</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($groupedSchedules as $group)
                            @foreach ($group as $index => $schedule)
                                <tr>

                                    <td class="date">
                                        @if ($index === 0)
                                            <div>
                                                {{ $schedule->exam_date?->format('d M Y, l') ?? 'N/A' }}
                                            </div>
                                            <div>
                                                {{ $schedule->start_time?->format('h:i A') ?? '--:--' }}
                                                –
                                                {{ $schedule->end_time?->format('h:i A') ?? '--:--' }}
                                            </div>
                                        @endif
                                    </td>


                                    <td>
                                        @if ($index === 0)
                                            <span class="subject-name">
                                                {{ $schedule->examSubject?->subject?->name ?? 'N/A' }}
                                            </span>
                                            <span class="subject-code">
                                                Code: {{ $schedule->examSubject?->subject?->code ?? 'N/A' }}
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $schedule->examSubject?->branchClass?->name ?? '—' }}
                                    </td>

                                    <td>{{ $schedule->room_number ?: '—' }}</td>

                                    <td>
                                        {{ $schedule->student_strength ?? '—' }}
                                    </td>

                                    <td>
                                        {{ $schedule->invigilator?->name ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <strong>No examination schedules found</strong>
                                    No subjects have been scheduled for this examination yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>



            {{-- <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%">Date</th>
                            <th style="width: 18%">Examination Time</th>
                            <th style="width: 25%">Subject</th>
                            <th style="width: 12%">Room</th>
                            <th style="width: 18%">Invigilator</th>
                            <th style="width: 12%">Graded</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($schedules as $schedule)
                            <tr>
                                <td class="date">
                                    {{ $schedule->exam_date?->format('d M Y') ?? 'N/A' }}
                                </td>

                                <td class="time">
                                    {{ $schedule->start_time?->format('H:i') ?? '--:--' }}
                                    –
                                    {{ $schedule->end_time?->format('H:i') ?? '--:--' }}
                                </td>

                                <td>
                                    <span class="subject-name">
                                        {{ $schedule->examSubject?->subject?->name ?? 'N/A' }}
                                    </span>

                                    <span class="subject-code">
                                        Code: {{ $schedule->examSubject?->subject?->code ?? 'N/A' }}
                                    </span>

                                    @if ($schedule->examSubject?->branchClass?->name)
                                        <span class="subject-code" style="margin-top: 4px; display: block;">
                                            Class: {{ $schedule->examSubject->branchClass->name }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $schedule->room_number ?: '—' }}
                                </td>

                                <td>
                                    {{ $schedule->invigilator?->name ?? '—' }}
                                </td>

                                <td>
                                    @if ($schedule->examSubject?->is_graded)
                                        <span class="status status-yes">Yes</span>
                                    @else
                                        <span class="status status-no">No</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <strong>No examination schedules found</strong>
                                    No subjects have been scheduled for this examination yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> --}}



        </section>

        <footer class="footer">
            <div>
                <strong>Important:</strong>
                Students should arrive before the scheduled examination time.
                Please verify the timetable with the school administration.
                <br>
                Generated on {{ now()->format('d M Y, h:i A') }}
            </div>

            <div class="signature">
                Authorized Signature
            </div>
        </footer>

    </main>
</div>
</body>
</html>