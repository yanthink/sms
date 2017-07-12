<?php

namespace Yanthink\Sms;

use Yanthink\Sms\Contracts\Sms;
use SoapClient;

/**
 * 3G新传媒短信实现
 * Yanthink\Sms\XcmHandler
 */
class XcmHandler implements Sms
{

    protected $charset = 'UTF-8';

    protected $account;

    protected $password;

    protected $cpid;

    protected $chid;

    protected $wsdlAddress;

    public function __construct($account, $password, $cpid = null, $chid = 112, $wsdlAddress = null)
    {
        $this->account = $account;
        $this->password = $password;
        if ($cpid) {
            $this->setCpid($cpid);
        }

        if ($chid) {
            $this->setChid($chid);
        }

        if ($wsdlAddress) {
            $this->setWsdlAddress($wsdlAddress);
        }
    }

    public function setCpid($cpid)
    {
        $this->cpid = $cpid;

        return $this;
    }

    public function setChid($chid)
    {
        $this->chid = $chid;

        return $this;
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

    public function send($mobiles, $content, $sendTime = null, $voice = false)
    {
        $params = [
            'in0' => join(',', (array)$mobiles),
            'in1' => $content,
            'in2' => '',
            'in3' => $this->chid,
            'in4' => $this->account,
            'in5' => $this->password
        ];

        $result = $this->dispatch('sendSmsAsNormal', $params);

        return $result->out === '0';
    }

    public function sendVoice($mobiles, $content, $sendTime = null)
    {
        // TODO: Implement sendVoice() method.
    }

    public function getBalance()
    {
        // TODO: Implement getBalance() method.
    }
}