<?php

namespace Yanthink\Sms\Contracts;

interface Sms
{

    /**
     * 发送短信
     * @param string|array $mobiles 手机号码
     * @param string $content 短信内容
     * @param \DateTime|int|null $delay 延时发送
     * @param bool $voice 发送语音
     * @return boolean
     */
    public function send($mobiles, $content, $delay = null, $voice = false);

    /**
     * 发送语音
     * @param string | array $mobiles 手机号码
     * @param string $content 短信内容
     * @param \DateTime|int|null $delay 延时发送
     * @return boolean
     */
    public function sendVoice($mobiles, $content, $delay = null);

    /**
     * @return mixed
     */
    public function getBalance();

}
