<?php

namespace WizeWiz\Gotowebinar\Facade;

use Illuminate\Support\Facades\Facade;
use WizeWiz\Gotowebinar\Resources\Attendee\Attendee;

class Attendees extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Attendee::class;
    }
}
