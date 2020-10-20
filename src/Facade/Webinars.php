<?php

namespace WizeWiz\Gotowebinar\Facade;

use Illuminate\Support\Facades\Facade;
use WizeWiz\Gotowebinar\Resources\Webinar\Webinar;

class Webinars extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Webinar::class;
    }
}
