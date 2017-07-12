<?php

namespace Yanthink\Sms;

use Carbon\Carbon;
use DateTime;
use Yanthink\Sms\Contracts\Sms;
use SoapClient;

/**
 * 建周短信实现
 * Yanthink\Sms\JianzhouHandler
 */
class JianzhouHandler implements Sms
{

    protected $charset = 'UTF-8';

    protected $account;

    protected $password;

    protected $wsdlAddress;

    public function __construct($account, $password, $wsdlAddress = null)
    {
        $this->account = $account;
        $this->password = $password;

        if ($wsdlAddress) {
            $this->setWsdlAddress($wsdlAddress);
        }
    }

    public function setWsdlAddress($wsdlAddress)
    {
        $this->wsdlAddress = $wsdlAddress;

        return $this;
    }

    protected function dispatch($funcName, $params = [])
    {
        $client = new SoapClient($this->wsdlAddress, ['encoding' => $this->charset, 'connection_timeout' => 10]);

        return $client->{$funcName}($params);
    }

    protected function getSendTime($delay)
    {
        if ($delay instanceof DateTime) {
            return Carbon::instance($delay)->format('YmdHis');
        }

        return $delay > 0 ? Carbon::now()->addSeconds($delay)->format('YmdHis') : null;
    }

    public function send($mobiles, $content, $delay = null, $voice = false)
    {
        $params = [
            'account' => $this->account,
            'password' => $this->password,
            'destmobile' => join(';', (array)$mobiles),
            'msgText' => $content,
        ];

        $funcName = 'sendBatchMessage';

        $sendTime = $this->getSendTime($delay);
        if ($sendTime) {
            $params['sendDateTime'] = $sendTime;
            $funcName = 'sendTimelyMessage';
        }

        $result = $this->dispatch($funcName, $params);

        return $result->sendBatchMessageReturn > 0;
    }

    public function sendVoice($mobiles, $content, $delay = null)
    {
        // TODO: Implement sendVoice() method.
    }

    public function getBalance()
    {
        $params = [
            'account' => $this->account,
            'password' => $this->password,
        ];

        $result = $this->dispatch('getUserInfo', $params);

        return $result;
    }

    public function isTime($time)
    {
        return strlen($time) == 14 && $time > date('YmdHms');
    }
}