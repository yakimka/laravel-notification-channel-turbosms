<?php

namespace NotificationChannels\TurboSms;

use DomainException;
use NotificationChannels\TurboSms\Exceptions\CouldNotSendNotification;
use SoapClient;

class TurboSmsApi
{

    /** @var HttpClient */
    protected $httpClient;

    /** @var string */
    protected $login;

    /** @var string */
    protected $secret;

    /** @var string */
    protected $sender;

    public function __construct($login, $secret, $sender, $url)
    {
        $this->login = $login;
        $this->secret = $secret;
        $this->sender = $sender;
        $this->httpClient = new SoapClient($url);
    }

    /**
     * @param  array $params
     *
     * @return array
     *
     * @throws CouldNotSendNotification
     */
    public function send($params)
    {
        try {
            $auth = [
              'login' => $this->login,
              'password' => $this->secret,
            ];
            $result = $this->httpClient->Auth($auth);
            if ($result->AuthResult == 'Неверный логин или пароль') {
                throw CouldNotSendNotification::incorrectCredentialsTurboSms();
            }
            $result = $this->httpClient->GetCreditBalance();
            $balance = (int)$result->GetCreditBalanceResult;
            if ($balance < 1) {
                throw CouldNotSendNotification::lowBalanceTurboSms();
            }

            $sms = [
              'sender' => $this->sender,
              'destination' => $params['phone'],
              'text' => $params['text'],
            ];
            $result = $this->httpClient->SendSMS($sms);

            if ($result->SendSMSResult->ResultArray[0]
              != 'Сообщения успешно отправлены'
            ) {
                throw new DomainException($result->SendSMSResult->ResultArray);
            }

            return $result;
        } catch (DomainException $exception) {
            throw CouldNotSendNotification::turbosmsRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithTurboSms($exception);
        }
    }
}
