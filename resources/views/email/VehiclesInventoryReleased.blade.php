@extends('email.scaffold.Main')

@section('content')
  <div>
    <h2 class="font-weight-light">Vehículos liberados para inventario</h2>

    <p class="text">
      Los siguientes vehículos han sido agregados al inventario:
    </p>

    <ul class="text" style="margin-top:10px;">
      @foreach ($data->vehicles as $v)
        <li>
          <b>{{ $v->uiid }}</b> — {{ $v->vehicle_brand_name }} | {{ $v->vehicle_model_name }}
          | {{ $v->vehicle_version_name }} | {{ $v->vehicle_version_model_year }} | Color: {{ $v->vehicle_color_name }}
        </li>
      @endforeach
    </ul>

    <p class="text" style="margin-top:20px;">
      Este mensaje es informativo y forma parte de las notificaciones automáticas del sistema.
    </p>
  </div>
@endsection