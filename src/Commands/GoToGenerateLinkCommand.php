<?php

namespace WizeWiz\Gotowebinar\Commands;

use Illuminate\Console\Command;
use WizeWiz\Gotowebinar\Traits\ConfigHelper;

class GoToGenerateLinkCommand extends Command
{
    use ConfigHelper;

    protected $signature = 'goto:generate-link {--state=} {--connection=}';

    protected $description = 'Generate an authorization link to receive an authorization code.
                              {--connection} The connection to generate this link for.
                              {--state} Pass a state to prevent cross-site request forgery';

    protected $scheme = 'https://api.getgo.com/oauth/v2/authorize?client_id={client_id}&response_type=code&redirect_uri={redirect_uri}{state}';

    public function handle()
    {
        $state = $this->option('state') ?? null;
        $connection = $this->option('connection') ?? 'default';

        $link = str_replace([
            '{client_id}',
            '{redirect_uri}',
            '{state}'
        ], [
            $this->getFromConnection($connection,'client_id'),
            $this->getFromConnection($connection, 'redirect_uri'),
            $state ? "&state={$state}" : ''
        ], $this->scheme);

        $this->info("Click (or copy/paste) the following link to receive an authorization code.\n".
                           "If your browser is not logged in, you need to login once with your credentials.\n".
                           "The returned authorization code will be exchanged for an access-token and\n".
                           "invalidates after the exchange.\n\n".
                           "Read for more info: https://developer.goto.com/guides/HowTos/03_HOW_accessToken/\n\n");
        $this->line('-- Authorization link: --');
        $this->line($link);
    }

}