<?php

namespace App\Providers;

use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\User' => 'App\Policies\UserPolicy',
        'App\CutGroupNationalityDate' => 'App\Policies\CutGroupNationalityDatePolicy',
        'App\HealthMark' => 'App\Policies\HealthMarkPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function($user, $ability) {
            if ($user->disabled == 1) {
                return false;
            }
            if ($user->isAdmin()) {
                return true;
            }
        });
    }
}
