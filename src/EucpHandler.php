<?php

namespace Yanthink\Sms;

use Carbon\Carbon;
use DateTime;
use Yanthink\Sms\Contracts\Sms;
use SoapClient;

/**
 * 亿美软通短信实现
 * Yanthink\Sms\EucpHandler
 */
class EucpHandler implements Sms
{

    /**
     * @var string $charset 字符编码
     */
    protected $charset = 'UTF-8';

    /**
     * @var string $account 亿美软通商户号
     */
    protected $account;

    /**
     * @var string $password 密码
     */
    protected $password;

    /**
     * @var string $sessionKey
     */
    protected $sessionKey;

    /**
     * @var string $wsdlAddress wsdl地址
     */
    protected $wsdlAddress;

    public function __construct($account, $password, $sessionKey, $wsdlAddress = null)
    {
        $this->account = $account;
        $this->password = $password;
        $this->sessionKey = $sessionKey;

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

    /**
     * 发送短信  (注:此方法必须为已登录状态下方可操作)
     * @param string|array $mobiles 手机号
     * @param string $content 短信内容(如果是语音验证码内容，最多不要超过6个字符，最少不要小于4个字符;字符必须为0至9的全英文半角数字字符)
     * @param DateTime|int|null $delay 延时发送
     * @param boolean $voice 语音验证码
     * @return boolean 操作结果状态码
     */
    public function send($mobiles, $content, $delay = null, $voice = false)
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
            'arg2' => $this->getSendTime($delay),
            'arg3' => (array)$mobiles,
            'arg4' => $content,
            'arg5' => '',
            'arg6' => $this->charset,
            'arg7' => 5,
            'arg8' => 8888,
        ];

        $result = $this->dispatch($voice ? 'sendVoice' : 'sendSMS', $params);

        return $result->return === 0;
    }

    /**
     * 发送语音  (注:此方法必须为已登录状态下方可操作)
     * @param string|array $mobiles 手机号
     * @param string $content 语音内容(最多不要超过6个字符，最少不要小于4个字符;字符必须为0至9的全英文半角数字字符)
     * @param DateTime|int|null $delay 延时发送
     * @return boolean 操作结果状态码
     */
    public function sendVoice($mobiles, $content, $delay = null)
    {
        return $this->send($mobiles, $content, $delay, true);
    }

    /**
     * 余额查询  (注:此方法必须为已登录状态下方可操作)
     * @return int 余额
     */
    public function getBalance()
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
        ];
        $result = $this->dispatch('getBalance', $params);

        return (int)($result->return / $this->getEachFee());
    }

    /**
     * 登录操作
     * @return int 操作结果状态码
     */
    public function login()
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
            'arg2' => $this->password,
        ];
        $result = $this->dispatch('registEx', $params);

        return $result->return;
    }

    /**
     * 注销操作  (注:此方法必须为已登录状态下方可操作)
     * @return int 操作结果状态码
     * 之前保存的sessionKey将被作废
     * 如需要，可重新login
     */
    public function logout()
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
        ];
        $result = $this->dispatch('logout', $params);

        return $result->return;
    }

    /**
     * 获取版本信息
     * @return string 版本信息
     */
    public function getVersion()
    {
        $result = $this->dispatch('getVersion');

        return $result->return;
    }

    /**
     * 取消短信转发  (注:此方法必须为已登录状态下方可操作)
     * @return int 操作结果状态码
     */
    public function cancelMOForward()
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
        ];
        $result = $this->dispatch('cancelMOForward', $params);

        return $result->return;
    }

    /**
     * 短信充值  (注:此方法必须为已登录状态下方可操作)
     * @param string $cardId [充值卡卡号]
     * @param string $cardPass [密码]
     * @return int              操作结果状态码
     *
     * 请通过亿美销售人员获取 [充值卡卡号]长度为20内 [密码]长度为6
     */
    public function chargeUp($cardId, $cardPass)
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
            'arg2' => $cardId,
            'arg3' => $cardPass,
        ];
        $result = $this->dispatch('chargeUp', $params);

        return $result->return;
    }

    /**
     * 查询单条费用  (注:此方法必须为已登录状态下方可操作)
     * @return double 单条费用
     */
    public function getEachFee()
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
        ];
        $result = $this->dispatch('getEachFee', $params);

        return $result->return;
    }

    /**
     * 得到上行短信  (注:此方法必须为已登录状态下方可操作)
     * @return array 上行短信列表, 每个元素是Mo对象, Mo对象内容参考最下面
     */
    public function getMO()
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
        ];
        $result = $this->dispatch('getMO', $params);

        $ret = [];
        if (is_array($result) && count($result) > 0) {
            if (is_array($result[0])) {
                foreach ($result as $moArray) {
                    $ret[] = (object)$moArray;
                }
            } else {
                $ret[] = (object)$result;
            }

        }

        return $ret;
    }

    /**
     * 得到状态报告  (注:此方法必须为已登录状态下方可操作)
     * @return array 状态报告列表, 一次最多取5个
     */
    public function getReport()
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
        ];
        $result = $this->dispatch('getReport', $params);

        return $result->return;
    }

    /**
     * 企业注册  [邮政编码]长度为6 其它参数长度为20以内
     *
     * @param string $eName 企业名称
     * @param string $linkMan 联系人姓名
     * @param string $phoneNum 联系电话
     * @param string $mobile 联系手机号码
     * @param string $email 联系电子邮件
     * @param string $fax 传真号码
     * @param string $address 联系地址
     * @param string $postcode 邮政编码
     *
     * @return int 操作结果状态码
     *
     */
    public function registDetailInfo($eName, $linkMan, $phoneNum, $mobile, $email, $fax, $address, $postcode)
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
            'arg2' => $eName,
            'arg3' => $linkMan,
            'arg4' => $phoneNum,
            'arg5' => $mobile,
            'arg6' => $email,
            'arg7' => $fax,
            'arg8' => $address,
            'arg9' => $postcode,
        ];
        $result = $this->dispatch('registDetailInfo', $params);

        return $result->return;
    }

    /**
     * 修改密码  (注:此方法必须为已登录状态下方可操作)
     * @param string $newPassword 新密码
     * @return int 操作结果状态码
     */
    public function updatePassword($newPassword)
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
            'arg2' => $this->password,
            'arg3' => $newPassword,
        ];
        $result = $this->dispatch('serialPwdUpd', $params);

        return $result->return;
    }

    /**
     *
     * 短信转发
     * @param string $forwardMobile 转发的手机号码
     * @return int 操作结果状态码
     *
     */
    public function setMOForward($forwardMobile)
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
            'arg2' => $forwardMobile,
        ];
        $result = $this->dispatch('setMOForward', $params);

        return $result->return;
    }

    /**
     * 短信转发扩展
     * @param string|array $forwardMobiles 转发的手机号码列表, 如 array('159xxxxxxxx','159xxxxxxxx');
     * @return int 操作结果状态码
     */
    public function setMOForwardEx($forwardMobiles)
    {
        $params = [
            'arg0' => $this->account,
            'arg1' => $this->sessionKey,
            'arg2' => is_array($forwardMobiles) ? $forwardMobiles : [$forwardMobiles],
        ];
        $result = $this->dispatch('setMOForwardEx', $params);

        return $result->return;
    }
}