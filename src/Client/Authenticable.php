<?php

namespace WizeWiz\Gotowebinar\Client;

use Carbon\Carbon;
use Httpful\Request;
use Illuminate\Http\Response;
use WizeWiz\Gotowebinar\Exception\GotoException;

trait Authenticable
{
    use AccessProvider, StorageProvider;

    protected $directAuthenticationUrl = 'https://api.getgo.com/oauth/v2/token';

    public function authenticate()
    {
        if(! $this->hasAccessToken() && $this->hasStoredAccessInformation($this->getConnection(), 'access'))  {
            $this->restoreAccessFromStorage();
        }

        if (! $this->hasAccessToken()) {
            $this->hasRefreshToken() ?
                $this->refreshAccessToken() :
                $this->authenticateClient();
        }

        return $this;
    }

    public function restoreAccessFromStorage()
    {
        $information = (object) array_merge($this->getAccessInformationDefaults(),
            $this->getStoredAccessInformation($this->getConnection(), 'access'));

        // verify expires in
        if(!$information->expires_in) {
            $information->expires_in = 3600;
        }

        $information->expires_at =
            property_exists($information, 'expires_at') && is_integer($information->expires_at) ?
                Carbon::createFromTimestamp($information->expires_at) :
                Carbon::now()->subHour();

        if($information->expires_at <= Carbon::now()) {
            $information->access_token = null;
        }

        $this->cacheAccessInformation($information);
    }

    public function flushAuthentication()
    {
        $this->clearAuthCache($this->connection);
        // @todo: where has this method gone?
        // $this->flushAccessInformation($this->connection);

        return $this;
    }

    public function refreshAccessToken()
    {
        $response = $this->sendAuthenticationRequest([
             'grant_type' => 'refresh_token',
             'refresh_token' => $this->getRefreshToken(),
         ], false);

        $this->processAuthorizationRequest($response);

        return $response;
    }

    public function verifyRefreshToken($refresh_token)
    {
        $connection = $this->getConnection();
        if($this->getRefreshToken() !== $refresh_token || $this->hasStoredRefreshTokenRequest($connection)) {
            $this->setRefreshToken($refresh_token);
            $this->storeRefreshTokenRequest($refresh_token, $connection);
        }
    }

    protected function authenticateClient()
    {
        return !$this->getFromConnection('legacy', false) ?
            $this->authenticateCode() :
            $this->authenticateDirect();
    }

    /**
     * @deprecated: will be removed in v1.2
     * @todo: drop support
     */
    protected function authenticateDirect()
    {
        $response = $this->sendAuthenticationRequest([
             'grant_type' => 'password',
             'username' => $this->getUsername(),
             'password' => $this->getPassword(),
             'client_id' => $this->getClientId(),
         ]);

        $this->processAuthorizationRequest($response);

        return $response;
    }

    protected function authenticateCode()
    {
        $response = $this->sendAuthenticationRequest([
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getRedirectUri(),
            'code' => $this->getAuthorizationCode(),
        ]);

        $this->processAuthorizationRequest($response);

        return $response;
    }

    protected function processAuthorizationRequest($response)
    {
        $connection = $this->getConnection();
        $response->expires_at = Carbon::now()->addSeconds($response->expires_in ?? 3600)->timestamp;

        // verify refresh token before being cached.
        $this->verifyRefreshToken($response->refresh_token);
        $this->cacheAccessInformation($response);
        $this->storeAccessInformation($response, $connection);
    }

    protected function sendAuthenticationRequest(array $payload)
    {
        $response = Request::post($this->directAuthenticationUrl, http_build_query($payload), 'form')
                                 ->strictSSL($this->strict_ssl)
                                 ->addHeaders($this->getAuthenticationHeader())
                                 ->timeout($this->timeout)
                                 ->expectsJson()
                                 ->send();

        if ($response->code >= Response::HTTP_BAD_REQUEST) {
            throw GotoException::responseException($response, 'Could not authenticate with the provided credentials.', 'POST');
        }

        return $response->body;
    }
}
