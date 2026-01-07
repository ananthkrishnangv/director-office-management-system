<?php

namespace App\Http\Middleware;

use App\Models\Lab;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $lab = null;

        if ($user && $user->lab_id) {
            $lab = Lab::find($user->lab_id);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'lab' => $lab,
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
            'appName' => config('app.name', 'Unified Director Office Management System'),
        ];
    }
}
