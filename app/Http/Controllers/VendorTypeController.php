<?php

namespace App\Http\Controllers;

use App\Models\VendorType;
use Illuminate\Http\Request;
use Throwable;

class VendorTypeController extends Controller
{
  public function index(Request $req) {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => VendorType::getItems($req)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }
}
