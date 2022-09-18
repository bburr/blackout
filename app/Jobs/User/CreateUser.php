<?php declare(strict_types=1);

namespace App\Jobs\User;

use App\Models\User;
use Illuminate\Support\Facades\Bus;

class CreateUser
{
    public function __construct(protected string $name, protected bool $startSession)
    {
    }

    public function handle(): User
    {
        $user = new User([
            'name' => $this->name,
        ]);

        $user->save();

        if ($this->startSession) {
            Bus::dispatch(new StartUserSession($user));
        }

        return $user;
    }
}
