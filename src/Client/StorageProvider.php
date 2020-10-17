<?php

namespace Slakbal\Gotowebinar\Client;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Slakbal\Gotowebinar\Traits\ConfigHelper;

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
        return $this->getStorageDisk()->has($this->getStorageRelativeFilepath($connection));
    }

    public function getStoredAccessInformation($connection) : ?array
    {
        $information = [];
        if($this->hasStoredAccessInformation($connection)) {
            $information = json_decode($this->getStorageDisk()->get($this->getStorageRelativeFilepath($connection)), true) ?? [];
        }
        return $information;
    }

    public function storeAccessInformation(object $information, $connection) : bool
    {
        return $this->getStorageDisk()->put($this->getStorageRelativeFilepath($connection), json_encode($information));
    }

    public function storeRefreshTokenRequest($refresh_token, $connection) : bool {
        return $this->getStorageDisk()->put($this->getStorageRelativeFilepath($connection, 'request'), json_encode([
            'refresh-token' => $refresh_token,
            'request-time' => Carbon::now()->timestamp,
            'ttl' => 30*24*60*60,
            'expires-at' => Carbon::now()->addDays(30)->timestamp
        ]));
    }

    public function clearAuthStorage($connection)
    {
        if($this->hasStoredAccessInformation($connection)) {
            $disk = $this->getStorageDisk();
            $disk->delete($this->getStorageRelativeFilepath($connection));
            $disk->delete($this->getStorageRelativeFilepath($connection, 'request'));
        }
    }
}