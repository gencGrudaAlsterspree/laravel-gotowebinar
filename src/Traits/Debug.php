<?php

namespace Slakbal\Gotowebinar\Traits;

use Illuminate\Support\Facades\Log;

trait Debug
{

    /**
     * @var bool
     */
    static $debug = false;

    static function bootDebug() {
        static::$debug = config('goto.debug', false);
    }

    static function logInfo($msg, $context = [])
    {
        if(static::debug) {
            Log::info($msg, $context);
        }
    }

    static function logError($msg, $context = [])
    {
        if(static::$debug) {
            Log::error($msg, $context);
        }
    }

}