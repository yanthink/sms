<?php

namespace Yanthink\Sms;

use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SmsManager extends Manager
{
    protected function createEucpDriver(array $config)
    {
        return new EucpHandler($config['account'], $config['password'], $config['session_key'], $config['wsdl_address']);
    }

    protected function createJianzhouDriver(array $config)
    {
        return new JianzhouHandler($config['account'], $config['password'], $config['wsdl_address']);
    }

    protected function createXcmDriver(array $config)
    {
        return new XcmHandler($config['account'], $config['password'], $config['cpid'], $config['chid'], $config['wsdl_address']);
    }

    protected function createDriver($driver)
    {
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver);
        } else {
            $method = 'create' . Str::studly($driver) . 'Driver';

            if (method_exists($this, $method)) {
                return $this->$method($this->getConfig($driver));
            }
        }
        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }

    public function getDefaultDriver()
    {
        return $this->app['config']['sms.default'];
    }

    public function setDefaultDriver($name)
    {
        $this->app['config']['sms.default'] = $name;
    }

    protected function getConfig($name)
    {
        return $this->app['config']["sms.drivers.{$name}"];
    }

}
