<?php
namespace Haozi\Qzpay\Tests;

use Haozi\Qzpay\Qzpay;
use PHPUnit\Framework\TestCase;

class QzpayTest extends TestCase
{

    public function testsubmitOrderInfo()
    {
        $config = array(
            'url'=>'https://qra.95516.com/pay/gateway',/*支付请求接口地址，无需更改 */
            'mch_id'=>'',/* 测试商户号，商户正式上线时需更改为自己的 */
            'key'=>'',  /* 测试密钥，商户需更改为自己的*/
            'version'=>'2.0',
            'sign_type'=>'MD5',
            'notify_url'=>'',//通知地址，必填项，接收平台通知的URL，
            'sub_appid'=>'',//对应公众号appid，必填
            'sub_openid'=>'',//对应公众号获取到的用户openid
        );

        $order = array(
            'out_trade_no'=>date('YmdHis').mt_rand(1000, 9999),//商户订单号
            'body'=>"124324",//商品描述
            'total_fee'=>12,//总金额单位：分
            'mch_create_ip'=>"123.12.12.123",

        );
        $q = new Qzpay($config);
        //提交订单
        $aa = $q->submitOrderInfo($order);
        $this->assertSame("0",$aa['result_code']);


    }
    public function testqueryOrder()
    {
        $config = array(
            'url'=>'https://qra.95516.com/pay/gateway',/*支付请求接口地址，无需更改 */
            'mch_id'=>'',/* 测试商户号，商户正式上线时需更改为自己的 */
            'key'=>'',  /* 测试密钥，商户需更改为自己的*/
            'version'=>'2.0',
            'sign_type'=>'MD5',
            'notify_url'=>'',//通知地址，必填项，接收平台通知的URL，
            'sub_appid'=>'',//对应公众号appid，必填
            'sub_openid'=>'',//对应公众号获取到的用户openid
        );

        $order = array(
            'out_trade_no'=>'',//商户订单号
            'transaction_id'=>"",//平台订单号

        );
        $q = new Qzpay($config);
        //查询订单
        $aa = $q->queryOrder($order);
        $this->assertSame("0",$aa['result_code']);


    }
}


