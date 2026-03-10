<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AuthController extends Controller
{
  public function login(Request $request)
  {
    try {
      $email = GenController::filter($request->email, 'l');
      $password = trim((string) $request->password);

      if (
        !Auth::attempt([
          'email' => $email,
          'password' => $password,
        ])
      ) {
        return $this->apiRsp(422, 'Datos de acceso inválidos');
      }

      $user = Auth::user();

      if (!$user || !boolval($user->is_active)) {
        return $this->apiRsp(422, 'Cuenta inactiva');
      }

      return $this->apiRsp(200, 'Datos de acceso válidos', [
        'auth' => [
          'token' => $user->createToken('passportToken')->accessToken,
          'user' => User::getItemAuth($user->id),
        ],
      ]);
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }

  public function logout(Request $req)
  {
    try {
      $req->user()->token()->revoke();

      return $this->apiRsp(
        200,
        'Sesión finalizada correctamente'
      );
    } catch (Throwable $err) {
      return $this->apiRsp(500, null, $err);
    }
  }
}
