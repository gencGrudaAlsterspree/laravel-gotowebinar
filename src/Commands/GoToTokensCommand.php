<?php

namespace WizeWiz\Gotowebinar\Commands;

use Illuminate\Console\Command;
use WizeWiz\Gotowebinar\Client\GotoClient;
use WizeWiz\Gotowebinar\Facade\Webinars;

class GoToTokensCommand extends Command
{

    protected $signature = 'goto:tokens';

    protected $description = 'Display the current access and refresh tokens.';

    public function handle()
    {
        $Client = app(GotoClient::class);

        $this->info('Access-Token:');
        $this->line($Client->getAccessToken());
        $this->line("\n");
        $this->info('Refresh-Token:');
        $this->line($Client->getRefreshToken());
    }

    protected function showResult($result)
    {
        $this->table(['ready', 'access-token', 'refresh_token', 'organiser_key', 'account_key'], [$result]);
    }

}