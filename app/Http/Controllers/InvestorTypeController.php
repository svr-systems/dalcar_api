<?php

namespace App\Http\Controllers;

use App\Models\InvestorType;
use Illuminate\Http\Request;
use Throwable;

class InvestorTypeController extends Controller
{
  public function index(Request $req) {
    try {
      return $this->apiRsp(
        200,
        'Registros retornados correctamente',
        ['items' => InvestorType::getItems($req)]
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }
}
