# sms
亿美软通、建周、3G新传媒短信接口

## 安装

1) 打开终端执行下面命令:
```php
composer require yanthink/sms
```

2) 打开 ```config/app.php``` 然后将下面内容添加到 ```providers``` 数组中:
```php
Yanthink\Sms\SmsServiceProvider::class,
```

3) 将下面内容添加到 ```config/app.php``` 文件的 ```aliases``` 数组中:
```php
'Sms' => Yanthink\Sms\Facades\Sms::class,
```

4) 在终端执行下面命令:
```php
php artisan vendor:publish --provider="Yanthink\Sms\SmsServiceProvider"
```

## 使用

```php
    <?php
    
    namespace App\Http\Controllers;
    
    use Yanthink\Sms\Contracts\Sms;
    use Carbon\Carbon;
    
    class SmsController extends Controller
    {
        public function index(Sms $sms)
        {
            $sms->send('13000000000', '短信内容'); // 发送普通短信
            $sms->send('13000000000', '短信内容', Carbon::now()->addMinutes(10)); // 发送定时短信
            $sms->sendVoice('13000000000', '123456'); // 发送语音
            $sms->getBalance(); // 获取短信余额
        }
    }
```