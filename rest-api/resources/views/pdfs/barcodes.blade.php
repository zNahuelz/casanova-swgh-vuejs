<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $medicine->barcode }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 10mm;
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 3mm;
        }

        .barcode-label {
            display: block;
            margin-top: 2mm;
            font-weight: bold;
            font-size: 10pt;
        }
    </style>
</head>

<body>
    <table>
        @for ($row = 0; $row < 10; $row++)
            <tr>
            <td>
                {!! DNS1D::getBarcodeHTML($medicine->barcode, 'C128', 2.5, 50) !!}
                <span class="barcode-label">{{ $medicine->barcode }}</span>
            </td>
            <td>
                {!! DNS1D::getBarcodeHTML($medicine->barcode, 'C128', 2.5, 50) !!}
                <span class="barcode-label">{{ $medicine->barcode }}</span>
            </td>
            </tr>
            @endfor
    </table>


</body>

</html>