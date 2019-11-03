<h1 align="center"> qzpay </h1>

<p align="center"> 中国银联条码支付综合前置平台</p>

#### 文档地址：
https://up.95516.com/open/openapi?code=unionpay


## Installing

```shell
$ composer require haozi/qzpay -vvv
```

## Usage

### 【微信】公众号&小程序支付
#### 提交订单：
```php
        $config = array(
            'url'=>'https://qra.95516.com/pay/gateway',//支付请求接口地址，无需更改 
            'mch_id'=>'',//商户号
            'key'=>'',  //密钥
            'version'=>'2.0',//版本
            'sign_type'=>'MD5',//加密方式
            'notify_url'=>'',//通知地址，必填项，接收平台通知的URL，
            'sub_appid'=>'',//对应公众号appid，必填
            'sub_openid'=>'',//对应公众号获取到的用户openid
        );

        $order = array(
            'out_trade_no'=>date('YmdHis').mt_rand(1000, 9999),//商户订单号
            'body'=>"124324",//商品描述
            'total_fee'=>12,//总金额 单位：分
            'mch_create_ip'=>"123.12.12.123",//ip

        );
        $q = new Qzpay($config);
        //提交订单
        $res = $q->submitOrderInfo($order);
```
#### 查询订单：
```php
        $config = array(
            'url'=>'https://qra.95516.com/pay/gateway',//支付请求接口地址，无需更改 
            'mch_id'=>'',//商户号
            'key'=>'',  //密钥
            'version'=>'2.0',//版本
            'sign_type'=>'MD5',//加密方式
            'notify_url'=>'',//通知地址，必填项，接收平台通知的URL，
            'sub_appid'=>'',//对应公众号appid，必填
            'sub_openid'=>'',//对应公众号获取到的用户openid
        );

        $order = array(
            'out_trade_no'=>"",//商户订单号
            'transaction_id'=>"",//平台订单号

        );
        $q = new Qzpay($config);
        //查询订单
        $res = $q->queryOrder($order);
```

#### 回调：
```php

        //回调地址接收信息
        $xml = file_get_contents('php://input');
        $config = array(
            'url'=>'https://qra.95516.com/pay/gateway',//支付请求接口地址，无需更改 
            'mch_id'=>'',//商户号
            'key'=>'',  //密钥
            'version'=>'2.0',//版本
            'sign_type'=>'MD5',//加密方式
            'notify_url'=>'',//通知地址，必填项，接收平台通知的URL，
            'sub_appid'=>'',//对应公众号appid，必填
            'sub_openid'=>'',//对应公众号获取到的用户openid
        );
        $q = new Qzpay($config);
        //回调
        $res = $q->callback($xml);
        //response 返回给网关信息
        echo $res;//这个地方根据不同框架，自行设计返回。

```
## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/haozi/qzpay/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/haozi/qzpay/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT