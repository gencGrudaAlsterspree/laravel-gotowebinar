<?php

namespace Slakbal\Gotowebinar\Client;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait AccessProvider
{
    protected $cache_tags = ['GOTO', 'GOTO-AUTH'];

    public function status($limit = true)
    {
        return [
            'ready' => $this->hasAccessToken() ? 'true' : 'false',
            'access_token' => Str::limit($this->getAccessToken(), 10),
            'refresh_token' => Str::limit($this->getRefreshToken(), 10),
            'organiser_key' => $limit ? Str::limit($this->getOrganizerKey(), 8) : $this->getOrganizerKey(),
            'account_key' => $limit ? Str::limit($this->getAccountKey(), 8) : $this->getAccountKey(),
        ];
    }

    public function getAuthenticationHeader()
    {
        return ['Authorization' => 'Basic '.base64_encode($this->getClientId().':'.$this->getClientSecret())];
    }

    public function getClientId()
    {
        return config('goto.client_id'); //Consumer Key = Client Id
    }

    public function getClientSecret()
    {
        return config('goto.client_secret');
    }

    public function getAuthorisationHeader()
    {
        return ['Authorization' => 'Bearer '.$this->getAccessToken()];
    }

    public function getAccessToken()
    {
        return Cache::tags($this->cache_tags)->get('access-token');
    }

    public function setAccessInformation($responseObject)
    {
        $this->setAccessToken($responseObject->access_token, $responseObject->expires_in)
             ->setRefreshToken($responseObject->refresh_token)
             ->setOrganizerKey($responseObject->organizer_key)
             ->setAccountKey($responseObject->account_key);

        return $this;
    }

    public function setAccountKey($accountKey)
    {
        Cache::tags($this->cache_tags)->forever('account-key', $accountKey);

        return $this;
    }

    public function setOrganizerKey($organizerKey)
    {
        Cache::tags($this->cache_tags)->forever('organizer-key', $organizerKey);

        return $this;
    }

    public function setRefreshToken($refreshToken, $ttlSeconds = null)
    {
        Cache::tags($this->cache_tags)->put('refresh-token', $refreshToken, $ttlSeconds ?? Carbon::now()->addDays(30));

        return $this;
    }

    public function setAccessToken($accessToken, $ttlSeconds = null)
    {
        Cache::tags($this->cache_tags)->put('access-token', $accessToken, $ttlSeconds ?? Carbon::now()->addHour());

        return $this;
    }

    public function hasAccessToken()
    {
        return Cache::tags($this->cache_tags)->has('access-token');
    }

    public function getOrganizerKey()
    {
        return Cache::tags($this->cache_tags)->get('organizer-key');
    }

    public function getAccountKey()
    {
        return Cache::tags($this->cache_tags)->get('account-key');
    }

    public function hasRefreshToken()
    {
        return Cache::tags($this->cache_tags)->has('refresh-token');
    }

    public function getRefreshToken()
    {
        return Cache::tags($this->cache_tags)->get('refresh-token');
    }

    public function clearAuthCache()
    {
        Cache::tags('GOTO-AUTH')->flush();

        return $this;
    }
}
