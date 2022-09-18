<?php declare(strict_types=1);

namespace App\Jobs\User;

use App\Models\User;
use Illuminate\Support\Facades\Session;

class StartUserSession
{
    public function __construct(protected User $user)
    {
    }

    public function handle(): void
    {
        Session::migrate(destroy: true);
        Session::put(User::CACHE_KEY_USER_ID, $this->user->getKey());
    }
}
