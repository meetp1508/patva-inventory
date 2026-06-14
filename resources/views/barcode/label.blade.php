<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Barcode Labels</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 12mm; }
        .toolbar { margin-bottom: 12mm; }
        .toolbar button { padding: 8px 16px; background: #4f46e5; color: white; border: 0; border-radius: 6px; cursor: pointer; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8mm; }
        .label {
            border: 1px dashed #ccc; padding: 6mm 4mm; text-align: center;
            page-break-inside: avoid;
        }
        .label .name { font-size: 11px; font-weight: 600; margin-bottom: 3mm; min-height: 13mm; }
        .label .price { font-size: 14px; font-weight: 700; margin-bottom: 2mm; }
        .label img { max-width: 100%; height: 18mm; }
        .label .code { font-family: monospace; font-size: 10px; margin-top: 1mm; }

        @media print {
            .toolbar { display: none; }
            body { margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Print Labels</button>
        <a href="#" onclick="history.back();return false;" style="margin-left: 8px; color: #555;">← Back</a>
    </div>

    <div class="grid">
        @foreach ($labels as $label)
            <div class="label">
                <div class="name">{{ $label['name'] }}</div>
                <div class="price">{{ $label['price'] }}</div>
                <img src="{{ $label['png'] }}" alt="{{ $label['code'] }}">
                <div class="code">{{ $label['code'] }}</div>
            </div>
        @endforeach
    </div>
</body>
</html>
