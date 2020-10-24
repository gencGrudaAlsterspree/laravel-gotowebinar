<?php

namespace WizeWiz\Gotowebinar;

use Illuminate\Support\ServiceProvider;
use WizeWiz\Gotowebinar\Client\GotoClient;
use WizeWiz\Gotowebinar\Commands\GoToAccessTokenCommand;
use WizeWiz\Gotowebinar\Commands\GoToGenerateLinkCommand;
use WizeWiz\Gotowebinar\Commands\GoToTokensCommand;
use WizeWiz\Gotowebinar\Contract\GotoClientContract;
use WizeWiz\Gotowebinar\Resources\Attendee\Attendee;
use WizeWiz\Gotowebinar\Resources\Registrant\Registrant;
use WizeWiz\Gotowebinar\Resources\Session\Session;
use WizeWiz\Gotowebinar\Resources\Webinar\Webinar;

class GotoWebinarServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     * @return void
     */
    public function boot()
    {
        if (! $this->app->environment('production')) {
            $this->loadRoutesFrom(__DIR__.'/Routes/routes.php');
        }

        $this->publishes([__DIR__.'/../config/goto.php' => config_path('goto.php')], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GoToGenerateLinkCommand::class,
                GoToAccessTokenCommand::class,
                // GoToRefreshTokenCommand::class,
                GoToTokensCommand::class
            ]);
        }
    }

    public function register()
    {
        // runtime merge config
        $this->mergeConfigFrom(__DIR__.'/../config/goto.php', 'goto');

        $this->app->bind(GotoClientContract::class, GotoClient::class);

        $this->app->bind(Webinar::class, function () {
            return new Webinar();
        });

        $this->app->bind(Registrant::class, function () {
            return new Registrant();
        });

        $this->app->bind(Session::class, function () {
            return new Session();
        });

        $this->app->bind(Attendee::class, function () {
            return new Attendee();
        });
    }
}
