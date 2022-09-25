<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->app->make('auth')
            ->viaRequest(
                'auth0-subject-token',
                function (Request $request) {
                    if (in_array($request->header('x-auth-audience'), $this->app['config']['auth']['audience'])) {
                        return User::firstOrCreate([
                            'sub' => $request->header('x-auth-subject'),
                        ], [
                            'name' => $request->header('x-auth-name'),
                            'email' => $request->header('x-auth-email'),
                        ]);
                    }

                    return null;
                }
            );
    }
}
