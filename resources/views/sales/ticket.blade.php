<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket_{{ $sale->invoice_number }}</title>
    <style>
        * {
            font-family: 'Courier New', Courier, monospace; /* Fuente tipica de ticketeras para alineación exacta */
            font-size: 12px;
            line-height: 1.2;
        }
        body {
            width: 72mm; /* Ancho seguro estándar para ticketeras de 80mm y 58mm */
            margin: 0;
            padding: 5mm;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .dashed-line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .item-row td {
            padding: 3px 0;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <div class="text-center">
        <span class="bold" style="font-size: 14px;">INVERSIONES CJ JIREH MARKET</span><br>
        <span>Tu Market de confianza</span><br>
        <span style="font-size: 10px;">RIF: J-504543140</span><br>
        <span style="font-size: 10px;">Telf: 0412-8609510</span><br>
    </div>

    <div class="dashed-line"></div>

    <div>
        <span><b>FACTURA:</b> #{{ $sale->invoice_number }}</span><br>
        <span><b>FECHA:</b> {{ $sale->created_at->format('d/m/Y h:i A') }}</span><br>
        <span><b>CLIENTE:</b> {{ $sale->client_name }}</span><br>
        <span><b>CÉDULA:</b> {{ $sale->client_id_number ?? 'Sin registro' }}</span><br>
    </div>

    <div class="dashed-line"></div>

    <table>
        <thead>
            <tr class="bold">
                <th>DESCRIPCIÓN</th>
                <th class="text-center">CANT</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr class="item-row">
                    <td>{{ substr($item->product->name, 0, 18) }}</td>
                    <td class="text-center">
                        {{ $item->unit_type === 'KG' ? number_format($item->quantity, 3) : number_format($item->quantity, 0) }}
                    </td>
                    <td class="text-right">Bs.{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="dashed-line"></div>

    <table>
        <tr class="bold" style="font-size: 13px;">
            <td>TOTAL PAGADO:</td>
            <td class="text-right">Bs.{{ number_format($sale->total, 2) }}</td>
        </tr>
    </table>

    <div class="dashed-line"></div>

    <div>
        <span class="bold" style="font-size: 10px;">FORMAS DE PAGO:</span><br>
        @foreach($sale->payments as $payment)
            <div style="display: flex; justify-content: space-between; font-size: 11px;">
                <span>• {{ $payment->payment_method }}:</span>
                <span>Bs.{{ number_format($payment->amount, 2) }}</span>
            </div>
        @endforeach
    </div>

    <div class="dashed-line"></div>

    <div class="text-center" style="margin-top: 10px; font-size: 10px;">
        <span>¡Gracias por su compra!</span><br>
        <span>Vuelva pronto.</span>
    </div>

</body>
</html>