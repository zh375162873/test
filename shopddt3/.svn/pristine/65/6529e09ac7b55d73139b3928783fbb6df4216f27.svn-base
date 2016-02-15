<?php

namespace BizService;

/**
 * 订单流程Service
 *
 * @author 王春一
 */
class PriceOrderService extends BaseService {

	
	/**
	 * 自动生成订单号
	 */
	public function create_ordersn(){
		return date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
	}
	
	function CreateExchangeCode(){
		$order_sn = substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8).sprintf('%04d',rand(0,9999));
		echo $order_sn;
	}
	
	/**
	 * 生成支付方式(支付宝支付)
	 */
	public function  online_pay($data){
	 vendor('Alipay.Corefunction');
	 vendor('Alipay.Md5function');
	 vendor('Alipay.Notify');
	 vendor('Alipay.Submit');
	//配置文件
     $alipay_config = C("ALIPAY_CONFIG");
	
     /**************************请求参数**************************/
     $payment_type = "1"; //支付类型 //必填，不能修改
     $notify_url = U("Home/PayReturn/alipay_notiy_do",array("type"=>2)); //服务器异步通知页面路径
     $return_url = U("Home/PayReturn/alipay_return_do",array("type"=>2)); //页面跳转同步通知页面路径
     $seller_email = C('ALIPAY.seller_email');//卖家支付宝帐户必填
     $out_trade_no = $data['order_sn'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
     $subject = $data['goods_name'];  //订单名称 //必填 通过支付页面的表单进行传递
     $total_fee = $data['online_paymoney'];   //付款金额  //必填 通过支付页面的表单进行传递
     $body = "购买抽奖活动商品".$data['goods_name']."在线支付".$data['online_paymoney'];  //订单描述 通过支付页面的表单进行传递
     $show_url = U("Home/Goods/info",array("goods_id"=>$data['goods_id']));  //商品展示地址 通过支付页面的表单进行传递
     $anti_phishing_key = "";//防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
     $exter_invoke_ip = get_client_ip(); //客户端的IP地址
     /************************************************************/
     
     
     
	//构造要请求的参数数组，无需改动
	 $parameter = array(
			"service" => "create_direct_pay_by_user",
			"partner" => trim($alipay_config['partner']),
			"payment_type"	=> '1',
			"notify_url"	=> $notify_url,
			"return_url"	=> $return_url,
			"seller_email"	=> $seller_email,
			"out_trade_no"	=> $out_trade_no,
			"subject"	=> $subject,
			"total_fee"	=> $total_fee,
			"body"	=> $body,
			"show_url"	=> $show_url,
			"anti_phishing_key"	=> $anti_phishing_key,
			"exter_invoke_ip"	=> $exter_invoke_ip,
			"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
	 );
   
   
	//建立请求
	 $alipaySubmit = new \AlipaySubmit($alipay_config);
	 $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "支付");
	 return $html_text;
  }
//微信支付
 public function wxpay($data){
     vendor("WxPayPubHelper.WxPayPubHelper");
      
     //使用jsapi接口
     $jsApi = new \JsApi_pub();
      
     //=========步骤1：网页授权获取用户openid============
     //通过code获得openid
     if (!isset($_GET['code']))
     {
         //触发微信返回code码
         $url = $jsApi->createOauthUrlForCode(\WxPayConf_pub::JS_API_CALL_URL);
         Header("Location: $url");
     }else
     {
         //获取code码，以获取openid
         $code = $_GET['code'];
         $jsApi->setCode($code);
         $openid = $jsApi->getOpenId();
     }
      
     //=========步骤2：使用统一支付接口，获取prepay_id============
     //使用统一支付接口
     $unifiedOrder = new \UnifiedOrder_pub();
      
     //设置统一支付接口参数
     //设置必填参数
     //appid已填,商户无需重复填写
     //mch_id已填,商户无需重复填写
     //noncestr已填,商户无需重复填写
     //spbill_create_ip已填,商户无需重复填写
     //sign已填,商户无需重复填写
     $unifiedOrder->setParameter("openid","$openid");//商品描述
     $unifiedOrder->setParameter("body","购买抽奖活动商品".$data['goods_name']."在线支付".$data['online_paymoney']);//商品描述
     //自定义订单号，此处仅作举例
     //$timeStamp = time();
     $out_trade_no = $data['order_sn'];
     $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
     $unifiedOrder->setParameter("total_fee",$data['online_paymoney']);//总金额
     $unifiedOrder->setParameter("notify_url",U("Home/PayReturn/wx_notiy_do",array("type"=>2)));//通知地址
     $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
     //非必填参数，商户可根据实际情况选填
     //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
     //$unifiedOrder->setParameter("device_info","XXXX");//设备号
     //$unifiedOrder->setParameter("attach","XXXX");//附加数据
     //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
     //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
     //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
     //$unifiedOrder->setParameter("openid","XXXX");//用户标识
     //$unifiedOrder->setParameter("product_id","XXXX");//商品ID
      
     $prepay_id = $unifiedOrder->getPrepayId();
     //=========步骤3：使用jsapi调起支付============
     $jsApi->setPrepayId($prepay_id);
      
     $jsApiParameters = $jsApi->getParameters();
     //echo $jsApiParameters;
      
     $html_info .= "<html><head><meta http-equiv='content-type' content='text/html;charset=utf-8'/><title>微信安全支付</title>";
     $html_info .= "<script type='text/javascript'>
                		//调用微信JS api 支付
                		function jsApiCall(){
                			WeixinJSBridge.invoke(
                				'getBrandWCPayRequest',
                				 ".$jsApiParameters.",
                				function(res){
                					WeixinJSBridge.log(res.err_msg);
                					//alert(res.err_code+res.err_desc+res.err_msg);});}
                		function callpay(){
                			if (typeof WeixinJSBridge == 'undefined'){
                			    if(document.addEventListener){
                			        document.addEventListener('WeixinJSBridgeReady',jsApiCall, false);
                			    }else if(document.attachEvent){
                			        document.attachEvent('WeixinJSBridgeReady',jsApiCall);
                			        document.attachEvent('onWeixinJSBridgeReady',jsApiCall);
                			    }}else{jsApiCall();}}</script></head>";
     $html_info .="<body></br></br></br></br><div align='center'>
                		<button style='width:210px; height:30px; background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;' type='button' onclick='callpay()' >贡献一下</button>
                	  </div></body></html>";
      
     return $html_info;
 }

   /*
    * 金额记录
    * &$date里包含总金额，账户金额，在线支付金额，用户id，商户id，商城id，
    * order_id,type现金流动类型,content备注内容
    * type:0：默认普通商品购买的消费
    *      1：兑奖活动商品购买的消费
    *      2：兑奖活动现金红包获取的充值
    * 
    */
  public function moneyls_add($date){
  	  $lsprice=D("LsPrice");
  	  $moneymanage=D("MoneyManage");
  	  
  	  
  	  //记录金额流水
  	  
  	  
  	  //如果金额流水牵扯账户支付后，需要记录账户流水
  	  
  	  
  }
  
  /*
   * 积分记录
   * 积分充值 商城id，商户id，用户id
   */
  public function integral_add($date){
      //增加账户积分记录
      $integral = $date['integral_num'];
      $user = D("users");
      
      $info=array(
          "shop_id" => $date['shop_id'],
          "store_id" => $date['store_id'],
          "buyer_id" => $date['buyer_id'],
          "integral_num" => $date['integral_num'],
          "order_id" => $date['order_id'],
          "type" => $date['type'],
          "content" => $date['content'],
          "addtime" => time(),  
      );
      
      $integral_ls = D("LsIntegral");
      
  }
  
  
  
  
  
  
}