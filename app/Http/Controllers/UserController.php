<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\User\CreateUser;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function createUser(CreateUser $request): Response
    {
        $user = $this->newUser($request->get('name'));

        Session::put(User::CACHE_KEY_USER_ID, $user->getKey());

        return response()->json($user);
    }

    public function createOtherUser(CreateUser $request): Response
    {
        $user = $this->newUser($request->get('name'));

        return response()->json($user);
    }

    protected function newUser(string $name): User
    {
        $user = new User([
            'name' => $name,
        ]);

        $user->save();

        return $user;
    }
}
