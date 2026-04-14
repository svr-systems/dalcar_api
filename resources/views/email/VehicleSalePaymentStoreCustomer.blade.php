@extends('email.scaffold.Main')

@section('content')
  <div>
    <h2 class="font-weight-light">Pago de Apartado Recibido</h2>

    <p class="text">
      Hola <b>{{ $data->customer?->full_name ?? 'Cliente' }}</b>,
      <br><br>
      Te confirmamos que hemos recibido correctamente el pago de apartado del auto
      <b>{{ $data->vehicle?->uiid ?? 'N/D' }}</b>.
    </p>

    <p class="text">
      <b>Monto recibido:</b>
      ${{ number_format((float) ($data->reservation_payment?->amount ?? 0), 2) }}
      <br>
      <b>Método de pago:</b>
      {{ $data->reservation_payment?->payment_method?->name ?? 'N/D' }}
      <br>
      <b>Fecha de registro:</b>
      {{ $data->reservation_payment?->created_at ?? 'N/D' }}
    </p>

    <p class="text" style="margin-top:20px;">
      Tu asesor continuará con el proceso correspondiente para dar seguimiento a la operación.
      En caso de requerir información adicional, se comunicarán contigo por los medios registrados.
    </p>
  </div>
@endsection