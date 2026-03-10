@extends('email.scaffold.Main')

@section('content')
  <div>
    <h2 class="font-weight-light">
      Apartado {{ $data->is_approved ? 'Aprobado' : 'Rechazado' }}
    </h2>

    <p class="text">
      La solicitud de apartado del auto <b>{{ $data->vehicle?->uiid ?? 'N/D' }}</b>
      ha sido
      <b>{{ $data->is_approved ? 'aprobada' : 'rechazada' }}</b>.
    </p>

    <p class="text">
      <b>Cliente:</b>
      {{ trim(($data->customer_name ?? '') . ' ' . ($data->customer_last_name ?? '')) }}
      <br>
      <b>Monto de apartado:</b>
      ${{ number_format((float) ($data->reservation_amount ?? 0), 2) }}
      <br>
      @if($data->is_approved)
        <b>Vence el:</b> {{ $data->expires_at ?? 'N/D' }}
        <br>
      @endif
      <b>Respuesta:</b> {{ $data->response_note ?: 'Sin comentarios' }}
    </p>

    <p class="text" style="margin-top:20px;">
      Favor de dar seguimiento conforme al estatus registrado en el sistema.
    </p>
  </div>
@endsection