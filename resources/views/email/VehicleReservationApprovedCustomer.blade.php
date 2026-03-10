@extends('email.scaffold.Main')

@section('content')
  <div>
    <h2 class="font-weight-light">Tu apartado fue aprobado</h2>

    <p class="text">
      Hola <b>{{ $data->customer_full_name ?? 'Cliente' }}</b>,
      <br><br>
      Te confirmamos que tu solicitud de apartado para el auto
      <b>{{ $data->vehicle?->uiid ?? 'N/D' }}</b>
      ha sido <b>aprobada</b>.
    </p>

    <p class="text">
      <b>Monto de apartado:</b>
      ${{ number_format((float) ($data->reservation_amount ?? 0), 2) }}
      <br>
      <b>Fecha límite para continuar:</b> {{ $data->expires_at ?? 'N/D' }}
    </p>

    <p class="text" style="margin-top:20px;">
      Por favor continúa con tu proceso de pago conforme a las indicaciones proporcionadas por tu asesor.
      En caso de dudas, te recomendamos comunicarte cuanto antes para evitar que el apartado pierda vigencia.
    </p>
  </div>
@endsection