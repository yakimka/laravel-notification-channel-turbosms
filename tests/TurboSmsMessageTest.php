<?php
namespace NotificationChannels\TurboSms\Test;
use NotificationChannels\TurboSms\TurboSmsMessage;

class TurboSmsMessageTest extends \PHPUnit_Framework_TestCase {
	/** @test */
	public function it_can_accept_a_content_when_constructing_a_message() {
		$message = new TurboSmsMessage('hello');
		$this->assertEquals('hello', $message->content);
	}
	/** @test */
	public function it_can_accept_a_content_when_creating_a_message() {
		$message = TurboSmsMessage::create('hello');
		$this->assertEquals('hello', $message->content);
	}
	/** @test */
	public function it_can_set_the_content() {
		$message = (new TurboSmsMessage())->content('hello');
		$this->assertEquals('hello', $message->content);
	}
	/** @test */
	public function it_can_set_the_from() {
		$message = (new TurboSmsMessage())->from('John_Doe');
		$this->assertEquals('John_Doe', $message->from);
	}

}
