<?php

namespace Slakbal\Gotowebinar\Exception;

use Illuminate\Http\Response;
use Slakbal\Gotowebinar\Traits\Debug;

class GotoException extends \Exception
{
    use Debug;

    public static function responseException($response, $customMessage = null, $verb = null)
    {
        $message = self::getResponseMessage($response, $customMessage);

        static::logError('GotoWebinar: '.self::formatVerb($verb).$message.' - Payload: '.json_encode($response->body));

        return new static($message);
    }

    private static function getResponseMessage($response, $customMessage = null)
    {
        $message = Response::$statusTexts[$response->code];

        if ($response->hasErrors() && $response->hasBody()) {
            if (isset($response->body->description)) {
                $message .= ' - '.$response->body->description;
            }
        }

        if ($customMessage) {
            $message = $message.' - '.$customMessage;
        }

        return $message;
    }

    private static function formatVerb($verb = null)
    {
        return ! empty($verb) ? strtoupper($verb).' - ' : null;
    }
}
