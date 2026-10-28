<?php
// app/Http/Middleware/EnsureUserIsStaff.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isEmployee()) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        return $next($request);
    }
}
