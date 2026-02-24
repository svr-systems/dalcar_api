@extends('email.scaffold.Main')

@section('content')
  <div>
    <h2 class="font-weight-light">Facturas pendientes por cargar</h2>

    <p class="text">
      Al día <b>{{ $data->date }}</b> se detectaron facturas calendarizadas sin evidencia digital.
    </p>

    <p class="text" style="margin-top: 14px;">
      Total pendientes: <b>{{ count($data->items) }}</b>
    </p>

    <ul class="text" style="margin-top: 8px;">
      @foreach ($data->items as $item)
        <li style="margin-bottom: 6px;">
          Vehículo: <b>{{ $item->vehicle_uiid }}</b>
          — Tipo: <b>{{ $item->invoice_type_name }}</b>
          — Debió cargarse: <b>{{ $item->scheduled_date_fmt ?? $item->scheduled_date }}</b>
          — Vencido: <b>{{ (int) ($item->overdue_days ?? 0) }}</b> día(s)
        </li>
      @endforeach
    </ul>

    <p class="text" style="margin-top: 18px;">
      Este mensaje es informativo y forma parte de las notificaciones automáticas del sistema.
    </p>
  </div>
@endsection