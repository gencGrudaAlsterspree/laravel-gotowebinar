<?php

namespace WizeWiz\Gotowebinar\Client;

use Illuminate\Support\Str;

trait PathBuilder
{
    /**
     * The type of the encoding in the query.
     *
     * @var int Can be either PHP_QUERY_RFC3986 or PHP_QUERY_RFC1738.
     */
    protected $encodingType = PHP_QUERY_RFC1738;

    private function buildUrl($path, $parameters = null)
    {
        $path = $this->replaceKeyPlaceholders($path, $this->getPathKeys());

        if (is_null($parameters) || empty($parameters)) {
            return $this->cleanPath($path);
        }

        return $this->cleanPath($path).'?'.http_build_query($parameters, '', '&', $this->encodingType);
    }

    private function cleanPath($path)
    {
        return trim($path, '/');
    }

    private function getPathKeys()
    {
        $keys = [
            'accountKey' => $this->getAccountKey(),
            'organizerKey' => $this->getOrganizerKey(),
        ];

        if (is_array($this->pathKeys)) {
            $keys = array_merge($keys, $this->pathKeys);
        }

        return $keys;
    }

    protected function replaceKeyPlaceholders($path, array $replacements)
    {
        if (empty($replacements)) {
            return $path;
        }

        foreach ($replacements as $key => $value) {
            $path = str_replace(
                [':'.$key, ':'.Str::upper($key), ':'.Str::ucfirst($key)],
                [$value, Str::upper($value), Str::ucfirst($value)],
                $path
            );
        }

        return $path;
    }
}
