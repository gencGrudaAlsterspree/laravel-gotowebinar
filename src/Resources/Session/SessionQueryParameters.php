<?php

namespace WizeWiz\Gotowebinar\Resources\Session;

use WizeWiz\Gotowebinar\Traits\Resources\FromToTimeParameters;
use WizeWiz\Gotowebinar\Traits\Resources\PagingParameters;

trait SessionQueryParameters
{
    use PagingParameters,
        FromToTimeParameters;

}
