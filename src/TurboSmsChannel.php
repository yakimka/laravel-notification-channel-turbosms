<?php

namespace NotificationChannels\TurboSms;

use Illuminate\Notifications\Notification;
use NotificationChannels\TurboSms\Exceptions\CouldNotSendNotification;

class TurboSmsChannel
{

    /** @var \NotificationChannels\TurboSms\TurboSmsApi */
    protected $turbosms;

    public function __construct(TurboSmsApi $turbosms)
    {
        $this->turbosms = $turbosms;
    }

    /**
     * Send the given notification.
     *
     * @param mixed                                  $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws \NotificationChannels\TurboSms\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        $to = $notifiable->routeNotificationFor('turbosms');
        if (empty($to)) {
            throw CouldNotSendNotification::missingRecipient();
        }
        $message = $notification->toTurbosms($notifiable);
        if (is_string($message)) {
            $message = new TurboSmsMessage($message);
        }
        $this->sendMessage($to, $message);
    }

    protected function sendMessage($recipient, TurboSmsMessage $message)
    {
        if (mb_strlen($message->content) > 800) {
            throw CouldNotSendNotification::contentLengthLimitExceeded();
        }
        $params = [
          'phone' => $recipient,
          'text' => $message->content,
        ];
        $this->turbosms->send($params);
    }
}
