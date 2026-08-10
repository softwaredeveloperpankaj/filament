@php
    function numberToWords($number)
    {
        $ones = [
            '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven',
            'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen',
            'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen',
            'nineteen'
        ];

        $tens = [
            '', '', 'twenty', 'thirty', 'forty', 'fifty',
            'sixty', 'seventy', 'eighty', 'ninety'
        ];

        if ($number < 20) {
            return $ones[$number];
        }

        if ($number < 100) {
            return $tens[intval($number / 10)] .
                ($number % 10 ? '-' . $ones[$number % 10] : '');
        }

        if ($number < 1000) {
            return $ones[intval($number / 100)] . ' hundred' .
                ($number % 100 ? ' ' . numberToWords($number % 100) : '');
        }

        if ($number < 1000000) {
            return numberToWords(intval($number / 1000)) . ' thousand' .
                ($number % 1000 ? ' ' . numberToWords($number % 1000) : '');
        }

        return (string) $number;
    }

    if(isset($student->created_at)) {
        $admission_date_day = date('d', strtotime($student->created_at));
        $admission_date_month = date('m', strtotime($student->created_at));
        $admission_date_year = date('Y', strtotime($student->created_at));
    } else {
        $admission_date_day = '';
        $admission_date_month = '';
        $admission_date_year = '';
    }

    if(isset($student->registration_number)){
        $sr_no = $student->registration_number;
    }else{
        $sr_no = '';
    }

    if(isset($student->branch_class_id)) {
        $grade_name = $student->class->name;
    } else {
        $grade_name = '';
    }

    if(isset($student->section_id)) {
        $section_name = $student->section->name;
    } else {
        $section_name = '';
    }

    $student_session_year_from = explode('-', $student->academic_year)[0] ?? '';
    $student_session_year_to = explode('-', $student->academic_year)[1] ?? '';

    $answers = $student->form_data;

    // Student Information
    $student_name = $answers['student_name'] ?? '';
    $student_name_hindi = $answers['student_name_hindi'] ?? '';

    $student_dob = $answers['student_dob'] ?? '';
    $student_dob_day = $student_dob ? date('d', strtotime($student_dob)) : '';
    $student_dob_month = $student_dob ? date('m', strtotime($student_dob)) : '';
    $student_dob_year = $student_dob ? date('Y', strtotime($student_dob)) : '';

    $dob_in_words = '';

    if (!empty($student_dob)) {
        $date = \Carbon\Carbon::parse($student_dob);

        $day = ucfirst(numberToWords($date->day));
        $month = $date->format('F');
        $year = ucfirst(numberToWords($date->year));

        $dob_in_words = "{$day} {$month} {$year}";
    }

    $student_gender = $answers['student_gender'] ?? '';
    $student_aadhar = $answers['student_aadhar'] ?? '';
    $student_nationality = $answers['student_nationality'] ?? '';
    $student_religion_caste = $answers['student_religion_caste'] ?? '';

    // Academic Information
    $academic_last_school = $answers['academic_last_school'] ?? '';
    $academic_percentage = $answers['academic_percentage'] ?? '';
    $academic_pen = $answers['academic_pen'] ?? '';
    $academic_apaar_id = $answers['academic_apaar_id'] ?? '';

    // Parent Information
    $parents_fathers_name = $answers['parents_fathers_name'] ?? '';
    $parents_fathers_name_hindi = $answers['parents_fathers_name_hindi'] ?? '';
    $parents_fathers_aadhar = $answers['parents_fathers_aadhar'] ?? '';

    $parents_mothers_name = $answers['parents_mothers_name'] ?? '';
    $parents_mothers_name_hindi = $answers['parents_mothers_name_hindi'] ?? '';
    $parents_mothers_aadhar = $answers['parents_mothers_aadhar'] ?? '';
    $parents_mobile_no_a = $answers['parents_mobile_no_a'] ?? '';
    $parents_mobile_no_b = $answers['parents_mobile_no_b'] ?? '';
    $guardian_name_relation = $answers['guardian_name_relation'] ?? '';
    $parents_address = $answers['parents_address'] ?? '';

    // Documents
    $doc_student_photo = $answers['doc_student_photo'] ?? '';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $student->branch->name.'-' ?? '' }} Admission Form</title>
    <link
        href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&family=Rajdhani:wght@500;600;700&display=swap"
        rel="stylesheet">

    <style>
        /* ====== SCREEN STYLES ====== */

        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --blue: #1a2f6e;
            --blue-light: #2a4099;
            --border: #1a2f6e;
            --text: #0d1a3e;
            --box-size: 22px;
        }

        body {
            background: #d9dde8;
            padding: 30px 20px;
            font-family: 'Rajdhani', Arial, sans-serif;
        }
        .no-print{
            text-align: center;
        }
        .btn {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .btn-primary { background: #0d6efd; color: #fff; }
        .btn-secondary { background: #6c757d; color: #fff; }        
        /* ====== FORM WRAPPER ====== */

        .form {
            width: 820px;
            margin: auto;
            background: #fff;
            border: 2.5px solid var(--blue);
            padding: 18px 20px 20px;
            color: var(--text);
            box-shadow: 0 4px 32px rgba(26, 47, 110, 0.13);
        }

        /* ====== TOP BAR (Sr / Session / Date) ====== */

        .session-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            flex-wrap: wrap;
        }

        .sr-line {
            width: 185px;
            height: 24px;
            border: 1px solid var(--blue);
            border-radius: 8px 0 8px 0;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ====== HEADER ====== */

        .header {
            border-bottom: 3px double var(--blue);
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .header-top {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            width: 100%;
        }

        .header-bottom {
            margin-top: 10px;
        }


        .left-head {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            min-width: 148px;
        }

        .udise {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3px;
            color: var(--blue);
            margin-bottom: 8px;
        }

        .logo {
            width: 82px;
            height: 82px;
            border: 3px solid var(--blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 9.5px;
            font-weight: 700;
            line-height: 14px;
            color: var(--blue);
            letter-spacing: 0.5px;
            background: #f0f3fb;
            overflow: hidden;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .center-head {
            flex: 1;
            text-align: center;
            padding-top: 2px;
        }

        .admission-label {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: underline;
            color: var(--blue);
            margin-bottom: 2px;
        }

        .school-name {
            font-family: 'EB Garamond', Georgia, serif;
            font-size: 30px;
            font-weight: 700;
            line-height: 1.1;
            color: var(--blue);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .place-badge {
            width: 280px;
            margin: 0 auto;
            background: var(--blue);
            margin-top: 4px;
            color: #fff;
            border-radius: 30px;
            padding: 4px 12px;
        }

        .place-badge span {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .medium-badge {
            display: inline-block;
            border: 2px solid var(--blue);
            border-radius: 12px 0;
            padding: 2px;
            margin-top: 7px;
            font-size: 12px;
            font-weight: 700;
            color: var(--blue);
        }

        .medium-badge span {
            display: inline-block;
            background: var(--blue);
            color: #fff;
            padding: 3px 8px;
            border-radius: 12px 0;
            font-size: 16px;
        }

        .right-head {
            min-width: 148px;
            text-align: right;
            padding-top: 4px;
        }

        .phone {
            font-size: 15px;
            font-weight: 700;
            color: var(--blue);
            line-height: 1.6;
        }

        /* ====== UTILITY ====== */

        .row {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 7px;
            flex-wrap: wrap;
        }

        .label {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
            white-space: nowrap;
            padding-left: 4px;
        }

        .boxes {
            display: flex;
            gap: 0;
        }

        .box {
            width: var(--box-size);
            height: var(--box-size);
            border: 1px solid var(--blue);
            border-right: none;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .br-1 {
            border-right: 1px solid var(--blue);
        }

        .long-line {
            border: 1px solid var(--blue);
            height: 24px;
            flex: 1;
            min-width: 40px;
        }

        .dotted {
            flex: 1;
            border-bottom: 1.5px dotted #444;
            height: 18px;
            min-width: 40px;
        }

        /* ====== MAIN SECTION ====== */

        .main {
            display: flex;
            margin-top: 8px;
            gap: 14px;
        }

        .left {
            flex: 1;
            min-width: 0;
        }

        .photo {
            width: 118px;
            height: 148px;
            border: 4px double var(--blue);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #aab;
            letter-spacing: 0.5px;
            background: #f7f8fc;
            text-align: center;
        }

        /* ====== NAME BLOCK (fields stuck together) ====== */

        .name-block {
            overflow: hidden;
            border: 1px solid var(--blue);
            margin-top: 4px;
        }

        .name-block .row {
            margin-top: 0;
            border-bottom: 1px solid var(--blue);
            gap: 5px;
            flex-wrap: nowrap;
            align-items: center;
        }

        .name-block .row:last-child {
            border-bottom: none;
        }

        .name-block .long-line {
            height: 28px;
            border: none;
        }

        .name-block .row .label {
            flex: 0 0 80px; /* fixed width for In Hindi / In English */
            min-width: 80px;
        }        
        
        .name-block .boxes {
            flex: 1;
            min-width: 0;
            display: flex;
            justify-content: flex-start;
            overflow: hidden;
        }

        .name-block .box {
            width: 23px;
            min-width: 23px;
            height: 28px;
            border-top: none;
            border-bottom: none;
            border-right: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        /* ====== SECTION TITLE ====== */

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--blue);
            margin-top: 9px;
            margin-bottom: 1px;
        }

        /* ====== SIGN ====== */

        .sign-section {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 22px;
        }

        .sign {
            flex: 1;
            text-align: center;
        }

        .sign-box {
            height: 68px;
            border: 1px solid var(--blue);
            background: #f7f8fc;
        }

        .sign-title {
            margin-top: 5px;
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text);
        }

        /* ====== NOTE ====== */

        .note {
            margin-top: 10px;
            font-size: 11.5px;
            color: #222;
            border-top: 1px solid #c5cce8;
            padding-top: 7px;
            line-height: 1.6;
        }

        /* ====== ID CARD ====== */

        .id-title {
            margin-bottom: 10px;
            display: flex;
            justify-content: center;
        }

        .id-badge {
            display: inline-block;
            background: var(--blue);
            color: #fff;
            padding: 3px 22px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .id-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 14px;
        }

        .id-left {
            flex: 1;
        }

        .id-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 11px;
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }

        .id-line {
            flex: 1;
            border-bottom: 1.5px solid #222;
            height: 18px;
        }

        .id-right {
            width: 122px;
            flex-shrink: 0;
        }

        .id-sr {
            width: 88px;
            height: 26px;
            border: 2px solid var(--blue);
            font-size: 8px;
            border-radius: 8px 0;
            text-align: center;
        }

        .id-photo {
            width: 110px;
            height: 138px;
            border: 4px double var(--blue);
            margin-left: auto;
            background: #f7f8fc;
            text-align: center;
            color: #aab;
            font-size: 11px;
            display: flex;
            align-items: center;
        }

        /* ====== SEPARATOR ====== */
        .sep {
            width: 5px;
            display: inline-block;
        }

        .id-ribbon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 200px;
            position: relative;
            background: #1f356e;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            height: 26px;
            text-transform: uppercase;
        }

        /* Left Arrow */
        .id-ribbon:before {
            content: "";
            position: absolute;
            left: -12.5px;
            top: 0;
            width: 0;
            height: 0;
            border-top: 13px solid transparent;
            border-bottom: 13px solid transparent;
            border-right: 13px solid #1f356e;
        }

        /* Right Arrow */
        .id-ribbon:after {
            content: "";
            position: absolute;
            right: -13px;
            top: 0;
            width: 0;
            height: 0;
            border-top: 13px solid transparent;
            border-bottom: 13px solid transparent;
            border-left: 13px solid #1f356e;
        }

        /* White inner arrows */
        .left-chevron,
        .right-chevron {
            position: absolute;
            top: 50%;
            width: 35px;
            height: 35px;
            border-top: 4px solid #fff;
            border-right: 4px solid #fff;
            transform: translateY(-50%) rotate(225deg);
        }

        .left-chevron {
            left: 8px;
        }

        .right-chevron {
            right: 8px;
            transform: translateY(-50%) rotate(45deg);
        }        
        /* ====== PRINT STYLES ====== */

        @media print {

            @page {
                size: A4 portrait;
                margin: 10mm 10mm 10mm 10mm;
            }

            html,
            body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }

            body {
                padding: 0 !important;
            }
            
            .no-print {
                display: none;
            }

            .form {
                width: 100% !important;
                max-width: 100% !important;
                border: 2px solid #1a2f6e !important;
                box-shadow: none !important;
                padding: 14px 16px 16px !important;
                margin: 0 !important;
                page-break-inside: avoid;
            }

            .school-name {
                white-space: nowrap !important;
                font-size: 28px !important;
                letter-spacing: 1px !important;
            }            

            .place-badge {
                background: #1a2f6e !important;
                color: #fff !important;
            }

            .place-badge span {
                background: #1a2f6e !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: exact;
            }            

            .id-badge {
                background: #1a2f6e !important;
                color: #fff !important;
            }

            .sign-box,
            .photo,
            .id-photo {
                background: #f7f8fc !important;
            }

            .logo {
                background: #f0f3fb !important;
            }

            .print-page-break {
                page-break-before: always;
                break-before: page;
            }

            .id-ribbon {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .name-block .row {
                display: flex !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
            }

            .name-block .row .label {
                width: 80px !important;
                min-width: 80px !important;
                flex-shrink: 0 !important;
            }

            .name-block .boxes {
                flex: 1 !important;
                overflow: hidden !important;
            }

            .name-block .box {
                width: 21px !important;
                min-width: 21px !important;
            }

            .fs-10{
                font-size: 10px;
            }

        }
    </style>
</head>

<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg px-5">Print Form</button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg ml-2">Close</button>
    </div>    

    <div class="form" style="border-bottom: 2px dashed #999;">

        <!-- ===== HEADER ===== -->

        <div class="header">
            <div class="header-top">
                <div class="left-head">
                    <div class="udise">UDISE CODE-09561704503</div>
                    <div class="logo">
                        @if(empty($student->school->logo_url))
                            AL-HILAL<br>PUBLIC SCHOOL<br>BAJAHRA                            
                        @else
                            <img src="{{ $student->school->logo_url ?? '' }}" alt="Logo" class="school-logo" />
                        @endif

                    </div>
                </div>
    
                <div class="center-head">
                    <div class="admission-label">Admission Form</div>
                    <div class="school-name">{{ $student->school->name ?? '' }}</div>
                    <div class="place-badge"><span>{{ $student->school->address ?? '' }}</span></div>
                    <div class="medium-badge"><span>English Medium</span></div>
                </div>
    
                <div class="right-head">
                    <div class="phone">&#9742; 9670442055</div>
                </div>
            </div>
            <div class="header-bottom">
                <div class="session-row">
                    <span class="label">Sr.</span>
                    <div class="sr-line">{{ $sr_no }}</div>
                    <div style="flex:1"></div>
                    <span class="label">
                        Session&nbsp;
                        20<span style="display:inline-block;border-bottom:1px dotted #000;width:20px;text-align:center;">
                            {{ substr($student_session_year_from ?? '', -2) }}
                        </span>
                        &ndash;
                        20<span style="display:inline-block;border-bottom:1px dotted #000;width:20px;text-align:center;">
                            {{ substr($student_session_year_to ?? '', -2) }}
                        </span>
                    </span>
                    <div style="flex:1"></div>
                    <span class="label">Date:</span>
                    <div class="boxes">
                        <div class="box">{{ substr($admission_date_day, 0, 1) }}</div>
                        <div class="box br-1">{{ substr($admission_date_day, 1, 1) }}</div>

                        <div class="sep"></div>

                        <div class="box">{{ substr($admission_date_month, 0, 1) }}</div>
                        <div class="box br-1">{{ substr($admission_date_month, 1, 1) }}</div>

                        <div class="sep"></div>

                        <div class="box">{{ substr($admission_date_year, 0, 1) }}</div>
                        <div class="box">{{ substr($admission_date_year, 1, 1) }}</div>
                        <div class="box">{{ substr($admission_date_year, 2, 1) }}</div>
                        <div class="box br-1">{{ substr($admission_date_year, 3, 1) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PEN / AADHAAR -->

        <div class="row">
            <span class="label">PEN No.</span>
            <div class="boxes">
                @php
                    $boxCount = !empty($academic_pen) ? strlen((string)$academic_pen) : 12;
                @endphp

                @for($i = 0; $i < $boxCount; $i++)
                    <div class="box {{ $i == $boxCount - 1 ? 'br-1' : '' }}">
                        {{ substr((string)$academic_pen, $i, 1) }}
                    </div>
                @endfor
            </div>
            <div style="flex:1"></div>
            <span class="label">Apaar ID</span>
            <div class="boxes">
                @php
                    $boxCount = !empty($academic_apaar_id) ? strlen((string)$academic_apaar_id) : 12;
                @endphp

                @for($i = 0; $i < $boxCount; $i++)
                    <div class="box {{ $i == $boxCount - 1 ? 'br-1' : '' }}">
                        {{ substr((string)$academic_apaar_id, $i, 1) }}
                    </div>
                @endfor
            </div>
        </div>

        <!-- ===== MAIN ===== -->

        <div class="main">

            <div class="left">

                <!-- 1. STUDENT -->
                <div class="section-title">1. &nbsp;Student's Name</div>
                <div class="name-block">
                    <div class="row">
                        <span class="label">In Hindi &nbsp;</span>
                        <div class="long-line">{{ $student_name_hindi }}</div>
                    </div>
                    <div class="row">
                        <span class="label">In English</span>
                        <div class="boxes">
                            @for($i = 0; $i < 25; $i++)
                                <div class="box {{ $i == 24 ? 'br-1' : '' }}">
                                    {{ substr((string)($student_name ?? ''), $i, 1) }}
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="row">
                    <span class="label">Aadhaar No.</span>
                    <div class="boxes">
                        @php
                            $boxCount = !empty($student_aadhar) ? strlen((string)$student_aadhar) : 12;
                        @endphp

                        @for($i = 0; $i < $boxCount; $i++)
                            <div class="box {{ in_array($i, [3, 7, $boxCount - 1]) ? 'br-1' : '' }}">
                                {{ substr((string)($student_aadhar ?? ''), $i, 1) }}
                            </div>

                            @if(in_array($i, [3, 7]))
                                <div class="sep"></div>
                            @endif
                        @endfor
                    </div>
                </div>

                <!-- 2. FATHER -->
                <div class="section-title">2. &nbsp;Father's Name</div>
                <div class="name-block">
                    <div class="row">
                        <span class="label">In Hindi &nbsp;</span>
                        <div class="long-line">{{ $parents_fathers_name_hindi }}</div>
                    </div>
                    <div class="row">
                        <span class="label">In English</span>
                        <div class="boxes">
                            @for($i = 0; $i < 25; $i++)
                                <div class="box {{ $i == 24 ? 'br-1' : '' }}">
                                    {{ substr((string)($parents_fathers_name ?? ''), $i, 1) }}
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="row">
                    <span class="label">Aadhaar No.</span>
                    <div class="boxes">
                        @php
                            $boxCount = !empty($parents_fathers_aadhar) ? strlen((string)$parents_fathers_aadhar) : 12;
                        @endphp

                        @for($i = 0; $i < $boxCount; $i++)
                            <div class="box {{ in_array($i, [3, 7, $boxCount - 1]) ? 'br-1' : '' }}">
                                {{ substr((string)($parents_fathers_aadhar ?? ''), $i, 1) }}
                            </div>

                            @if(in_array($i, [3, 7]))
                                <div class="sep"></div>
                            @endif
                        @endfor
                    </div>
                </div>

                <!-- 3. MOTHER -->
                <div class="section-title">3. &nbsp;Mother's Name</div>
                <div class="name-block">
                    <div class="row">
                        <span class="label">In Hindi &nbsp;</span>
                        <div class="long-line">{{ $parents_mothers_name_hindi }}</div>
                    </div>
                    <div class="row">
                        <span class="label">In English</span>
                        <div class="boxes">
                            @for($i = 0; $i < 25; $i++)
                                <div class="box {{ $i == 24 ? 'br-1' : '' }}">
                                    {{ substr((string)($parents_mothers_name ?? ''), $i, 1) }}
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="row">
                    <span class="label">Aadhaar No.</span>
                    <div class="boxes">
                        @php
                            $boxCount = !empty($parents_mothers_aadhar) ? strlen((string)$parents_mothers_aadhar) : 12;
                        @endphp

                        @for($i = 0; $i < $boxCount; $i++)
                            <div class="box {{ in_array($i, [3, 7, $boxCount - 1]) ? 'br-1' : '' }}">
                                {{ substr((string)($parents_mothers_aadhar ?? ''), $i, 1) }}
                            </div>

                            @if(in_array($i, [3, 7]))
                                <div class="sep"></div>
                            @endif
                        @endfor
                    </div>
                </div>

            </div><!-- /.left -->

            <div class="photo">
                @if(!empty($doc_student_photo))
                    <img src="{{ asset('/storage/' . $doc_student_photo) }}"
                        alt="Student Photo"
                        style="width:100%; height:100%; object-fit:cover;">
                @else
                    Affix a Passport Size Photo
                @endif
            </div>
        </div><!-- /.main -->

        <!-- ===== DETAILS ===== -->

        <div class="row">
            <span class="label">5. &nbsp;Guardian's Name &amp; Relation with Student</span>
            <div class="dotted">{{ $guardian_name_relation }}</div>
        </div>

        <div class="row">
            <span class="label">6. &nbsp;Address</span>
            <div class="dotted">{{ $parents_address }}</div>
        </div>

        <div class="row">
            <span class="label">7. &nbsp;Date of Birth (in digits)</span>
            <div class="boxes">
                <div class="box">{{ substr($student_dob_day, 0, 1) }}</div>
                <div class="box br-1">{{ substr($student_dob_day, 1, 1) }}</div>
                <div class="sep"></div>
                <div class="box">{{ substr($student_dob_month, 0, 1) }}</div>
                <div class="box br-1">{{ substr($student_dob_month, 1, 1) }}</div>
                <div class="sep"></div>
                <div class="box">{{ substr($student_dob_year, 0, 1) }}</div>
                <div class="box">{{ substr($student_dob_year, 1, 1) }}</div>
                <div class="box">{{ substr($student_dob_year, 2, 1) }}</div>
                <div class="box br-1">{{ substr($student_dob_year, 3, 1) }}</div>
            </div>
            <span class="label">in words</span>
            <div class="dotted fs-10">{{ $dob_in_words }}</div>
        </div>

        <div class="row">
            <span class="label">8. &nbsp;Nationality</span>
            <div class="dotted">{{ $student_nationality }}</div>
            <span class="label">Religion &amp; Caste</span>
            <div class="dotted">{{ $student_religion_caste }}</div>
        </div>

        <div class="row">
            <span class="label">9. &nbsp;Name of Last School Attended</span>
            <div class="dotted">{{ $academic_last_school }}</div>
        </div>

        <div class="row">
            <span class="label">10. Class in which admission is sought</span>
            <div class="dotted">{{ $grade_name }}</div>
            <span class="label">Section</span>
            <div class="dotted">{{ $section_name }}</div>
        </div>

        <div class="row">
            <span class="label">12. &nbsp;Mobile No.&nbsp;</span>
            <span class="label">(A)</span>
            <div class="boxes">
                @for($i = 0; $i < 10; $i++)
                    <div class="box {{ $i == 9 ? 'br-1' : '' }}">
                        {{ substr((string)($parents_mobile_no_a ?? ''), $i, 1) }}
                    </div>
                @endfor
            </div>
            <span class="label">(B)</span>
            <div class="boxes">
                @for($i = 0; $i < 10; $i++)
                    <div class="box {{ $i == 9 ? 'br-1' : '' }}">
                        {{ substr((string)($parents_mobile_no_b ?? ''), $i, 1) }}
                    </div>
                @endfor
            </div>
        </div>

        <!-- ===== SIGNATURES ===== -->

        <div class="sign-section">
            <div class="sign">
                <div class="sign-box"></div>
                <div class="sign-title">Student's Signature</div>
            </div>
            <div class="sign">
                <div class="sign-box"></div>
                <div class="sign-title">Guardian's Signature</div>
            </div>
            <div class="sign">
                <div class="sign-box"></div>
                <div class="sign-title">Principal's Signature</div>
            </div>
        </div>

        <!-- ===== NOTE ===== -->

        <div class="note">
            <strong>Note:</strong> &nbsp;Please attach a photocopy of the student's Aadhaar Card and Transfer
            Certificate (T.C.).
            Bring the original copies of all attachments at the time of admission.
        </div>

    </div>

    <div class="print-page-break"></div>

    <div class="form" style="border-top: 2px dashed #999;">
        <!-- ===== ID CARD SECTION ===== -->

        <div class="id-card">

            <div class="id-title">
                {{-- <span class="id-badge">For Student ID Card</span> --}}
                <div class="id-ribbon">
                    <span class="left-chevron"></span>
                    For student ID Card
                    <span class="right-chevron"></span>
                </div>
            </div>

            <div class="id-content">

                <div class="id-left">
                    <div class="id-row">
                        Student's Name <div class="id-line">{{ $student_name }}</div>
                    </div>
                    <div class="id-row">
                        Father's Name <div class="id-line">{{ $parents_fathers_name }}</div>
                    </div>
                    <div class="id-row">
                        Class <div class="id-line">{{ $grade_name }}</div>
                        &nbsp;&nbsp;Section <div class="id-line">{{ $section_name }}</div>
                        &nbsp;&nbsp;D.O.B. <div class="id-line">{{ $student_dob }}</div>
                    </div>
                    <div class="id-row">
                        Address <div class="id-line">{{ $parents_address }}</div>
                    </div>
                    <div class="id-row" style="margin-bottom:5;">
                        Mob. No. <div class="id-line">{{ $parents_mobile_no_a }}</div>
                    </div>
                </div>

                <div class="id-right">
                    <div class="id-row" style=" justify-content: end; ">
                        <span class="label">Sr.</span>
                        <div class="id-sr">{{ $sr_no }}</div>
                    </div>
                    <div class="id-photo">
                        @if(!empty($doc_student_photo))
                            <img src="{{ asset('/storage/' . $doc_student_photo) }}"
                                alt="Student Photo"
                                style="width:100%; height:100%; object-fit:cover;">
                        @else
                            Affix a Passport Size Photo
                        @endif
                    </div>
                </div>

            </div>

        </div>

    </div>

</body>

</html>