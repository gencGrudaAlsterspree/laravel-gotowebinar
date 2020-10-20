<?php

namespace WizeWiz\Gotowebinar\Facade;

use Illuminate\Support\Facades\Facade;
use WizeWiz\Gotowebinar\Resources\Session\Session;

class Sessions extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Session::class;
    }
}
