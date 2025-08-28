<?php

namespace App\Http\Controllers;

use App\Models\VehicleTransmission;
use Illuminate\Http\Request;
use Throwable;

class VehicleTransmissionController extends Controller
{
  public function index(Request $req) {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => VehicleTransmission::getItems($req)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }
}
