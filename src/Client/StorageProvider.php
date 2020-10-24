<?php

namespace WizeWiz\Gotowebinar\Client;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use WizeWiz\Gotowebinar\Traits\ConfigHelper;

trait StorageProvider
{

    use ConfigHelper;

    public function initializeStorageProvider()
    {
        // @todo: check if given disk and path visibility is private
    }

    protected function getStorageRelativeFilepath($connection, $filename = null)
    {
        $filename = $filename ?? $this->getFromConfig('storage.file', $this->getFromConnection($connection, 'client_id'));
        $path = $this->getFromConfig('storage.path', 'goto');
        $full_path = $path.DIRECTORY_SEPARATOR."{$connection}.{$filename}";

        return !Str::endsWith($full_path, '.json') ?
            $full_path .'.json' :
            $full_path;
    }

    public function getStorageDisk()
    {
        return Storage::disk($this->getFromConfig('storage.disk', 'local'));
    }

    public function hasStoredAccessInformation($connection) : bool
    {
        return $this->getStorageDisk()->has($this->getStorageRelativeFilepath($connection, 'access'));
    }

    public function getStoredAccessInformation($connection) : ?array
    {
        $information = [];
        if($this->hasStoredAccessInformation($connection)) {
            $information = json_decode($this->getStorageDisk()->get($this->getStorageRelativeFilepath($connection, 'access')), true) ?? [];
        }
        return $information;
    }

    public function storeAccessInformation(object $information, $connection) : bool
    {
        return $this->getStorageDisk()->put($this->getStorageRelativeFilepath($connection, 'access'), json_encode($information));
    }

    public function storeRefreshTokenRequest($refresh_token, $connection) : bool
    {
        $ttl = 30*24*60*60;
        $now = Carbon::now();
        return $this->getStorageDisk()->put($this->getStorageRelativeFilepath($connection, 'request'), json_encode([
            'refresh-token' => $refresh_token,
            'request-time' => $now->timestamp,
            'ttl' => $ttl,
            'expires-at' => $now->addSeconds($ttl)->timestamp
        ]));
    }

    public function hasStoredRefreshTokenRequest($connection) : bool
    {
        return $this->getStorageDisk()->has($this->getStorageRelativeFilepath($connection, 'request'));
    }

    public function getStoredRefreshTokenRequest($connection) : ?array
    {
        $information = [];
        if($this->hasStoredAccessInformation($connection)) {
            $information = json_decode($this->getStorageDisk()->get($this->getStorageRelativeFilepath($connection, 'request')), true) ?? [];
        }
        return $information;
    }

    public function clearAuthStorage($connection)
    {
        if($this->hasStoredAccessInformation($connection)) {
            $disk = $this->getStorageDisk();
            $disk->delete($this->getStorageRelativeFilepath($connection, 'access'));
            $disk->delete($this->getStorageRelativeFilepath($connection, 'request'));
        }
    }
}