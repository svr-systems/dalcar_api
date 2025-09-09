<?php

namespace App\Http\Controllers;

use App\Models\OriginType;
use Illuminate\Http\Request;
use Throwable;

class OriginTypeController extends Controller
{
  public function index(Request $req) {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => OriginType::getItems($req)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }
}
