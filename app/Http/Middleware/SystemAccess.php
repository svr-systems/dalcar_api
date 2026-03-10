<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SystemAccess
{
  public function handle(Request $request, Closure $next)
  {
    $user = $request->user();

    if (!$user || !in_array((int) $user->role_id, [1, 2, 3, 4], true)) {
      return response()->json([
        'msg' => 'No autorizado',
      ], 403);
    }

    return $next($request);
  }
}