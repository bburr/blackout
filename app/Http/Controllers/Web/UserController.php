<?php declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Jobs\User\CreateUser;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function createUser(CreateUserRequest $request): Response
    {
        Bus::dispatch(new CreateUser($request->getName(), startSession: true));

        return Redirect::route('home');
    }
}
