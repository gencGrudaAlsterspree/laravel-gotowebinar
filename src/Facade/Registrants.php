<?php

namespace WizeWiz\Gotowebinar\Facade;

use Illuminate\Support\Facades\Facade;
use WizeWiz\Gotowebinar\Resources\Registrant\Registrant;

class Registrants extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Registrant::class;
    }
}
