<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Home', [
            'user' => Session::has(User::CACHE_KEY_USER_ID) ? User::find(Session::get(User::CACHE_KEY_USER_ID)) : null,
        ]);
    }
}
