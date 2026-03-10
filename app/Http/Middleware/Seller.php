<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Seller
{
  public function handle(Request $request, Closure $next)
  {
    $user = $request->user();

    if (!$user || (int) $user->role_id !== 5) {
      return response()->json([
        'msg' => 'No autorizado',
      ], 403);
    }

    return $next($request);
  }
}