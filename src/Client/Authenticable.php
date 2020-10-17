<?php

namespace Slakbal\Gotowebinar\Client;

use Carbon\Carbon;
use Httpful\Request;
use Illuminate\Http\Response;
use Slakbal\Gotowebinar\Exception\GotoException;

trait Authenticable
{
    use AccessProvider, StorageProvider;

    protected $directAuthenticationUrl = 'https://api.getgo.com/oauth/v2/token';

    public function authenticate()
    {
        if(! $this->hasAccessToken() && $this->hasStoredAccessInformation($this->getConnection()))  {
            $this->restoreAccessFromStorage();
        }

        if (! $this->hasAccessToken()) {
            if ($this->hasRefreshToken()) {
                //Get new bearer token with refresh token
                $this->refreshAccessToken();
            } else {
                //Perform fresh authentication for bearer and refresh token
                $this->authenticateClient();
            }
        }

        return $this;
    }

    public function restoreAccessFromStorage()
    {
        $information = (object) array_merge($this->getAccessInformationDefaults(),
            $this->getStoredAccessInformation($this->getConnection()));

        if(!$information->expires_in) {
            // let it be expired.
            $information->expires_in = Carbon::now()->subDay();
        }

        if(!$information->expires_in instanceof Carbon) {
            $information->expires_in = Carbon::createFromTimestamp(strtotime($information->expires_in));
        }

        $this->cacheAccessInformation($information);
    }

    public function flushAuthentication()
    {
        $this->clearAuthCache($this->connection);
        $this->flushAccessInformation($this->connection);

        return $this;
    }

    public function refreshAccessToken()
    {
        $response = $this->sendAuthenticationRequest([
                                                         'grant_type' => 'refresh_token',
                                                         'refresh_token' => $this->getRefreshToken(),
                                                     ]);

        // explicitly set only the Access Token so that the refresh token's ttl expiry is not affected
        // @note: No, the TTL does not decide validation, a new refresh_token could be supplied 1 hour, 3 days or 3 weeks
        //          before "official" expiry, this is not in our control and therefor we should ALWAYS check validity of
        //          the refresh_token that was returned with the request.
        $this->setAccessToken($response->access_token, $response->expires_in);
        // @todo: the problem here is if the cache is cleared, we won't have a persistent refresh token.
        $this->verifyRefreshToken($response->refresh_token);

        return $response;
    }

    public function verifyRefreshToken($refresh_token)
    {
        if($this->getRefreshToken() !== $refresh_token) {
            $this->setRefreshToken($refresh_token);
            $this->storeRefreshTokenRequest($refresh_token, $this->getConnection());
        }
    }

    protected function authenticateClient()
    {
        return !$this->getFromConnection('legacy', false) ?
            $this->authenticateCode() :
            $this->authenticateDirect();
    }

    /**
     * @deprecated
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

        $this->processAuthentication($response);

        return $response;
    }

    protected function authenticateCode()
    {
        $response = $this->sendAuthenticationRequest([
                                                        'grant_type' => 'authorization_code',
                                                        'redirect_uri' => $this->getRedirectUri(),
                                                        'code' => $this->getAuthorizationCode(),
                                                    ]);

        $this->processAuthentication($response);

        return $response;
    }

    protected function processAuthentication($response)
    {
        $connection = $this->getConnection();
        $response->expires_at = Carbon::now()->addSeconds($response->expires_in ?? 3600)->timestamp;

        $this->cacheAccessInformation($response);
        $this->storeAccessInformation($response, $connection);
        $this->storeRefreshTokenRequest($response->refresh_token, $connection);
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
