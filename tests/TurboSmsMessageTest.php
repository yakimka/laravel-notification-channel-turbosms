<?php

namespace NotificationChannels\TurboSms\Test;

use NotificationChannels\TurboSms\TurboSmsMessage;
use PHPUnit\Framework\TestCase;

class TurboSmsMessageTest extends TestCase
{
    public function test_it_can_accept_a_content_when_constructing_a_message(): void
    {
        $message = new TurboSmsMessage('hello');

        $this->assertEquals('hello', $message->content);
    }

    public function test_it_can_accept_a_content_when_creating_a_message(): void
    {
        $message = TurboSmsMessage::create('hello');

        $this->assertEquals('hello', $message->content);
    }

    public function test_it_can_set_the_content(): void
    {
        $message = (new TurboSmsMessage())->content('hello');

        $this->assertEquals('hello', $message->content);
    }

    public function test_it_can_set_the_from(): void
    {
        $message = (new TurboSmsMessage())->from('John_Doe');

        $this->assertEquals('John_Doe', $message->from);
    }
}
