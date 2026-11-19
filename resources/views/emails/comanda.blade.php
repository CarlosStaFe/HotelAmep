<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Detalle de su consumo</title>
</head>
<body>
    <p>Hola {{ $pasajero }},</p>
    <p>Punto de venta: {{ $punto }}<br>Fecha y hora: {{ $fecha }}</p>
    <table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border: 1px solid #b8c5cc;">
        <thead>
            <tr style="background-color: #d9edf7;">
                <th style="border: 1px solid #b8c5cc;">Artículo</th>
                <th style="border: 1px solid #b8c5cc;">Cantidad</th>
                <th style="border: 1px solid #b8c5cc;">Precio</th>
                <th style="border: 1px solid #b8c5cc;">Importe</th>
                <th style="border: 1px solid #b8c5cc;">Moza</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($consumos as $consumo)
                <tr>
                    <td style="border: 1px solid #b8c5cc;">{{ $consumo['articulo'] }}</td>
                    <td style="border: 1px solid #b8c5cc; text-align: right;">{{ $consumo['cantidad'] }}</td>
                    <td style="border: 1px solid #b8c5cc; text-align: right;">{{ $consumo['precio'] }}</td>
                    <td style="border: 1px solid #b8c5cc; text-align: right;">{{ $consumo['total'] }}</td>
                    <td style="border: 1px solid #b8c5cc; text-align: right;">{{ $consumo['moza'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p><strong>Total de sus consumos de esta comanda: {{ $total }}</strong></p>
</body>
</html>
