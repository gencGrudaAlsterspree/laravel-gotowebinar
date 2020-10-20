<?php

namespace WizeWiz\Gotowebinar\Client;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use WizeWiz\Gotowebinar\Traits\ConfigHelper;

trait AccessProvider
{
    use ConfigHelper;

    protected $cache_tags = ['GOTO', 'GOTO-AUTH'];

    protected $connection;

    public function setConnection($connection)
    {
        if($connection) {
            $this->connection = Str::lower(Str::slug($connection, '_'));
        }

        return $this;
    }

    public function getConnection() : string
    {
        return $this->connection ?? $this->getFromConfig('connection_default', 'default');
    }

    public function getConnectionTag($connection)
    {
        return "GOTO-CONNECTION:" . ($connection ?? $this->connection);
    }

    public function getCacheTags()
    {
        return $this->connection ?
            array_merge($this->cache_tags, [$this->getConnectionTag($this->connection)]) :
            $this->cache_tags;
    }

    public function status($limit = true)
    {
        return [
            'ready' => $this->hasAccessToken() ? 'true' : 'false',
            'access_token' => Str::limit($this->getAccessToken(), 10),
            'refresh_token' => Str::limit($this->getRefreshToken(), 10),
            'organiser_key' => $limit ? Str::limit($this->getOrganizerKey(), 8) : $this->getOrganizerKey(),
            'account_key' => $limit ? Str::limit($this->getAccountKey(), 8) : $this->getAccountKey(),
            'expires_at' => $this->getExpiresAt()
        ];
    }

    public function getAuthenticationHeader()
    {
        return ['Authorization' => 'Basic '.base64_encode($this->getClientId().':'.$this->getClientSecret())];
    }

    public function getAuthorisationHeader()
    {
        return ['Authorization' => 'Bearer '.$this->getAccessToken()];
    }

    public function getAccessToken()
    {
        return Cache::tags($this->getCacheTags())->get('access-token');
    }

    public function cacheAccessInformation(object $object)
    {
        $this->setAccessToken($object->access_token, $object->expires_in)
             ->setRefreshToken($object->refresh_token)
             ->setOrganizerKey($object->organizer_key)
             ->setAccountKey($object->account_key);

        return $this;
    }

    public function getAccessInformationDefaults() : array
    {
        return [
            'access_token' => null,
            'refresh_token' => null,
            'expires_in' => null,
            'organizer_key' => null,
            'account_key' => null,
            'expires_at' => null
        ];
    }

    public function setAccountKey($accountKey)
    {
        Cache::tags($this->getCacheTags())->forever('account-key', $accountKey);

        return $this;
    }

    public function setOrganizerKey($organizerKey)
    {
        Cache::tags($this->getCacheTags())->forever('organizer-key', $organizerKey);

        return $this;
    }

    public function setRefreshToken($refreshToken, $ttlSeconds = null)
    {
        Cache::tags($this->getCacheTags())->put('refresh-token', $refreshToken, $ttlSeconds ?? Carbon::now()->addDays(30));

        return $this;
    }

    public function setAccessToken($accessToken, $ttlSeconds = 3600)
    {
        $expiry = Carbon::now()->addSeconds($ttlSeconds);
        Cache::tags($this->getCacheTags())->put('access-token', $accessToken, $expiry);
        $this->setExpiresAt($expiry->timestamp);

        return $this;
    }

    public function hasAccessToken()
    {
        return Cache::tags($this->getCacheTags())->has('access-token');
    }

    public function getOrganizerKey()
    {
        return Cache::tags($this->getCacheTags())->get('organizer-key');
    }

    public function getAccountKey()
    {
        return Cache::tags($this->getCacheTags())->get('account-key');
    }

    public function hasRefreshToken()
    {
        return Cache::tags($this->getCacheTags())->has('refresh-token');
    }

    public function getRefreshToken()
    {
        return Cache::tags($this->getCacheTags())->get('refresh-token');
    }

    public function getExpiresIn()
    {
        return Cache::tags($this->getCacheTags())->get('expires-in');
    }

    public function setExpiresAt(int $expires_at)
    {
        return Cache::tags($this->getCacheTags())->put('expires-at', $expires_at);
    }

    public function getExpiresAt()
    {
        if( ($expiry = Cache::tags($this->getCacheTags())->get('expires-at')) ) {
            return Carbon::createFromTimestamp($expiry);
        }
        return null;
    }

    public function clearAuthCache($connection = null)
    {
        $tag = $connection ?
            $this->getConnectionTag($connection) :
            'GOTO-AUTH';

        Cache::tags($tag)->flush();

        return $this;
    }

}
