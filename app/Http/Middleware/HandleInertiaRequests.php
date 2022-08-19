<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param Request $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param Request $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'jetstream' => function () use ($request) {
                return [
                    'flash' => $request->session()->get('flash', []),
                ];
            },
            'user' => function () {
                if (! Session::has(User::CACHE_KEY_USER_ID)) {
                    return null;
                }
                $userId = (string) Session::get(User::CACHE_KEY_USER_ID);

                /** @var User $user */
                $user = User::find($userId);

                return [
                    'name' => $user->getName(),
                ];
            },
        ]);
    }
}
