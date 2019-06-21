<?php

namespace app\common\library\wechat;
use app\common\library\wechat\lib\WxPay.Api ;
use app\common\library\wechat\WxPay.NativePay ;
use app\common\library\wechat\log ;

use app\common\model\Payconfig as PayconfigModel; 
use app\common\exception\BaseException;

/**
 * 微信支付
 * Class WxPay
 * @package app\common\library\wechat
 */
class WxPay
{
    

    /**
     * 统一下单API
     * @param $order_no
     * @param $openid
     * @param $total_fee
     * @return array
     * @throws BaseException
     */
    public function unifiedorder($order_no, $openid, $total_fee)
    {
			  /**
		*
		* example目录下为简单的支付样例，仅能用于搭建快速体验微信支付使用
		* 样例的作用仅限于指导如何使用sdk，在安全上面仅做了简单处理， 复制使用样例代码时请慎重
		* 请勿直接直接使用样例对外提供服务
		* 
		**/
/* 
		require_once "../lib/WxPay.Api.php";
		require_once "WxPay.NativePay.php";
		require_once 'log.php'; */

		//初始化日志
		$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
		$log = Log::Init($logHandler, 15);

		//模式一
		//不再提供模式一支付方式
		/**

		 * 流程：
		 * 1、组装包含支付信息的url，生成二维码
		 * 2、用户扫描二维码，进行支付
		 * 3、确定支付之后，微信服务器会回调预先配置的回调地址，在【微信开放平台-微信支付-支付配置】中进行配置
		 * 4、在接到回调通知之后，用户进行统一下单支付，并返回支付信息以完成支付（见：native_notify.php）
		 * 5、支付完成之后，微信服务器会通知支付成功
		 * 6、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
		 */

		$notify = new NativePay();
		$url1 = $notify->GetPrePayUrl("123456789");

		//模式二
		/**
		 * 流程：
		 * 1、调用统一下单，取得code_url，生成二维码
		 * 2、用户扫描二维码，进行支付
		 * 3、支付完成之后，微信服务器会通知支付成功
		 * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
		 */
		$input = new WxPayUnifiedOrder();
		$input->SetBody("test");
		$input->SetAttach("test");
		$input->SetOut_trade_no("sdkphp123456789".date("YmdHis"));
		$input->SetTotal_fee("1");
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 600));
		$input->SetGoods_tag("test");
		$input->SetNotify_url("http://paysdk.weixin.qq.com/notify.php");
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id("123456789");

		$result = $notify->GetPayUrl($input);
		$url2 = $result["code_url"];
    }

    /**
     * 支付成功异步通知
     * @param \app\task\model\Order $OrderModel
     * @throws BaseException
     * @throws \Exception
     * @throws \think\exception\DbException
     */
    public function notify($OrderModel)
    {
//        $xml = <<<EOF
//<xml><appid><![CDATA[wx62f4cad175ad0f90]]></appid>
//<attach><![CDATA[test]]></attach>
//<bank_type><![CDATA[ICBC_DEBIT]]></bank_type>
//<cash_fee><![CDATA[1]]></cash_fee>
//<fee_type><![CDATA[CNY]]></fee_type>
//<is_subscribe><![CDATA[N]]></is_subscribe>
//<mch_id><![CDATA[1499579162]]></mch_id>
//<nonce_str><![CDATA[963b42d0a71f2d160b3831321808ab79]]></nonce_str>
//<openid><![CDATA[o9coS0eYE8pigBkvSrLfdv49b8k4]]></openid>
//<out_trade_no><![CDATA[2018062448524950]]></out_trade_no>
//<result_code><![CDATA[SUCCESS]]></result_code>
//<return_code><![CDATA[SUCCESS]]></return_code>
//<sign><![CDATA[E252025255D59FE900DAFA4562C4EF5C]]></sign>
//<time_end><![CDATA[20180624122501]]></time_end>
//<total_fee>1</total_fee>
//<trade_type><![CDATA[JSAPI]]></trade_type>
//<transaction_id><![CDATA[4200000146201806242438472701]]></transaction_id>
//</xml>
//EOF;
        if (!$xml = file_get_contents('php://input')) {
            $this->returnCode(false, 'Not found DATA');
        }
        // 将服务器返回的XML数据转化为数组
        $data = $this->fromXml($xml);
        // 记录日志
        $this->doLogs($xml);
        $this->doLogs($data);
        // 订单信息
        $order = $OrderModel->payDetail($data['out_trade_no']);
        empty($order) && $this->returnCode(true, '订单不存在');
        // 小程序配置信息
        $wxConfig = WxappModel::getWxappCache($order['wxapp_id']);
        // 设置支付秘钥
        $this->config['apikey'] = $wxConfig['apikey'];
        // 保存微信服务器返回的签名sign
        $dataSign = $data['sign'];
        // sign不参与签名算法
        unset($data['sign']);
        // 生成签名
        $sign = $this->makeSign($data);
        // 判断签名是否正确  判断支付状态
        if (($sign === $dataSign)
            && ($data['return_code'] == 'SUCCESS')
            && ($data['result_code'] == 'SUCCESS')) {
            // 更新订单状态
            $order->updatePayStatus($data['transaction_id']);
            // 发送短信通知
            //$this->sendSms($order['wxapp_id'], $order['order_no']);
            // 返回状态
            $this->returnCode(true, 'OK');
        }
        // 返回状态
        $this->returnCode(false, '签名失败');
    }

    /**
     * 发送短信通知
     * @param $wxapp_id
     * @param $order_no
     * @return mixed
     * @throws \think\Exception
     */
    private function sendSms($wxapp_id, $order_no)
    {
        // 短信配置信息
        $config = SettingModel::getItem('sms', $wxapp_id);
        $SmsDriver = new SmsDriver($config);
        return $SmsDriver->sendSms('order_pay', compact('order_no'));
    }

    /**
     * 返回状态给微信服务器
     * @param bool $is_success
     * @param string $msg
     */
    private function returnCode($is_success = true, $msg = null)
    {
        $xml_post = $this->toXml([
            'return_code' => $is_success ? $msg ?: 'SUCCESS' : 'FAIL',
            'return_msg' => $is_success ? 'OK' : $msg,
        ]);
        die($xml_post);
    }

    /**
     * 写入日志记录
     * @param $values
     * @return bool|int
     */
    private function doLogs($values)
    {
        return write_log($values, __DIR__);
    }

    /**
     * 生成paySign
     * @param $nonceStr
     * @param $prepay_id
     * @param $timeStamp
     * @return string
     */
    private function makePaySign($nonceStr, $prepay_id, $timeStamp)
    {
        $data = [
            'appId' => $this->config['app_id'],
            'nonceStr' => $nonceStr,
            'package' => 'prepay_id=' . $prepay_id,
            'signType' => 'MD5',
            'timeStamp' => $timeStamp,
        ];

        //签名步骤一：按字典序排序参数
        ksort($data);

        $string = $this->toUrlParams($data);

        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $this->config['apikey'];

        //签名步骤三：MD5加密
        $string = md5($string);

        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);

        return $result;
    }

    /**
     * 将xml转为array
     * @param $xml
     * @return mixed
     */
    private function fromXml($xml)
    {
        // 禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     * 以post方式提交xml到对应的接口url
     * @param $xml
     * @param $url
     * @param int $second
     * @return mixed
     */
    private function postXmlCurl($xml, $url, $second = 30)
    {
        $ch = curl_init();
        // 设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);//严格校验
        // 设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        // 运行curl
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 生成签名
     * @param $values
     * @return string 本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    private function makeSign($values)
    {
        //签名步骤一：按字典序排序参数
        ksort($values);
        $string = $this->toUrlParams($values);
        //签名步骤二：在string后加入KEY
        $string = $string . '&key=' . $this->config['apikey'];
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     * @param $values
     * @return string
     */
    private function toUrlParams($values)
    {
        $buff = '';
        foreach ($values as $k => $v) {
            if ($k != 'sign' && $v != '' && !is_array($v)) {
                $buff .= $k . '=' . $v . '&';
            }
        }
        return trim($buff, '&');
    }

    /**
     * 输出xml字符
     * @param $values
     * @return bool|string
     */
    private function toXml($values)
    {
        if (!is_array($values)
            || count($values) <= 0
        ) {
            return false;
        }

        $xml = "<xml>";
        foreach ($values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

}
