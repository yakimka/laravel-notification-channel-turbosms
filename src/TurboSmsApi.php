<?php

namespace NotificationChannels\TurboSms;

use DomainException;
use NotificationChannels\TurboSms\Exceptions\CouldNotSendNotification;
use SoapClient;

class TurboSmsApi
{
    /** @var SoapClient */
    protected $soapClient;

    /** @var string */
    protected $login;

    /** @var string */
    protected $secret;

    /** @var string */
    protected $sender;

    public function __construct($login, $secret, $sender, SoapClient $client)
    {
        $this->login = $login;
        $this->secret = $secret;
        $this->sender = $sender;
        $this->soapClient = $client;
    }

    /**
     * @param array $params
     *
     * @throws CouldNotSendNotification
     *
     * @return array
     */
    public function send($params)
    {
        try {
            $auth = [
                'login'    => $this->login,
                'password' => $this->secret,
            ];
            $result = $this->soapClient->Auth($auth);
            if ($result->AuthResult == 'Неверный логин или пароль') {
                throw CouldNotSendNotification::incorrectCredentialsTurboSms();
            }

            $sms = [
                'sender'      => $this->sender,
                'destination' => $params['phone'],
                'text'        => $params['text'],
            ];
            $result = $this->soapClient->SendSMS($sms);

            if ($result->SendSMSResult->ResultArray[0]
              != 'Сообщения успешно отправлены'
            ) {
                $message = $result->SendSMSResult->ResultArray;
                if (is_array($message)) {
                    $message = implode('. ', $message);
                }

                throw new DomainException($message);
            }

            return $result;
        } catch (DomainException $exception) {
            throw CouldNotSendNotification::turbosmsRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithTurboSms($exception);
        }
    }
}
