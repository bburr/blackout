<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Jobs\User\CreateUser;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function createUser(CreateUserRequest $request): Response
    {
        /** @var User $user */
        $user = Bus::dispatch(new CreateUser($request->getName(), startSession: true));

        return response()->json($user);
    }

    public function createOtherUser(CreateUserRequest $request): Response
    {
        $user = Bus::dispatch(new CreateUser($request->getName(), startSession: false));

        return response()->json($user);
    }
}
