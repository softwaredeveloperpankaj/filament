@php
    if(isset($student->created_at)) {
        $admission_date_day = date('d', strtotime($student->created_at));
        $admission_date_month = date('m', strtotime($student->created_at));
        $admission_date_year = date('Y', strtotime($student->created_at));
    } else {
        $admission_date_day = '';
        $admission_date_month = '';
        $admission_date_year = '';
    }

    $grade_name = $student->class->name ?? '';

    if(isset($student->section)) {
        $section_name = $student->section->name;
    } else {
        $section_name = '';
    }

    $student_session_year_from = explode('-', $student->academic_year)[0] ?? '';
    $student_session_year_to = explode('-', $student->academic_year)[1] ?? '';

    // Student Information
    $student_name = $student->form_data['student_name'] ?? '';

    $student_dob = $student->form_data['student_dob'] ?? '';
    $student_dob_day = $student_dob ? date('d', strtotime($student_dob)) : '';
    $student_dob_month = $student_dob ? date('F', strtotime($student_dob)) : '';
    $student_dob_year = $student_dob ? date('Y', strtotime($student_dob)) : '';

    $student_gender = $student->form_data['student_gender'] ?? '';
    $student_aadhar = $student->form_data['student_aadhar'] ?? '';
    $student_category = $student->form_data['student_cast'] ?? '';

    // Academic Information
    $academic_last_class = $student->form_data['academic_last_class'] ?? '';
    $academic_last_school = $student->form_data['academic_last_school'] ?? '';
    $academic_percentage = $student->form_data['academic_percentage'] ?? '';
    $academic_pen = $student->form_data['academic_pen'] ?? '';
    $academic_apaar_id = $student->form_data['academic_apaar_id'] ?? '';

    // Parent Information
    $parents_fathers_name = $student->form_data['parents_fathers_name'] ?? '';
    $parents_fathers_occupation = $student->form_data['parents_fathers_occupation'] ?? '';
    $parents_mothers_name = $student->form_data['parents_mothers_name'] ?? '';
    $parents_fathers_mobile_no = $student->form_data['parents_fathers_mobile_no'] ?? '';
    $parents_address_village = $student->form_data['parents_address_village'] ?? '';
    $parents_address_post_office = $student->form_data['parents_address_post_office'] ?? '';
    $parents_address_district = $student->form_data['parents_address_district'] ?? '';
    $parents_address_state = $student->form_data['parents_address_state'] ?? '';
    $parents_address_pin_code = $student->form_data['parents_address_pin_code'] ?? '';

    // Documents
    $student_photo = $student->form_data['student_photo'] ?? '';\
    
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $student_name ? 'Admission Form - ' . $student_name : 'Blank Admission Form' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rethink+Sans:ital,wght@0,400..800;1,400..800&family=Oswald:wght@200..700&family=Archivo+Black&family=Arimo:ital,wght@0,400..700;1,400..700&family=Mogra&display=swap" rel="stylesheet">
       

    <style>
        @media print {
            .no-print {
                display: none;
            }
            html, body {
                width: 210mm;
                height: 297mm;
                font-family: 'Rethink Sans', sans-serif;
                background: #fff;
            }

            .form-container {
                width: 100%;
                margin: 0 auto;
                border: none !important;
                box-shadow: none !important;
            }
        }
        .btn {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        .btn-primary { background: #0d6efd; color: #fff; }
        .btn-secondary { background: #6c757d; color: #fff; }

        .form-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 50px;
            border: 1px solid #dee2e6;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-radius: 8px;
        }        

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        td,
        .form-label {
            font-family: 'Rethink Sans', sans-serif;
        }

        .font-style-italic{
            font-family: 'Rethink Sans', sans-serif;
            font-weight: 400;
            font-size: 20px;
            font-style: italic;
        }
        
        .school-logo {
            object-fit: cover;
            height: 120px;
            width: 120px;
        }

        .school-name {
            font-family: 'Oswald', 'sans-serif';
            font-weight: 700;
            font-size: 35px;
        }

        .school-address {
            font-size: 20px;
            font-weight: bold;
            font-style: italic;
        }

        .photo-box {
            object-fit: cover;
            margin-left: auto;
            width: 120px;
            height: 140px;
            border: 2px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            text-align: center;
            overflow: hidden;
        }

        .photo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }        

        .square-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border: 1px solid black;
            font-size: 18px;
            line-height: 1;
            box-sizing: border-box;
            vertical-align: middle;
            position: relative;
            bottom: 4px;            
        }
        .title-heading {
            font-family: 'Mogra', italic;
            margin-top: 20px;
            text-transform: uppercase;
            text-align: center;
            font-size: 40px;
        }
        .rules {
            list-style-type: "❖ ";
            line-height: 2;
            font-size: 18px;
        }

        .line-field {
            display: table;
            width: 100%;
        }

        .line-field .label {
            display: table-cell;
            white-space: nowrap;
        }

        .line-field .dotted-bottom-line {
            display: table-cell;
            border-bottom: 2px dotted black;
            width: 100%;
            vertical-align: bottom;
        }

        .dotted-bottom-line .content {
            display: inline-block;
            padding: 0 4px;
            background: #fff;
        }


        .multi-field {
            display: table-cell;
            width: 100%;
            white-space: nowrap;
        }

        .multi-field .dotted {
            display: inline-block;
            border-bottom: 2px dotted black;
            vertical-align: bottom;
            min-width: 40px;
        }

        .multi-field .small {
            min-width: 30px;
        }

        .multi-field .large {
            min-width: 60px;
        }

        .multi-field .separator {
            margin: 0 5px;
        }

        .dotted .content {
            display: inline-block;
            padding: 0 4px;
            background: #fff;
        }

        .signature-field {
            width: 100%;
        }

        .signature-field .line {
            border-bottom: 2px dotted black;
            height: 30px;
            margin-bottom: 5px;
        }

        .signature-field .label {
            font-size: 18px;
        }

        .inline-dotted {
            display: inline-block;
            border-bottom: 2px dotted black;
            min-width: 210px;   /* important: keeps line when empty */
            vertical-align: bottom;
            line-height: 1.5;
        }

        .inline-dotted .text {
            display: inline-block;
            padding: 0 4px;
            background: #fff;
        }

    </style>

</head>

<body>
    <div class="no-print" style="text-align: center;">
        <button onclick="window.print()" class="btn btn-primary btn-lg px-5">Print Form</button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg ml-2">Close</button>
    </div>

    <div class="form-container">

        <table style="width: 100%; text-align: center; border-collapse: collapse;">
            <tr>
                <!-- Logo -->
                <td style="width: 25%; text-align: left;">
                    <img src="{{ $student->school->logo_url ?? '' }}" alt="Logo" class="school-logo" />
                </td>
        
                <!-- School Info -->
                <td style="width: 50%; text-align: center;">
                    <div class="school-name">{{ $student->branch->name ?? '' }}</div>
                    <div class="school-address">
                        {{ $student->school->address ?? '' }}
                    </div>
                </td>
        
                <!-- Student Photo -->
                <td style="width: 25%;">
                    <div class="photo-box">
                        @if(!empty($student_photo))
                            <img src="{{ asset('/storage/' . $student_photo) }}" alt="Affix a Passport Size Photo">
                        @else
                            Affix a Passport Size Photo
                        @endif
                    </div>
                </td>
            </tr>
        </table>
        
        <table style="width: 100%; text-align: center; border-collapse: collapse;">
            <tr>
                <td>
                    <div style="text-align: center; margin: 20px 0 20px 0;">
                        <img src="{{ asset('pdfs/layout1/admission-form.png') }}" alt="" style="width: 50%;">
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <h3 style="margin: 20px 0 40px 0;">
                        Session-&nbsp;20
                        <span style="display: inline-block; border-bottom: 2px dotted black; width: 35px;">{{ substr($student_session_year_from ?? '', -2) }}</span>
                        &nbsp;-&nbsp;20
                        <span style="display: inline-block; border-bottom: 2px dotted black; width: 35px;">{{ substr($student_session_year_to ?? '', -2) }}</span>
                    </h3>
                </td>
            </tr>
        </table>

        <table style="width: 100%; border-collapse: collapse;">
            <tr class="font-style-italic">
                <td colspan="2" style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Applying For Class:</span>
                        
                        <span class="multi-field">
                            <span class="dotted">
                                <span class="content">{{ $grade_name }}</span>
                            </span>
                            <span class="separator"> - </span>
                            <span class="dotted">
                                <span class="content">{{ $section_name }}</span>
                            </span>
                        </span>
                    </div>                    
                </td>
                <td colspan="2" style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Admission Date:</span>
                        
                        <span class="multi-field">
                            <span class="dotted small">
                                <span class="content">{{ $admission_date_day }}</span>
                            </span>
                            <span class="separator"> / </span>
                            
                            <span class="dotted small">
                                <span class="content">{{ $admission_date_month }}</span>
                            </span>
                            <span class="separator"> / </span>
                            
                            <span class="dotted large">
                                <span class="content">{{ $admission_date_year }}</span>
                            </span>
                        </span>
                    </div>                    
                </td>
            </tr>
            <tr class="font-style-italic">
                <td colspan="4" style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Student's Name<sub>(As Per Aadhar)</sub>:</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $student_name }}</span>
                        </span>
                    </div>
                </td>
            </tr>
            <tr class="font-style-italic">
                <td style="padding-bottom:10px">Date of Birth:</td>            
                <td style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Day</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $student_dob_day }}</span>
                        </span>
                    </div>
                </td>
            
                <td style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Month</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $student_dob_month }}</span>
                        </span>
                    </div>
                </td>
            
                <td style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Year</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $student_dob_year }}</span>
                        </span>
                    </div>
                </td>
            </tr>

            <tr class="font-style-italic">
                <td style="padding-bottom:10px" colspan="1">
                    GENDER:&nbsp;Male&nbsp;
                    <span class="square-box">{!! (strtolower($student_gender) === 'male') ? '&#10003;' : '' !!}</span>
                    &nbsp;Female&nbsp;
                    <span class="square-box">{!! (strtolower($student_gender) === 'female') ? '&#10003;' : '' !!}</span>
                </td>
                <td style="padding-bottom:10px" colspan="3">
                    Aadhar:
                    @php
                        $aadhar = array_pad(str_split((string) ($student_aadhar)), 12, '');
                    @endphp
                    @foreach($aadhar as $digit)
                        <span class="square-box">{{ $digit }}</span>
                    @endforeach
                </td>
            </tr>
            <tr class="font-style-italic">
                <td colspan="1" style="padding-bottom:10px">CATEGORY:</td>
                <td colspan="3" style="padding-bottom:10px">
                    <span>GEN<span style="margin-left: 5px;" class="square-box">{!! (strtolower($student_category) === 'gen') ? '&#10003;' : '' !!}</span></span>
                    <span>OBC<span style="margin-left: 5px;" class="square-box">{!! (strtolower($student_category) === 'obc') ? '&#10003;' : '' !!}</span></span>
                    <span>SC<span style="margin-left: 5px;" class="square-box">{!! (strtolower($student_category) === 'sc') ? '&#10003;' : '' !!}</span></span>
                    <span>ST<span style="margin-left: 5px;" class="square-box">{!! (strtolower($student_category) === 'st') ? '&#10003;' : '' !!}</span></span>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <div class="title-heading">ACADEMIC DETAILS</div>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <table style="width: 100%; border: 1px solid black; border-collapse: collapse;">
                        <thead>
                            <tr style="border: 1px solid black;">
                                <th style="border: 1px solid black; padding: 10px;">Last Class</th>
                                <th style="border: 1px solid black; padding: 10px;">Last School Attended</th>
                                <th style="border: 1px solid black; padding: 10px;">Percentage</th>
                                <th style="border: 1px solid black; padding: 10px;">Std. P.E.N.</th>
                                <th style="border: 1px solid black; padding: 10px;">Std. Apaar ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border: 1px solid black;">
                                @if($academic_last_class)
                                <td style="border: 1px solid black; padding: 10px;">{{ $academic_last_class }}</td>
                                @else
                                <td style="border: 1px solid black; padding: 10px; height: 20px;"></td>
                                @endif
                                <td style="border: 1px solid black; padding: 10px;">{{ $academic_last_school }}</td>
                                <td style="border: 1px solid black; padding: 10px;">{{ $academic_percentage }}</td>
                                <td style="border: 1px solid black; padding: 10px;">{{ $academic_pen }}</td>
                                <td style="border: 1px solid black; padding: 10px;">{{ $academic_apaar_id }}</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <div class="title-heading">Parents / Guardian's Details</div>
                </td>
            </tr>
            <tr class="font-style-italic">
                <td colspan="4" style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Father's Name<sub>(As Per Aadhar)</sub>:</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $parents_fathers_name }}</span>
                        </span>
                    </div>
                </td>
            </tr>
            <tr class="font-style-italic">
                <td colspan="4" style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Mother's Name<sub>(As Per Aadhar)</sub>:</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $parents_mothers_name }}</span>
                        </span>
                    </div>
                </td>
            </tr>
            <tr class="font-style-italic">
                <td colspan="2" style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Occupation:</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $parents_fathers_occupation }}</span>
                        </span>
                    </div>
                </td>
                <td colspan="2" style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Mob.No:</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $parents_fathers_mobile_no }}</span>
                        </span>
                    </div>
                </td>
            </tr>

            <tr class="font-style-italic">
                <td colspan="2" style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Address:Village</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $parents_address_village }}</span>
                        </span>
                    </div>
                </td>
                <td colspan="2" style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">P.O:</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $parents_address_post_office }}</span>
                        </span>
                    </div>
                </td>
            </tr>
            <tr class="font-style-italic">
                <td colspan="1" style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Distt:</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $parents_address_district ?? '' }}</span>
                        </span>
                    </div>
                </td>
                <td colspan="2" style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">State:</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $parents_address_state ?? '' }}</span>
                        </span>
                    </div>
                </td>
                <td colspan="1" style="padding-bottom:10px">
                    <div class="line-field">
                        <span class="label">Pin Code:</span>
                        <span class="dotted-bottom-line">
                            <span class="content">{{ $parents_address_pin_code ?? '' }}</span>
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <div class="title-heading">Documents Attached</div>
                </td>
            </tr>
            <tr class="font-style-italic">
                <td>
                    <ol>
                        <li>Last Class Mark sheet.</li>
                        <li>T.C.</li>
                        <li>Student Aadhar / Birth Certificate.</li>
                        <li>Father's Aadhar.</li>
                        <li>Mother's Aadhar.</li>
                        <li>Passport Size Photo.</li>
                    </ol>    
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <div class="title-heading">Rules & Regulations</div>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <ul class="rules">
                        <li>समस्त छात्र/छात्रा निर्धारित समय (माह का प्रथम सप्ताह) में फीस (शुल्क) जमा करना है।</li>
                        <li>विलम्ब (Late) होने की दशा में विद्यालय द्वारा निर्धारित विलम्ब शुल्क (Late Fee) जमा करना होगा।</li>
                        <li>लगातार दो माह शुल्क न जमा होने की स्थिति में छात्र-छात्रा को विद्यालय में प्रवेश (Entry) नही दिया जायेगा।</li>
                        <li>वाहन से आने वाले छात्र-छात्रा का शुल्क हर माह समय पर जमा करना अनिवार्य है। न जमा होने की स्थिति में छात्र-छात्रा को वाहन में बैठने नही दिया जाएगा।</li>
                        <li>छात्रावास (Hostel) में रहने वाले छात्र-छात्रा का शुल्क निर्धारित समय पर जमा करना होगा।</li>
                        <li>विद्यालय में प्रवेश लेने के बाद किसी कारणवश न पढ़ने पर जमा हुई फीस व किताब वापस नहीं होगा।</li>
                        <li>छात्रावास (Hostel) में रहने वाली छात्राओं के अभिभावक (Guardian) बिना कार्ड के मिलने की अनुमति नहीं होगी।</li>
                        <li>विद्यालय में किसी भी छात्र-छात्रा द्वारा किया गया नुक़सान अभिभावक द्वारा भरा जाएगा।</li>
                        <li>अनुशासन हीनता होने पर बिना किसी पूर्व सूचना के छात्र-छात्रा को विद्यालय से निष्कासित कर दिया जाएगा।</li>
                        <li>विद्यालय के किसी भी प्रकरण (लड़ाई/झगडा आदि) होने पर किसी भी प्रकार की विधिक कार्यवाही अभिभावक द्वारा नहीं की जाएगी।</li>
                        <li>विद्यालय में बिना यूनीफार्म के प्रवेश वर्जित है।</li>
                        <li>हॉस्टल मे रहने वाले छात्र-छात्रा हॉस्टल के नियमों का पालन करेंगें।</li>
                        <li>वाहन से आने वाले छात्र-छात्रा वाहन के नियमों का पालन करेंगें।</li>
                    </ul>                    
                </td>
            </tr>

            <tr>
                <td colspan="4" style="text-align: center; font-weight: bold; font-size: 24px;">
                    <u>शपथ-पत्र</u>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="line-height: 40px; font-size: 18px;">
                    मैं,
                    <span class="inline-dotted">
                        <span class="text">{{ $parents_fathers_name }}</span>
                    </span>
                    अपने पुत्र/पुत्री
                    <span class="inline-dotted">
                        <span class="text">{{ $student_name}}</span>
                    </span>
                    का प्रवेश कक्षा
                    <span class="inline-dotted">
                        <span class="text">{{ $grade_name }} - {{ $section_name }}</span>
                    </span>
                    में करा रहा/रही हूँ। मै उक्त दिए गए नियमों को ध्यान पूर्वक पढ़कर उसका पालन करते हेतु अपने बच्चे का प्रवेश करा रहा/रही हूँ। भविष्य में मैं कोई विधिक कार्यवाही किसी तरह से विद्यालय पर नही करूँगा/करूँगी।
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="signature-field">
                        <div class="label">प्रधानाचार्य हस्ताक्षर</div>
                        <div style="width: 95%;" class="line"></div>
                    </div>
                </td>
            
                <td colspan="2">
                    <div class="signature-field" style="text-align: right;">
                        <div class="label">अभिभावक का हस्ताक्षर/अंगूठा</div>
                        <div class="line"></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>