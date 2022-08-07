<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class HandleSessionUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Session::has(User::CACHE_KEY_USER_ID)) {
            return abort(Response::HTTP_UNAUTHORIZED, 'You do not have an active session');
        }

        $request->merge([
            'auth_user_id' => (string) Session::get(User::CACHE_KEY_USER_ID),
        ]);

        return $next($request);
    }
}
