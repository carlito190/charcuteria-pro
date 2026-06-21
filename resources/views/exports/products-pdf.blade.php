<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Precios - CJ Jireh Market</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; color: #334155; font-size: 11px; }
        .header { width: 100%; border-bottom: 2px solid #f59e0b; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #1e293b; text-transform: uppercase; }
        .subtitle { font-size: 10px; color: #64748b; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8fafc; color: #0f172a; text-transform: uppercase; font-size: 9px; padding: 8px; border-bottom: 1px solid #cbd5e1; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .price-bs { color: #1d4ed8; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <table style="border: none; margin: 0;">
            <tr>
                <td style="border: none; padding: 0;">
                    <div class="title">Inversiones CJ Jireh Market c.a.</div>
                    <div class="subtitle">Tu Market de Confianza • San Juan de los Morros</div>
                </td>
                <td style="border: none; padding: 0; text-align: right; vertical-align: top;">
                    <div style="font-size: 10px; font-weight: bold;">LISTA DE PRECIOS</div>
                    <div style="font-size: 9px; color: #64748b;">Generado: {{ $date }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Código</th>
                <th style="width: 45%;">Descripción del Producto</th>
                <th style="width: 15%;">Marca</th>
                <th style="width: 12%; text-align: right;">Ref ($)</th>
                <th style="width: 13%; text-align: right;">Precio (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->barcode ?? 'N/A' }}</td>
                    <td class="font-bold" style="text-transform: uppercase;">{{ $product->name }}</td>
                    <td style="text-transform: uppercase;">{{ $product->brand->name ?? 'Genérico' }}</td>
                    <td class="text-right">${{ number_format($product->selling_price_usd, 2) }}</td>
                    <td class="text-right price-bs">{{ number_format($product->price_bs, 2) }} Bs</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
