<?php

namespace NotificationChannels\TurboSms\Test;

use Mockery as M;
use NotificationChannels\TurboSms\Exceptions\CouldNotSendNotification;
use NotificationChannels\TurboSms\TurboSmsApi;
use PHPUnit\Framework\TestCase;
use SoapClient;

class TurboSmsApiTest extends TestCase
{
    /** @var SoapClient|M\MockInterface */
    private $soapClient;

    /**
     * @var string[]
     */
    private $params;

    public function setUp(): void
    {
        $this->soapClient = M::mock(SoapClient::class);
        $this->params = [
            'phone' => '+1234567890',
            'text'  => 'hello',
        ];
        $this->turbosms = $this->getExtendedTurboSmsApi(
            'login',
            'secret',
            'sender',
            $this->soapClient
        );
    }

    public function tearDown(): void
    {
        M::close();
    }

    public function test_construct_params(): void
    {
        $this->assertEquals('login', $this->turbosms->getLogin());
        $this->assertEquals('secret', $this->turbosms->getSecret());
        $this->assertEquals('sender', $this->turbosms->getSender());
        $this->assertEquals($this->soapClient, $this->turbosms->getClient());
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

        $this->turbosms->send($this->params);
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

        $result = $this->turbosms->send($this->params);

        $this->assertEquals($expectedResult, $result);
    }

    public function test_send_sms_and_receive_string_error(): void
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

        $this->turbosms->send($this->params);
    }

    public function test_send_sms_and_receive_array_error(): void
    {
        $this->soapClient->shouldReceive([
            'Auth' => new TestAuthResult('ОК'),
        ])->once();

        $expectedResult = new TestSendSmsResult(new TestResultArray(['Ошибка1', 'Ошибка2']));
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
        $this->expectExceptionMessage('Ошибка1. Ошибка2');

        $this->turbosms->send($this->params);
    }

    private function getExtendedTurboSmsApi($login, $secret, $sender, $client)
    {
        return new class($login, $secret, $sender, $client) extends TurboSmsApi {
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
