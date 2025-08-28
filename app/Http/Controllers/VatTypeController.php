<?php

namespace App\Http\Controllers;

use App\Models\VatType;
use Illuminate\Http\Request;
use Throwable;

class VatTypeController extends Controller
{
  public function index(Request $req) {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => VatType::getItems($req)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }
}
