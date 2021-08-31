<?php

namespace NotificationChannels\TurboSms\Test;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery as M;
use NotificationChannels\TurboSms\Exceptions\CouldNotSendNotification;
use NotificationChannels\TurboSms\TurboSmsApi;
use NotificationChannels\TurboSms\TurboSmsChannel;
use NotificationChannels\TurboSms\TurboSmsMessage;
use PHPUnit\Framework\TestCase;

class TurboSmsChannelTest extends TestCase
{
    /** @var TurboSmsApi|M\MockInterface */
    private $turbosms;

    /** @var TurboSmsMessage */
    private $message;

    /** @var TurboSmsChannel */
    private $channel;

    /** @var \DateTime */
    public static $sendAt;

    public function setUp(): void
    {
        $this->turbosms = M::mock(TurboSmsApi::class, [
            'login' => 'test',
            'secret' => 'test',
            'sender' => 'John_Doe',
            'url' => 'http://example.com/wsdl.html',
        ]);
        $this->channel = new TurboSmsChannel($this->turbosms);
        $this->message = M::mock(TurboSmsMessage::class);
    }

    public function tearDown(): void
    {
        M::close();
    }

    public function test_it_can_send_a_notification(): void
    {
        $this->expectNotToPerformAssertions();
        $this->turbosms->shouldReceive('send')
            ->once()
            ->with([
                'phone'  => '+1234567890',
                'text'   => 'hello',
            ]);

        $this->channel->send(new TestNotifiable(), new TestNotification());
    }

    public function test_it_does_not_send_a_message_when_to_missed(): void
    {
        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionMessage('Notification was not sent. Phone number is missing.');

        $this->channel->send(
            new TestNotifiableWithoutRouteNotificationForTurboSms(),
            new TestNotification()
        );
    }
}

class TestNotifiable
{
    use Notifiable;

    // Laravel v5.6+ passes the notification instance here
    // So we need to add `Notification $notification` argument to check it when this project stops supporting < 5.6
    public function routeNotificationForTurboSms()
    {
        return '+1234567890';
    }
}

class TestNotifiableWithoutRouteNotificationForTurboSms extends TestNotifiable
{
    public function routeNotificationForTurboSms()
    {
        return false;
    }
}

class TestNotification extends Notification
{
    public function toTurboSms()
    {
        return TurboSmsMessage::create('hello')->from('John_Doe');
    }
}
