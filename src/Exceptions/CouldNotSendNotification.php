<?php

namespace NotificationChannels\TurboSms\Exceptions;

use DomainException;
use Exception;

class CouldNotSendNotification extends Exception
{

    /**
     * Thrown when recipient's phone number is missing.
     *
     * @return static
     */
    public static function missingRecipient()
    {
        return new static('Notification was not sent. Phone number is missing.');
    }

    /**
     * Thrown when content length is greater than 800 characters.
     *
     * @return static
     */
    public static function contentLengthLimitExceeded()
    {
        return new static(
          'Notification was not sent. Content length may not be greater than 800 characters.'
        );
    }

    /**
     * Thrown when we're unable to communicate with TurboSms.
     *
     * @param  DomainException $exception
     *
     * @return static
     */
    public static function turbosmsRespondedWithAnError(
      DomainException $exception
    ) {
        return new static(
          "TurboSms responded with an error '{$exception}'"
        );
    }

    /**
     * Thrown when we're unable to communicate with TurboSms.
     *
     * @param  Exception $exception
     *
     * @return static
     */
    public static function couldNotCommunicateWithTurboSms(Exception $exception)
    {
        return new static("The communication with TurboSms failed. Reason: {$exception->getMessage()}");
    }

    /**
     * Thrown when autch credentials incorrect.
     *
     * @return static
     */
    public static function incorrectCredentialsTurboSms()
    {
        return new static(
          'Notification was not sent. Failed login to TurboSms.'
        );
    }

    /**
     * Thrown when ballance less than 1 credit.
     *
     * @return static
     */
    public static function lowBalanceTurboSms()
    {
        return new static(
          'Notification was not sent. Low balance.'
        );
    }

    /**
     * @param $request
     *
     * @return mixed
     */
    public function render($request)
    {
        return response()->json($this->getMessage(), 500,
          ['Content-type' => 'application/json; charset=utf-8'],
          JSON_UNESCAPED_UNICODE);
    }
}
