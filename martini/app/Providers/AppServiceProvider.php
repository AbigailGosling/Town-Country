<?php

namespace App\Providers;

use App\Listeners\NotificationSendingListener;
use App\Listeners\NotificationSentListener;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Jenssegers\Agent\Agent;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //table config
	    Schema::defaultStringLength(191);
        //Observers
        User::observe(UserObserver::class);
        View::share('user_agent', new Agent());
        Event::listen(NotificationSent::class,NotificationSentListener::class);
        Event::listen(NotificationSending::class,NotificationSendingListener::class);
        /*DB::listen(function ($query) {
            Log::info(json_encode(["query"=>$query->sql,"time"=>$query->time]));     // the query being executed
        });*/
    }
}
