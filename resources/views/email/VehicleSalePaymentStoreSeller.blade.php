@extends('email.scaffold.Main')

@section('content')
  <div>
    <h2 class="font-weight-light">Pago de Apartado Registrado</h2>

    <p class="text">
      Se ha registrado correctamente el pago de apartado del auto
      <b>{{ $data->vehicle?->uiid ?? 'N/D' }}</b>.
    </p>

    <p class="text">
      <b>Cliente:</b>
      {{ $data->customer?->full_name ?? 'N/D' }}
      <br>
      <b>Monto recibido:</b>
      ${{ number_format((float) ($data->reservation_payment?->amount ?? 0), 2) }}
      <br>
      <b>Método de pago:</b>
      {{ $data->reservation_payment?->payment_method?->name ?? 'N/D' }}
    </p>

    <p class="text" style="margin-top:20px;">
      Ya puedes continuar con el proceso correspondiente de la venta.
    </p>
  </div>
@endsection