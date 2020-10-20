<?php

namespace WizeWiz\Gotowebinar\Resources\Attendee;

use WizeWiz\Gotowebinar\Resources\AbstractResource;

final class Attendee extends AbstractResource
{
    use AttendeeQueryParameters, AttendeeOperations;

    /** RESOURCE PATH **/
    protected $baseResourcePath = '/organizers/:organizerKey/webinars/:webinarKey/sessions/:sessionKey/attendees';

    public function __construct()
    {
        $this->resourcePath = $this->baseResourcePath;
    }
}
