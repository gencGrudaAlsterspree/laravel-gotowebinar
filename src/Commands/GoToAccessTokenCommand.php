<?php

namespace WizeWiz\Gotowebinar\Commands;

use Illuminate\Console\Command;
use WizeWiz\Gotowebinar\Contract\GotoClientContract;
use WizeWiz\Gotowebinar\Facade\Webinars;
use WizeWiz\Gotowebinar\Traits\ConfigHelper;

use Exception;

class GoToAccessTokenCommand extends Command
{

    use ConfigHelper;

    protected $signature = 'goto:access-token 
                            {--connection= : Connection to be used} 
                            {--flush : Flush all (cache) access} 
                            {--flush-only : Only flush (cache) access and quit} 
                            {--ready : Check the access status} 
                            {--renew : Renew the access token}';

    protected $description = 'Control access and/or refresh tokens.';

    protected $connection;

    public function handle()
    {
        $this->connection = $this->option('connection') ?? 'default';

        if(!$this->getFromConfig("connections.{$this->connection}")) {
            throw new Exception("Unknown connection: {$this->connection}");
        }

        if($this->option('ready')) {
            return $this->showReadyStatus();
        }

        if(($flush_only = $this->option('flush-only')) || $this->option('flush')) {
            $this->flushAccess($flush_only);

            if($flush_only) {
                return null;
            }
        }

        if($this->option('renew')) {
            return $this->renewAccessToken();
        }

        $this->authorizeAccessToken();
    }

    protected function createGotoClient()
    {
        return app(GotoClientContract::class)->setConnection($this->connection);
    }

    protected function showReadyStatus()
    {
        // @todo: get by contract.
        if($this->createGotoClient()->hasAccessToken()) {
            $this->info("Valid access-token present for connection: {$this->connection}.");
        }
        else {
            $this->line("<error>!!</error> Valid-access token missing for connection: {$this->connection}.");
        }
        return null;
    }

    protected function flushAccess($flush_only)
    {
        $result = $this->createGotoClient()->flushAuthentication()->status();
        $this->call('cache:clear');

        if($flush_only) {
            $this->showResult($result);
        }
    }

    protected function authorizeAccessToken()
    {
        try {

            $result = $this->createGotoClient()->authenticate()->status();

            if (!empty($result)) {
                if (array_key_exists('access_token', $result)) {
                    $this->info("Access-Token received:\n");
                    $this->showResult($result);
                }
            }

        } catch(Exception $e) {
            $this->error('Failed to retrieve Access-Token: ' . $e->getMessage());
        }
    }

    protected function renewAccessToken()
    {
        try {
            $result = $this->createGotoClient()->refreshAccessToken();
        } catch(Exception $e) {
            $this->error('Failed to renew Access-Token: ' . $e->getMessage());
        }
    }

    protected function showResult($result)
    {
        $this->table(['ready', 'access', 'refresh', 'organiser', 'account', 'expires'], [$result]);
    }

}