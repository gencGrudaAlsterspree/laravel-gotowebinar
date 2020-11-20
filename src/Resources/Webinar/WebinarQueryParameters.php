<?php

namespace WizeWiz\Gotowebinar\Resources\Webinar;

use WizeWiz\Gotowebinar\Traits\Resources\FromToTimeParameters;
use WizeWiz\Gotowebinar\Traits\Resources\PagingParameters;

trait WebinarQueryParameters
{
    use PagingParameters,
        FromToTimeParameters;

    public function sendUpdateNotifications(): self
    {
        $this->queryParameters['notifyParticipants'] = true;

        return $this;
    }

    public function dontSendUpdateNotifications(): self
    {
        $this->queryParameters['notifyParticipants'] = false;

        return $this;
    }

    public function sendCancellationEmails(): self
    {
        $this->queryParameters['sendCancellationEmails'] = true;

        return $this;
    }

    public function dontSendCancellationEmails(): self
    {
        $this->queryParameters['sendCancellationEmails'] = false;

        return $this;
    }
}
