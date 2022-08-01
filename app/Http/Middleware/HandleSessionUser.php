<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class HandleSessionUser
{
    const CACHE_KEY = 'session-user-id';

    public function handle(Request $request, Closure $next)
    {
        if (! Session::has(self::CACHE_KEY)) {
            return abort(Response::HTTP_UNAUTHORIZED);
        }

        $request->merge([
            'auth_user_id' => Session::get(self::CACHE_KEY),
        ]);

        dump(Session::all());

        return $next($request);
    }
}
