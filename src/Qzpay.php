<?php
namespace Haozi\Qzpay;


use Haozi\Qzpay\Exceptions\BaseException;
use Haozi\Qzpay\Exceptions\HttpException;
use Haozi\Qzpay\Exceptions\InvalidArgumentException;

class Qzpay
{
    private $resHandler = null;
    private $reqHandler = null;
    private $pay = null;
    private $config = null;
    public function __construct(array $config)
    {

        //验证参数

        $this->config = $config;
        $this->resHandler = new ClientResponseHandler();
        $this->reqHandler = new RequestHandler();
        $this->pay = new PayHttpClient();

        $this->reqHandler->setGateUrl($this->config['url']);

        $sign_type = $this->config['sign_type'];

        if ($sign_type == 'MD5') {
            $this->reqHandler->setKey($this->config['key']);
            $this->resHandler->setKey($this->config['key']);
            $this->reqHandler->setSignType($sign_type);
        } else if ($sign_type == 'RSA_1_1' || $sign_type == 'RSA_1_256') {
            $this->reqHandler->setRSAKey($this->config['private_rsa_key']);
            $this->resHandler->setRSAKey($this->config['public_rsa_key']);
            $this->reqHandler->setSignType($sign_type);
        }
    }

    /**
     * 提交订单信息
     * @param $order
     * @return string
     * @throws BaseException
     * @throws HttpException
     */
    public function submitOrderInfo($order){
        $this->reqHandler->setReqParams($order);
        $this->reqHandler->setParameter('service','pay.weixin.jspay');//接口类型：pay.weixin.jspay
        $this->reqHandler->setParameter('mch_id',$this->config['mch_id']);//必填项，商户号，由平台分配
        $this->reqHandler->setParameter('version',$this->config['version']);
        $this->reqHandler->setParameter('sign_type',$this->config['sign_type']);
        $this->reqHandler->setParameter('notify_url',$this->config['notify_url']);//通知地址，必填项，接收平台通知的URL，保证外网能正常访问到
        $this->reqHandler->setParameter('sub_appid',$this->config['sub_appid']);//对应公众号appid，必填
        $this->reqHandler->setParameter('sub_openid',$this->config['sub_openid']);//对应公众号获取到的用户openid，必填(使用微信官方网页授权接口获取地址：            https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140842&token=&lang=zh_CN )
        $this->reqHandler->setParameter('is_raw','1');
        $this->reqHandler->setParameter('nonce_str',mt_rand());//随机字符串，必填项，不长于 32 位
        $this->reqHandler->createSign();//创建签名

        $data = Utils::toXml($this->reqHandler->getAllParameters());
        //var_dump($data);
        $this->pay->setReqContent($this->reqHandler->getGateURL(),$data);

        try {
            $this->pay->call();
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
        $this->resHandler->setContent($this->pay->getResContent());
        $this->resHandler->setKey($this->reqHandler->getKey());
        $res = $this->resHandler->getAllParameters();

        if($this->resHandler->isTenpaySign()){
            if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
                //当返回状态与业务结果都为0时继续判断
                //echo json_encode(array('status'=>200,'data'=>$res));

                return $res;
                //echo json_encode(array('pay_info'=>$this->resHandler->getParameter('pay_info')));
                //exit();
            }else{

                throw new BaseException($this->resHandler->getParameter('err_msg'),500);

            }
        }else{
            throw new BaseException($this->resHandler->getParameter('message'),500);

        }

    }

    /**
     * 查询订单
     */
    public function queryOrder($order){
        $this->reqHandler->setReqParams($order);
        $reqParam = $this->reqHandler->getAllParameters();
        if(empty($reqParam['transaction_id']) && empty($reqParam['out_trade_no'])){

            throw new InvalidArgumentException('请输入商户订单号,平台订单号!',500);

        }
        $this->reqHandler->setParameter('version',$this->config['version']);
        $this->reqHandler->setParameter('service','unified.trade.query');//接口类型：unified.trade.query
        $this->reqHandler->setParameter('mch_id',$this->config['mch_id']);//必填项，商户号，由平台分配
        $this->reqHandler->setParameter('sign_type',$this->config['sign_type']);
        $this->reqHandler->setParameter('nonce_str',mt_rand());//随机字符串，必填项，不长于 32 位
        $this->reqHandler->createSign();//创建签名
        $data = Utils::toXml($this->reqHandler->getAllParameters());

        $this->pay->setReqContent($this->reqHandler->getGateURL(),$data);

        try {
            $this->pay->call();
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
        $this->resHandler->setContent($this->pay->getResContent());
        $this->resHandler->setKey($this->reqHandler->getKey());
        if($this->resHandler->isTenpaySign()){
            $res = $this->resHandler->getAllParameters();
            //支付成功会输出更多参数，详情请查看文档中的7.1.4返回结果
            return $res;
        }else{
            throw new BaseException($this->resHandler->getParameter('message'),$this->resHandler->getParameter('status'));
        }
    }


    /**
     * 提交退款
     */
    public function submitRefund($order){
        $this->reqHandler->setReqParams($order);
        $reqParam = $this->reqHandler->getAllParameters();
        if(empty($reqParam['transaction_id']) && empty($reqParam['out_trade_no'])){
            throw new InvalidArgumentException('请输入商户订单号,平台订单号!',500);


        }
        $this->reqHandler->setParameter('version',$this->config['version']);
        $this->reqHandler->setParameter('service','unified.trade.refund');//接口类型：unified.trade.refund
        $this->reqHandler->setParameter('mch_id',$this->config['mch_id']);//必填项，商户号，由平台分配
        $this->reqHandler->setParameter('nonce_str',mt_rand());//随机字符串，必填项，不长于 32 位
        $this->reqHandler->setParameter('sign_type',$this->config['sign_type']);
        $this->reqHandler->setParameter('op_user_id',$this->config['mch_id']);//必填项，操作员帐号,默认为商户号

        $this->reqHandler->createSign();//创建签名
        $data = Utils::toXml($this->reqHandler->getAllParameters());//将提交参数转为xml，目前接口参数也只支持XML方式

        $this->pay->setReqContent($this->reqHandler->getGateURL(),$data);

        try {
            $this->pay->call();
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }

        $this->resHandler->setContent($this->pay->getResContent());
        $this->resHandler->setKey($this->reqHandler->getKey());


        if($this->resHandler->isTenpaySign()){
            //当返回状态与业务结果都为0时才返回支付二维码，其它结果请查看接口文档
            if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
                /*$res = array('transaction_id'=>$this->resHandler->getParameter('transaction_id'),
                             'out_trade_no'=>$this->resHandler->getParameter('out_trade_no'),
                             'out_refund_no'=>$this->resHandler->getParameter('out_refund_no'),
                             'refund_id'=>$this->resHandler->getParameter('refund_id'),
                             'refund_channel'=>$this->resHandler->getParameter('refund_channel'),
                             'refund_fee'=>$this->resHandler->getParameter('refund_fee'),
                             'coupon_refund_fee'=>$this->resHandler->getParameter('coupon_refund_fee'));*/
                $res = $this->resHandler->getAllParameters();
                return $res;
            }else{
                throw new BaseException($this->resHandler->getParameter('err_msg'),$this->resHandler->getParameter('err_code'));

            }
        }else{
            throw new BaseException($this->resHandler->getParameter('message'),$this->resHandler->getParameter('status'));

        }
    }

    /**
     * 查询退款
     */
    public function queryRefund($order){
        $this->reqHandler->setReqParams($order);
        if(count($this->reqHandler->getAllParameters()) === 0){
            throw new InvalidArgumentException('请输入商户订单号,平台订单号,商户退款单号,平台退款单号!',500);
        }
        $this->reqHandler->setParameter('version',$this->config['version']);
        $this->reqHandler->setParameter('service','unified.trade.refundquery');//接口类型：unified.trade.refundquery
        $this->reqHandler->setParameter('mch_id',$this->config['mch_id']);//必填项，商户号，由平台分配
        $this->reqHandler->setParameter('sign_type',$this->config['sign_type']);
        $this->reqHandler->setParameter('nonce_str',mt_rand());//随机字符串，必填项，不长于 32 位

        $this->reqHandler->createSign();//创建签名
        $data = Utils::toXml($this->reqHandler->getAllParameters());//将提交参数转为xml，目前接口参数也只支持XML方式

        $this->pay->setReqContent($this->reqHandler->getGateURL(),$data);//设置请求地址与请求参数
        try {
            $this->pay->call();
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
        $this->resHandler->setContent($this->pay->getResContent());
        $this->resHandler->setKey($this->reqHandler->getKey());
        if($this->resHandler->isTenpaySign()){
            //当返回状态与业务结果都为0时才返回支付二维码，其它结果请查看接口文档
            if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){

                $res = $this->resHandler->getAllParameters();
                return $res;
            }else{
                throw new BaseException($this->resHandler->getParameter('err_code'),500);

            }
        }else{
            throw new BaseException($this->resHandler->getContent(),500);
        }

    }

    /**
     * 提供给平台的回调方法
     */
    public function callback($xml){
        $this->resHandler->setContent($xml);
        //var_dump($this->resHandler->setContent($xml));
        $this->resHandler->setKey($this->config['key']);
        if($this->resHandler->isTenpaySign()){
            if($this->resHandler->getParameter('status') == 0 && $this->resHandler->getParameter('result_code') == 0){
                //echo $this->resHandler->getParameter('status');
                // 11;
                //更改订单状态
                return $this->resHandler->getAllParameters();
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}
