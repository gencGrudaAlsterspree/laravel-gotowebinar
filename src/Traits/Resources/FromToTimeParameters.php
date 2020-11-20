<?php

namespace WizeWiz\Gotowebinar\Traits\Resources;

use Carbon\Carbon;

trait FromToTimeParameters
{

    public function fromTime(Carbon $value): self
    {
        $this->queryParameters['fromTime'] = $value->toW3cString();

        return $this;
    }

    public function toTime(Carbon $value): self
    {
        $this->queryParameters['toTime'] = $value->toW3cString();

        return $this;
    }

}