<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admit Cards - Bulk Print</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body>
    {!! $cards !!}
</body>
</html>