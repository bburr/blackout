<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Middleware\HandleSessionUser;
use App\Http\Requests\CreateUser;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function createUser(CreateUser $request)
    {
        $user = (new User())
            ->fill([
                'name' => $request->get('name'),
            ]);

        $user->save();

        Session::put(HandleSessionUser::CACHE_KEY, $user->getKey());

        return response()->json($user);
    }
}
