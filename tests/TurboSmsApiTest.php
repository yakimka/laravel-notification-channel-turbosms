<?php

namespace NotificationChannels\TurboSms\Test;

use NotificationChannels\TurboSms\TurboSmsApi;
use NotificationChannels\TurboSms\Exceptions\CouldNotSendNotification;
use SoapClient;
use Mockery as M;
use PHPUnit\Framework\TestCase;

class TurboSmsApiTest extends TestCase
{
    /** @var SoapClient|M\MockInterface */
    private $soapClient;

    public function setUp(): void
    {
        $this->soapClient = M::mock(SoapClient::class);
        $this->params = [
            'phone' => '+1234567890',
            'text'  => 'hello',
        ];
    }

    public function tearDown(): void
    {
        M::close();
    }
    
    public function test_construct_params(): void
    {   
        $turbosms = $this->getExtendedTurboSmsApi([
            'login'  => $login = 'login',
            'secret' => $secret = 'secret',
            'sender' => $sender = 'sender',
            'client' => $client = $this->soapClient,
        ]);

        $this->assertEquals($login, $turbosms->getLogin());
        $this->assertEquals($secret, $turbosms->getSecret());
        $this->assertEquals($sender, $turbosms->getSender());
        $this->assertEquals($client, $turbosms->getClient());
    }

    public function test_error_when_trying_to_auth(): void
    {
        $this->soapClient->shouldReceive([
            'Auth' => new TestAuthResult('Неверный логин или пароль'),
        ])
        ->once()
        ->with([
            'login'    => 'login',
            'password' => 'secret',
        ]);
        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionMessage('The communication with TurboSms failed. Reason: Notification was not sent. Failed login to TurboSms.');

        $turbosms = $this->getExtendedTurboSmsApi([
            'login'  => $login = 'login',
            'secret' => $secret = 'secret',
            'sender' => $sender = 'sender',
            'client' => $client = $this->soapClient,
        ]);

        $turbosms->send($this->params);
    }

    public function test_send_sms(): void
    {
        $this->soapClient->shouldReceive([
            'Auth' => new TestAuthResult('ОК'),
        ])->once();

        $expectedResult = new TestSendSmsResult(new TestResultArray(['Сообщения успешно отправлены']));
        $this->soapClient->shouldReceive([
            'SendSMS' => $expectedResult,
        ])
        ->once()
        ->with([
            'sender'      => 'sender',
            'destination' => '+1234567890',
            'text'        => 'hello',
        ]);

        $turbosms = $this->getExtendedTurboSmsApi([
            'login'  => $login = 'login',
            'secret' => $secret = 'secret',
            'sender' => $sender = 'sender',
            'client' => $client = $this->soapClient,
        ]);

        $result = $turbosms->send($this->params);

        $this->assertEquals($expectedResult, $result);
    }

    public function test_send_sms_and_receive_error(): void
    {
        $this->soapClient->shouldReceive([
            'Auth' => new TestAuthResult('ОК'),
        ])->once();

        $expectedResult = new TestSendSmsResult(new TestResultArray('Ошибка'));
        $this->soapClient->shouldReceive([
            'SendSMS' => $expectedResult,
        ])
        ->once()
        ->with([
            'sender'      => 'sender',
            'destination' => '+1234567890',
            'text'        => 'hello',
        ]);
        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionMessage('Ошибка');

        $turbosms = $this->getExtendedTurboSmsApi([
            'login'  => $login = 'login',
            'secret' => $secret = 'secret',
            'sender' => $sender = 'sender',
            'client' => $client = $this->soapClient,
        ]);

        $turbosms->send($this->params);
    }

    private function getExtendedTurboSmsApi(array $config)
    {
        return new class(...$config) extends TurboSmsApi
        {
            public function getClient(): string
            {
                return $this->soapClient;
            }

            public function getLogin(): string
            {
                return $this->login;
            }

            public function getSecret(): string
            {
                return $this->secret;
            }

            public function getSender(): string
            {
                return $this->sender;
            }
        };
    }
}


class TestAuthResult
{

    public function __construct($resultMsg)
    {
        $this->AuthResult = $resultMsg;
    }
}

class TestSendSmsResult
{

    public function __construct($result)
    {
        $this->SendSMSResult = $result;
    }
}


class TestResultArray
{

    public function __construct($resultArray)
    {
        $this->ResultArray = $resultArray;
    }
}
