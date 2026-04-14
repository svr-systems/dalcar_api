<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Recibo de pago</title>
  <style>
    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      color: #222;
      margin: 30px;
    }

    .logo {
      margin-bottom: 18px;
    }

    .header {
      border-bottom: 2px solid #444;
      padding-bottom: 12px;
      margin-bottom: 20px;
    }

    .title {
      font-size: 22px;
      font-weight: bold;
      margin: 0;
    }

    .subtitle {
      font-size: 12px;
      color: #666;
      margin-top: 4px;
    }

    .section {
      margin-top: 18px;
    }

    .section-title {
      font-size: 14px;
      font-weight: bold;
      border-bottom: 1px solid #ccc;
      padding-bottom: 4px;
      margin-bottom: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    td {
      padding: 6px 4px;
      vertical-align: top;
    }

    .label {
      width: 180px;
      font-weight: bold;
      color: #444;
    }

    .amount {
      font-size: 18px;
      font-weight: bold;
      color: #0b7a3b;
    }

    .footer {
      margin-top: 30px;
      font-size: 11px;
      color: #666;
      border-top: 1px solid #ddd;
      padding-top: 10px;
    }
  </style>
</head>

<body>
  @if(file_exists(storage_path('app/public') . '/logo.png'))
    <div class="logo">
      <img src="data:image/png;base64,{{ base64_encode(file_get_contents(storage_path('app/public') . '/logo.png')) }}"
        alt="Logo" style="height: 80px;">
    </div>
  @endif

  <div class="header">
    <p class="title">Recibo de pago</p>
    <p class="subtitle">
      Folio: {{ $data->reservation_payment?->uiid ?? 'N/D' }}
    </p>
  </div>

  <div class="section">
    <div class="section-title">Cliente</div>
    <table>
      <tr>
        <td class="label">Nombre</td>
        <td>{{ $data->customer?->full_name ?? 'N/D' }}</td>
      </tr>
      <tr>
        <td class="label">Correo</td>
        <td>{{ $data->customer?->email ?? 'N/D' }}</td>
      </tr>
      <tr>
        <td class="label">Teléfono</td>
        <td>{{ $data->customer?->phone ?? 'N/D' }}</td>
      </tr>
      <tr>
        <td class="label">RFC</td>
        <td>{{ $data->customer?->rfc ?? 'N/D' }}</td>
      </tr>
    </table>
  </div>

  <div class="section">
    <div class="section-title">Vehículo</div>
    <table>
      <tr>
        <td class="label">ID</td>
        <td>{{ $data->vehicle?->uiid ?? 'N/D' }}</td>
      </tr>
      <tr>
        <td class="label">Marca</td>
        <td>{{ $data->vehicle?->vehicle_version?->vehicle_model?->vehicle_brand?->name ?? 'N/D' }}</td>
      </tr>
      <tr>
        <td class="label">Modelo</td>
        <td>{{ $data->vehicle?->vehicle_version?->vehicle_model?->name ?? 'N/D' }}</td>
      </tr>
      <tr>
        <td class="label">Año</td>
        <td>{{ $data->vehicle?->vehicle_version?->model_year ?? 'N/D' }}</td>
      </tr>
      <tr>
        <td class="label">Versión</td>
        <td>{{ $data->vehicle?->vehicle_version?->name ?? 'N/D' }}</td>
      </tr>
    </table>
  </div>

  <div class="section">
    <div class="section-title">Pago registrado</div>
    <table>
      <tr>
        <td class="label">Tipo de pago</td>
        <td>{{ $data->reservation_payment?->sale_payment_type?->name ?? 'RESERVACION' }}</td>
      </tr>
      <tr>
        <td class="label">Método de pago</td>
        <td>{{ $data->reservation_payment?->payment_method?->name ?? 'N/D' }}</td>
      </tr>
      <tr>
        <td class="label">Fecha</td>
        <td>{{ $data->reservation_payment?->created_at ?? 'N/D' }}</td>
      </tr>
      <tr>
        <td class="label">Monto</td>
        <td class="amount">
          ${{ number_format((float) ($data->reservation_payment?->amount ?? 0), 2) }}
        </td>
      </tr>
    </table>
  </div>

  @if(!empty($data->reservation_payment?->notes))
    <div class="section">
      <div class="section-title">Observaciones</div>
      <p>{{ $data->reservation_payment->notes }}</p>
    </div>
  @endif

  <div class="footer">
    El presente recibo hace constar únicamente que un pago fue registrado en el sistema interno de la empresa como parte
    del proceso comercial correspondiente. Este documento se emite para fines administrativos y de seguimiento, por lo
    que no sustituye factura, CFDI, contrato definitivo, carta responsiva ni autoriza por sí mismo la entrega del
    vehículo. Cualquier alteración, reproducción parcial o total, o uso indebido de este documento fuera de su finalidad
    original será responsabilidad exclusiva de quien lo realice.
  </div>
</body>

</html>