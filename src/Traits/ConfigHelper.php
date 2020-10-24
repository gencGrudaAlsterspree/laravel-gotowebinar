<?php

namespace WizeWiz\Gotowebinar\Traits;

trait ConfigHelper {

    protected function getFromConnection($connection, $key, $default = null)
    {
        return !$connection ?
            $this->getFromConfig($key, $default) :
            $this->getFromConfig("connections.{$connection}.{$key}", $default);
    }

    protected function getFromConfig($key, $default = null)
    {
        return config("goto.{$key}", $default);
    }

    /**
     * @deprecated
     * @todo: drop support
     */
    public function getUsername()
    {
        return $this->getFromConnection($this->getConnection(), 'username');
    }

    public function getPassword()
    {
        return $this->getFromConnection($this->getConnection(), 'password');
    }

    public function getClientId()
    {
        return $this->getFromConnection($this->getConnection(), 'client_id');
    }

    public function getClientSecret()
    {
        return $this->getFromConnection($this->getConnection(), 'client_secret');
    }

    public function getRedirectUri()
    {
        return $this->getFromConnection($this->getConnection(), 'redirect_uri');
    }

    public function getAuthorizationCode()
    {
        return $this->getFromConnection($this->getConnection(), 'authorization_code');
    }

}