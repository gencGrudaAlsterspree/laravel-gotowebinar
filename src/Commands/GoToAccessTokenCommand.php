<?php

namespace Slakbal\Gotowebinar\Commands;

use Illuminate\Console\Command;
use Slakbal\Gotowebinar\Client\GotoClient;
use Slakbal\Gotowebinar\Facade\Webinars;

class GoToAccessTokenCommand extends Command
{

    protected $signature = 'goto:access-token {--flush} {--flush-only} {--ready}';

    protected $description = 'Control access and/or refresh tokens.';

    public function handle()
    {
        if($this->option('ready')) {
            if(app(GotoClient::class)->hasAccessToken()) {
                $this->info('Valid access-token present.');
            }
            else {
                $this->line('<error>!!</error> Valid-access token missing.');
            }
            return;
        }

        if(($flush_only = $this->option('flush-only')) || $this->option('flush')) {
            $result = Webinars::flushAuthentication()->status();
            $this->call('cache:clear', );

            if($flush_only) {
                $this->showResult($result);
                return;
            }
        }

        $result = (array)Webinars::authenticate()->status(false);

        if(!empty($result)) {
            if(array_key_exists('access_token', $result)) {
                $this->info("Access-Token received:\n");
                $this->showResult($result);
            }
            return;
        }

        $this->error('Failed to retrieve Access-Token');
    }

    protected function showResult($result)
    {
        $this->table(['ready', 'access-token', 'refresh_token', 'organiser_key', 'account_key'], [$result]);
    }

}