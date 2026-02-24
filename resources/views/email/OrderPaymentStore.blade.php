@extends('email.scaffold.Main')

@section('content')
  <div>
    <h2 class="font-weight-light">Nueva Orden de Pago Generada</h2>

    <p class="text">
      Se ha generado una nueva orden de pago en el sistema con el folio <b>{{ $data->uiid }}</b>.
      <br><br>
      Le invitamos a revisar los detalles correspondientes para su validación y seguimiento.
    </p>

    <p class="text" style="margin-top:20px;">
      Para cargar los comprobantes, es necesario registrar previamente los autos asociados a la orden de pago.
      En caso contrario, no será posible adjuntar dichos comprobantes.
    </p>
  </div>
@endsection