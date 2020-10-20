<?php

namespace WizeWiz\Gotowebinar\Resources\Session;

use WizeWiz\Gotowebinar\Resources\AbstractResource;

final class Session extends AbstractResource
{
    use SessionQueryParameters, SessionOperations;

    /** RESOURCE PATH **/
    protected $baseResourcePath = '/organizers/:organizerKey/webinars/:webinarKey/sessions';

    public function __construct()
    {
        $this->resourcePath = $this->baseResourcePath;
    }
}
