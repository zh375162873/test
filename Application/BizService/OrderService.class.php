<?php

namespace BizService;
use BizService\PeriodService;
use BizService\GoodsstoreService;
use BizService\ExtendService;
use BizService\UserService;
/**
 * 订单流程Service
 *
 * @author 王春一
 */
class OrderService extends BaseService {
    
    public function __construct(){
        header("Content-type:text/html;charset=utf-8");
    }
	/**
	 * 自动生成订单号
	 */
	public function create_ordersn(){
		return date('Ymd').substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8);
	}
	
	function CreateExchangeCode(){
		$order_sn = substr(implode(NULL,array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8).sprintf('%04d',rand(0,9999));
		return  $order_sn;
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
	 
	 
	  
	 //循环出所有订单信息
	    $order_id=$data['order_id'];
	    $orderlist=M("orders")->where("parent_id=$order_id")->select();
		$orderall=array();
		//商品总价
		$goods_amount=0;
		//余额支付
		$ye_paymoney=0;
		//在线支付
		$online_paymoney=0;
		//合计支付
		$order_amount=0;
		//商品名称
		$goods_name="";
		$orderdb = D("Orders");
		foreach($orderlist as $key=>$val){
		  //调取订单商品信息
		  $orderinfo1 = $orderdb->getinfo($val['order_id']);
		  $orderall[$key]=$orderinfo1;
		  $goods_amount=$goods_amount+$orderinfo1['goods_amount'];
		  $ye_paymoney=$ye_paymoney+$orderinfo1['ye_paymoney'];
		  $online_paymoney=$online_paymoney+$orderinfo1['online_paymoney'];
		  $order_amount=$order_amount+$orderinfo1['order_amount'];
		  $goods_name=$goods_name.",".$val['goods']['goods_name'];
		}
	 
	
     /**************************请求参数**************************/
     $payment_type = "1"; //支付类型 //必填，不能修改
     $notify_url = "http://".$_SERVER['HTTP_HOST']."/index.php?m=Home&c=PayReturn&a=alipay_notiy_do"; //服务器异步通知页面路径
     $return_url = "http://".$_SERVER['HTTP_HOST']."/index.php?m=Home&c=PayReturn&a=alipay_return_do"; //页面跳转同步通知页面路径
     $seller_email = C('ALIPAY.seller_email');//卖家支付宝帐户必填
     $out_trade_no = $data['order_sn'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
     $subject =$data['goods']['goods_name'];  //订单名称 //必填 通过支付页面的表单进行传递
     $total_fee = $online_paymoney;//$data['online_paymoney'];   //付款金额  //必填 通过支付页面的表单进行传递
     $body = "购买".$goods_name."在线支付".$online_paymoney; //"购买".$data['goods_name']."在线支付".$data['online_paymoney'];  //订单描述 通过支付页面的表单进行传递
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
   
	 //echo "<pre>";print_r($parameter);exit();
	//建立请求
	 $alipaySubmit = new \AlipaySubmit($alipay_config);
	 $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "支付宝支付");
	 return $html_text;
}


 /* 
  * wap支付宝方式
  */   

// public function wap_online_pay($data){}


    //微信支付
     public function wxpay($data){
         vendor("WxPayPubHelper.WxPayPubHelper");
         $wxconfig = C("WXPAY");
        
         //使用jsapi接口
         $jsApi = new \JsApi_pub();
        
         //=========步骤1：网页授权获取用户openid============
         //通过code获得openid
         if (!isset($_GET['code']))
         {
             //触发微信返回code码
             $url = $jsApi->createOauthUrlForCode($wxconfig['JS_API_CALL_URL']);
             
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
         $unifiedOrder->setParameter("body","购买".$data['goods_name']."在线支付".$data['online_paymoney']);//商品描述
         //自定义订单号，此处仅作举例
         //$timeStamp = time();
         $out_trade_no = $data['order_sn'];
         $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
         $unifiedOrder->setParameter("total_fee",$data['online_paymoney']);//总金额
         $unifiedOrder->setParameter("notify_url",U("Home/PayReturn/wx_notiy_do",array("type"=>1)));//通知地址
         $unifiedOrder->setParameter("trade_type","NATIVE");//交易类型
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
     




  public function http($url, $params, $method = 'GET', $header = array(), $multi = false){
      $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
        );
        switch(strtoupper($method)){
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                E('不支持的请求方式！');
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($error) exit('请求发生错误：' . $error);
        
        return  $data;
    }
  
    //订单统计
    public function shangcheng_order_count($shop_id){
        $order_total_num = D("Orders")->where(array("shop_id"=>$shop_id,"order_status"=>array("neq",0)))->count();
        $order_total_money = D("Orders")->where(array("shop_id"=>$shop_id,"order_status"=>array("neq",0),"refund_state"=>0))->sum("order_amount");
        $todaytime =  strtotime(date('Y-m-d'));
        $order_today_time = D("Orders")->where(array("shop_id"=>$shop_id,"order_status"=>array("neq",0),"add_time"=>array("gt",$todaytime)))->count();
        $monthtime = $strat_time = strtotime(date("Y")."-".date("m")."-1");
        $order_month_time = D("Orders")->where(array("shop_id"=>$shop_id,"order_status"=>array("neq",0),"add_time"=>array("gt",$monthtime)))->count();
        //金额统计
        $dhm_wxf_info = D("Orders")->where(array("shop_id"=>$shop_id,"order_status"=>1,"refund_state"=>0))->sum("order_amount");
        $dhm_yxf_info = D("Orders")->where(array("shop_id"=>$shop_id,"order_status"=>2,"refund_state"=>0))->sum("order_amount");
        $wxf_money=$dhm_wxf_info;
        $yxf_money=$dhm_yxf_info;
        
        $order_tksq_num = D("Home/DhmManage")->where(array("shop_id"=>$shop_id,"status"=>0,"refund_status"=>1))->count();
        $order_ytk_num = D("Home/DhmManage")->where(array("shop_id"=>$shop_id,"status"=>0,"refund_status"=>2))->count();
        
        $date = array(
            'order_total_num' => $order_total_num,//订单总数
            'order_total_money' => $order_total_money,//订单总金额
            'order_today_time' => $order_today_time,//今日订单量
            'order_month_time' => $order_month_time,//本月订单量
            'dhm_wxf_money' => $wxf_money,//未消费订单
            'dhm_yxf_money' => $yxf_money,//已消费订单
            'order_tksq_num' => $order_tksq_num,//有退款
            'order_ytk_num' => $order_ytk_num,//退款完成
        );
        return $date;
    }
     
    //评论统计
    public function pinglun_count($shop_id){
        $pl_total = D("Home/PinglunManage")->where(array("shop_id"=>$shop_id))->count();//评论总数
        $pl_yc_total = D("Home/PinglunManage")->where(array("shop_id"=>$shop_id,"pl_status"=>0))->count();//隐藏数
        $pl_xs_total = D("Home/PinglunManage")->where(array("shop_id"=>$shop_id,"pl_status"=>1))->count();//显示数
        $data = array(
           "pl_total" => $pl_total,
           "pl_yc_total" => $pl_yc_total,
           "pl_xs_total" => $pl_xs_total,  
        );
        return $data;
    }
    
    /**
     * 用户订单的统计
     * $condition 数组 参数为：
     * @param unknown $userid array
     * @param $proxy_id渠道id
     * @param unknown $start_time
     * @param unknown $end_time
     * @return Ambigous <\Think\mixed, boolean, unknown, mixed, object>
     */
    public function user_order_count($condition){
        
        $data=array();
        if(!empty($condition['user_id'])){
        $data['buyer_id'] = intval($condition['user_id']);
        }
        if(!empty($condition['proxy_id'])){
            $shopservice = new ShopService();
            $arr=$shopservice->get_shop_info_by_proxy($condition['proxy_id']);
            $shop_id = $arr[0]['shop_id'];
            $data['shop_id'] = $shop_id;
        }
       
        if(!empty($condition['start_time'])&&!empty($condition['end_time'])){
            $data['add_time'] = array(array("gt",$condition['start_time']),array("lt",$condition['end_time']));
        }
        elseif(!empty($condition['start_time'])&&empty($condition['end_time'])){
            $data['add_time'] = array("gt",$condition['start_time']);
        }
        elseif(empty($condition['start_time'])&&!empty($condition['end_time'])){
            $data['add_time'] = array("lt",$condition['end_time']);
        }
        
        if(!empty($condition['type'])){
            //type为1订单已经消费 2订单退款
            if($condition['type']==1){
              $data['order_status'] = 2;
            }
            elseif($condition['type']==2){
              $data['refund_state'] = 1;
            }
            
        }else{
            $data['order_status'] = array("neq",0);
        }
        //$data_amount = $data;
        //$data_amount['refund_state'] = 0;//处理退款订单不计算里面

        $order_goods_total = D("Orders")->where($data)->sum("goods_num");//上皮
        $order_num = D("Orders")->where($data)->count();
        $order_money = D("Orders")->where($data)->sum("order_amount");
        
        $info['order_num'] = empty($order_num)?0:$order_num;
        $info['order_money']=empty($order_money)?0:$order_money;
        $info['order_goods_total']=empty($order_goods_total)?0:$order_goods_total;
        
        
        
        return $info;
    }
    /**
     * 统计总用户的订单数
     * @param unknown $proxy_id
     * @param unknown $start_time
     * @param unknown $end_time
     * @param unknown $ordernum 1购买一次用户统计，2购买两次用户统计,这个数字可以递增
     */
    public function count_user_order($condition){
        $where="";
        if(!empty($condition['proxy_id'])){
            $shopservice = new ShopService();
            $arr=$shopservice->get_shop_info_by_proxy($condition['proxy_id']);
            $shop_id = $arr[0]['shop_id'];
            $where .= "AND shop_id=".$shop_id;
        }
         
        if(!empty($condition['start_time'])&&!empty($condition['end_time'])){
            $where .= " AND add_time >".$condition['start_time']." AND add_time <".$condition['end_time'];
        }
        elseif(!empty($condition['start_time'])&&empty($condition['end_time'])){
            $where .= " AND add_time >".$condition['start_time'];
        }
        elseif(empty($condition['start_time'])&&!empty($condition['end_time'])){
            $where .= " AND add_time <".$condition['end_time'];
        }
        $where .=" AND order_status >0 AND refund_state = 0";

        $where_s = '';
        if(!empty($condition['ordernum'])){
            $where_s .=" AND COUNT(buyer_id) >=".$condition['ordernum'];
        }
        
        $sql = "SELECT buyer_id,COUNT(buyer_id) AS total FROM ddt_orders WHERE 1 ".$where."
               GROUP BY buyer_id HAVING 1 ".$where_s;
       
        $model = new \Think\Model();
        $arr = $model->query($sql);
        $count = count($arr);
        
        return $count;
    }
    
    /**
     * 用户订单列表
     * @param unknown $userid
     * $proxy_id渠道id
     * @return unknown
     */
    public function user_order_list($userid,$proxy_id){
        if(empty($userid)){
            return false;
        }
        
        $data = array();
        $data['buyer_id'] = intval($userid);
        if(!empty($proxy_id)){
            $shopservice = new ShopService();
            $arr=$shopservice->get_shop_info_by_proxy($proxy_id);
            $shop_id = $arr[0]['shop_id'];
            $data['shop_id'] = $shop_id;
        }
        
        $data['order_status'] = array("neq",0);
        
        $arr = D("Home/Orders")->where($data)->order("add_time DESC")->select();
        for($i=0;$i<count($arr);$i++){
            $arr[$i]['goods']=D("Home/OrdersGoods")->where(array("order_id"=>$arr[$i]['order_id'],"goods_id"=>$arr[$i]['goods_id']))->field("goods_name,goods_image,goods_price")->find();
            $arr[$i]['dhm']=D("Home/DhmManage")->where(array("order_id"=>$arr[$i]['order_id'],"goods_id"=>$arr[$i]['goods_id']))->select();
        }
        
        return $arr;
    }
   /**
    * 现金账户流水记录
    * @param unknown $date
    * @param unknown $type
    * @param unknown $admin_name
    */
    public function ls_jl($data,$type,$admin_name){
        if(D("Home/LsPrice")->ls_price_jl($data,$type,$admin_name)){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 通过推广id获取订单列表
     * @param unknown $extend_id
     */
    public function extend_order($extend_id,$goods_id){
        $map =array();
        $map['extend_id']=$extend_id;
        if($goods_id){
            $map['goods_id']=$goods_id;
        }
        
        $info = D("Orders")->where($map)->select();
        $order_num_total = count($info);
        $order_money_total = 0;
        $order_goods_total = 0;
        for($i=0;$i<$order_num_total;$i++){
            $order_money_total = $order_money_total+$info[$i]['order_amount'];
            $order_goods_total = $order_goods_total+$info[$i]['goods_num'];
            $info[$i]['goods']=D("OrdersGoods")->where(array("order_id"=>$info[$i]['order_id']))->find();
            $info[$i]['dhm']=D("DhmManage")->where(array("order_id"=>$info[$i]['order_id'],"type"=>1))->select();
        }
        $data = array();
        $data['list']=$info; 
        $data['order_num_total'] = $order_num_total;
        $data['order_money_total'] = $order_money_total;
        $data['order_goods_total'] = $order_goods_total;
        return $data;
    }
	
	
	/**
 * 取得是实物商品并且要发货的订单信息
 * @param   array     $fliter   筛选条件
 * @param   string     $fliter[‘keywords’]   订单号、商品编号、商品名称关键字、用户名和昵称、发货备注内容
 * @param   string     $fliter[‘ordername’]   排序字段
 * @param   string     $fliter[‘orderby’]   排序方向asc/desc
 * @return  array	
 */
	public function get_delivery_order_list($fliter){
	   $shop_info=get_shop_proxy();
       $shop_id = $shop_info['shop_id']?$shop_info['shop_id']:1;
	    /**
	     * 处理订单搜索数据
	     */
	   if($fliter){
	        $ordername = empty($fliter['ordername'])?"":trim($fliter['ordername']);
			$orderby = empty($fliter['ordertype'])?"":trim($fliter['ordertype']);
			$keywords =empty($fliter['keywords'])?'':trim($fliter['keywords']);
			$delivery_status =trim($fliter['delivery_status']);
			$begin_time =empty($fliter['begin_time'])?'':trim($fliter['begin_time']);
			$end_time =empty($fliter['end_time'])?'':trim($fliter['end_time']);
			$goodsclass =empty($fliter['goodsclass'])?'':trim($fliter['goodsclass']);
			$excel =empty($fliter['excel'])?0:trim($fliter['excel']);
			//排序
    	    $order_by = 'add_time DESC';
    	    if($ordername&&$orderby){
    	              $order_by = "$ordername $orderby";
    	    }
    	    $data=array();
			//关键词搜索
    	    if($keywords){
    	        //获取对应的order_id
    	        $goodselect=D("OrdersGoods")->where(array("goods_name"=>array('like','%'.$keywords.'%'),"goods_serial"=>array('like','%'.$keywords.'%'),'_logic'=>'or'))->distinct(true)->field("order_id")->select();

    	        $arr = '';
    	        for($i=0;$i<count($goodselect);$i++){
    	            $arr .= $goodselect[$i]['order_id'];
    	            if($i!=(count($goodselect)-1)){
    	                $arr .=',';
    	            }
    	        }
    	        $where['order_id']=array('in',$arr);
    	        $where['order_sn'] = array('like', '%'.$keywords.'%');
    	        $where['buyer_name'] = array('like', '%'.$keywords.'%');
                $where['buyer_nickname'] = array('like', '%'.$keywords.'%');
    	        $where['_logic'] = 'or';
    	        $data['_complex'] = $where;
    	    }
	    }
		//筛选时间
		if($begin_time&&$end_time){
    	        $data['add_time'] = array(array('egt',strtotime($begin_time)),array('elt',strtotime($end_time)),'and');
    	    }
    	    elseif($begin_time){
    	        $data['add_time'] = array('egt',strtotime($begin_time));
    	    }
    	    elseif ($end_time){
    	        $data['add_time'] = array('elt',strtotime($end_time));
    	    }
		//筛选发货状态
		if($delivery_status>=0){
		$data['delivery_status']=$delivery_status;
		}
		
		$data['is_entity']=1;
		$data['send_type']=1;
	    //订单列表
		$orders = D("Admin/Orders");
	    $orderlist = $orders->order_list($shop_id,$data,$order_by,$excel);
		return $orderlist;
	}
	
	
	
	//订单生成，单个订单生成
	public function create_order($goods_id=0,$num=0,$parent_id=0,$gift_order_id=0,$goods_code)
    {  
            //初始化参数
            $goods_id = $goods_id?$goods_id:0;
            $num = $num?$num:0;
            $goods_code = $goods_code?$goods_code:"";
			//判断此商品是否开启限时抢购
			$PeriodService = new PeriodService();
            $Period = $PeriodService->get_period_sales($goods_id);
			if($Period){
			  if($Period['shut']!=0){
			    return  -10;
			   // $this->error('购买时间错误！');
			  }
			}
            if (empty($goods_id) && empty($num)&&$goods_id&&$num) {
			   return -11;
               //$this->error('参数不全');
            }
			
            //获取商品数据
			$goods_model = new GoodsstoreService();
            $goods = $goods_model->getinfo($goods_id, array("goods_id", "goods_name", "shop_id", "store_id", "store_name", "goods_plun", "goods_price","goods_divided", "goods_promotion_price", "goods_marketprice", "goods_storage", "goods_image","virtual_limit",'is_virtual'), 1);
            $bianhao = $goods_model->getinfo($goods_id,array('goods_serial'),4);
            $goods['goods_serial']=$bianhao['goods_serial'];
            //判断限购
            if($num>$goods['virtual_limit']&&$goods['virtual_limit']>0){
               return -6;
            } 
			//数量不能为空
            if($num==0){
			   return -1;
            }
            //判断购买数量库存是否足够
            if ($num > $goods['goods_storage']) {
			   return  -2;
            } else {
                $goods['goods_num'] = $num;
            }
			
           //判断优惠码，处理优惠信息
		    $extendservice = new ExtendService(); 
            if($goods_code){
                $extend = $extendservice->checkExtendGoods($goods_id);
                if($extend>0){
                    $extend_info = $extendservice->getExtendGoods($goods_id,$goods_code);
                    //判断优惠码有效期
                    if(time()>$extend_info['begin_time']||time()<$extend_info['end_time']){
                        //判断优惠码数量
                        if($num > $extend_info['quantity']){
                            $extend_num = $extend_info['quantity'];
                        }else{
                            $extend_num = $num;
                        }
                    }else{
					return -3;
                        //$this->error("此优惠码已经过期了");
                    }
                }else{
				   return -4;
                   // $this->error("此优惠码已经没有了");
                }
            }
			
			

            //模拟
            $goods_arr['goods_id'] = $goods['goods_id'];
            $goods_arr['goods_serial']=$goods['goods_serial'];
            $goods_arr['goods_name'] = $goods['goods_name'];
            $goods_arr['shop_id'] = $goods['shop_id'];
            $goods_arr['store_id'] = $goods['store_id'];
            $goods_arr['store_name'] = $goods['store_name'];
            $goods_arr['market_price'] = $goods['goods_marketprice'];
            $goods_arr['goods_price'] = $goods['goods_price'];
            $goods_arr['goods_plun'] = $goods['goods_plun'];
            $goods_arr['goods_image'] = $goods['goods_image'];
            $goods_arr['goods_divided'] = $goods['goods_divided'];
            $goods_arr['goods_num'] = $num;

			
            /*
             * 计算订单金额，由于订单没有其他额外金额支付（物流），所以商品总额和订单总额一样
             * 首先得判断商户余额状况，如果足够就直接余额支付，如果不足够就计算看需要计算线支付金额和余额支付金额。
             */
            $money_info = array();
            $money_info['goods_amount'] = $goods_arr['goods_price'] * $num;
			

            //优惠信息处理
            if($extend_info){
			    if($extend_info['discount_type']==1){
                  $extend_price = $goods_arr['goods_price']*(1-($extend_info['discount']/100));
				}elseif($extend_info['discount_type']==2){
				  $extend_price =$goods_arr['goods_price']- $extend_info['discount_price'];
				}
                $extend_money = round($extend_price*$extend_num,2);
                $money_info['extend_id'] = $extend_info['channel_user'];
                $money_info['extend_discount'] = $extend_info['discount'];
                $money_info['extend_num'] = $extend_num;
                $money_info['order_youhui'] = $extend_money;
				//将优惠口令信息加入订单商品详情中
				$goods_arr['extend_price'] = $extend_price;
				$goods_arr['extend_code'] = $goods_code;
				$goods_arr['extend_num'] = $extend_num;
				$goods_arr['extend_id'] = $extend_num;
            }
            $money_info['order_amount'] = $goods_arr['goods_price'] * $num-$extend_money;
           
            //获取用户余额账户情况，计算
			if (!session("userId")) {
			   return  -5;
               //$this->error("用户没有登录，请先登录!");
            } else {
               $_userid = session("userId");
            }
			$users = new UserService();
            $userinfo = $users->userInfo($_userid);
            
            $ye_money = $userinfo['user_money'];
            if ($money_info['order_amount'] > $ye_money) {
                $money_info['ye_paymoney'] = $ye_money;
                $money_info['online_paymoney'] = $money_info['order_amount'] - $ye_money;
            }else{
                $money_info['ye_paymoney'] = $money_info['order_amount'];
                $money_info['online_paymoney'] = 0;
            }
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
                $http_agent = 1;
            }
            
            //提交订单
			$orderdb = D("Home/Orders");
            $orderid = $orderdb->createorder($goods_arr, $money_info);
			//增加批次父订单号
			if($parent_id==0){
			  //当前订单为父订单，将父订单id加入到订单数据中
			  M("Orders")->where("order_id=$orderid")->data(array('parent_id'=>$orderid))->save();
			}else{
			  //获取父订单id，加入到订单数据表中
			  M("Orders")->where("order_id=$orderid")->data(array('parent_id'=>$parent_id))->save();
			}
			//增加是否为实物商品
			if($goods['is_virtual']==0){
			   M("Orders")->where("order_id=$orderid")->data(array('is_entity'=>1))->save();
			}
			//判断是否为赠品订单，如果是就把父订单id加入
			if($gift_order_id>0){
			  M("Orders")->where("order_id=$orderid")->data(array('gift_order_id'=>$gift_order_id))->save();
			}
			//处理余额支付
			if($parent_id>0){
			  //获取订单此批次的订单信息
				$orderlist=M("orders")->where("parent_id=$parent_id")->select();
				$orderall=array();
				//余额支付
				$ye_paymoney=0;
				//用户id
				$userid=0;
				$orderdb = D("Home/Orders");
				foreach($orderlist as $key=>$val){
				  //调取订单商品信息
				  $orderinfo = $orderdb->getinfo($val['order_id']);
				  $ye_paymoney=$ye_paymoney+$orderinfo['ye_paymoney'];
				  $userid=$orderinfo['buyer_id'];
				}
				$users = new UserService();
				$userinfo = $users->userInfo($userid);
				//判断余额是否够支付的
				$orderinfo = $orderdb->getinfo($orderid);
				$ye=$userinfo['user_money']-$ye_paymoney+$orderinfo['ye_paymoney'];
				if($ye>0){
				    //还有余额可以支付,修改订单的余额支付和在线支付金额
					if($ye>=$orderinfo['order_amount']){
					
					}else{
					 //如果所剩余额不足以支付全部金额，需要修改下余额支付和在线支付金额
					 $ye_paymoney=$ye;
					 $online_paymoney=$orderinfo['order_amount']-$ye;
					 M("Orders")->where("order_id=$orderid")->data(array('ye_paymoney'=>$ye_paymoney,'online_paymoney'=>$online_paymoney))->save();
					}
				}
				if($ye==0){
				     $ye_paymoney=0;
					 $online_paymoney=$orderinfo['order_amount'];
					 M("Orders")->where("order_id=$orderid")->data(array('ye_paymoney'=>$ye_paymoney,'online_paymoney'=>$online_paymoney))->save(); 
				}
			  
			}
			
			
			 

            if ($orderid) {
			   return $orderid;
               //$this->redirect('order/createpay', array('id' => $orderid));
            } else {
			    return 0;
                //$this->error('订单生成失败！');
            }
    }
	
	
	/**
	 * 生成支付方式(支付宝支付)
	 */
	public function  online_pay_alipay($data,$param){
	 vendor('Alipay.Corefunction');
	 vendor('Alipay.Md5function');
	 vendor('Alipay.Notify');
	 vendor('Alipay.Submit');
	//配置文件
     $alipay_config = C("ALIPAY_CONFIG");
	 
	 //是否使用余额
	 $is_balance=isset($param['is_balance'])?$param['is_balance']:0;
	 //快递类型
	 $sendtype=isset($param['sendtype'])?$param['sendtype']:0;
	 $yunfei=0;
	 if($sendtype>0){
	   $yun= get_shipping_fee($data['order_id'],$sendtype);
	   if($yun>=0){
	     if($sendtype==1){
		 
		 }elseif($sendtype==2){
		   $yun=0-$yun;
		 }
	      $yunfei=$yun;
	   }
	 }
	 //获取用户余额账户情况，计算
			if (!session("userId")) {
               return false;
            } else {
               $_userid = session("userId");
            }
			$users = new UserService();
            $userinfo = $users->userInfo($_userid);
	 
	 
	 //循环出所有订单信息
	    $order_id=$data['order_id'];
	    $orderlist=M("orders")->where("parent_id=$order_id")->select();
		$orderall=array();
		//商品总价
		$goods_amount=0;
		//余额支付
		$ye_paymoney=0;
		//在线支付
		$online_paymoney=0;
		//合计支付
		$order_amount=0;
		//商品名称
		$goods_name="";
		$orderdb = D("Orders");
		foreach($orderlist as $key=>$val){
		  //调取订单商品信息
		  $orderinfo1 = $orderdb->getinfo($val['order_id']);
		  $orderall[$key]=$orderinfo1;
		  $goods_amount=$goods_amount+$orderinfo1['goods_amount'];
		  $ye_paymoney=$ye_paymoney+$orderinfo1['ye_paymoney'];
		  $online_paymoney=$online_paymoney+$orderinfo1['online_paymoney'];
		  $order_amount=$order_amount+$orderinfo1['order_amount'];
		  $goods_name=$orderinfo1['goods']['goods_name'].",".$goods_name;
		}
		$online_paymoney=$online_paymoney;
		
		
	 
	
     /**************************请求参数**************************/
     $payment_type = "1"; //支付类型 //必填，不能修改
     $notify_url = "http://".$_SERVER['HTTP_HOST']."/index.php?m=Home&c=PayReturn&a=alipay_notiy_do"; //服务器异步通知页面路径
     $return_url = "http://".$_SERVER['HTTP_HOST']."/index.php?m=Home&c=PayReturn&a=alipay_return_do"; //页面跳转同步通知页面路径
     $seller_email = C('ALIPAY.seller_email');//卖家支付宝帐户必填
     $out_trade_no = $data['order_sn'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
     $subject =$data['goods']['goods_name'].".....";  //订单名称 //必填 通过支付页面的表单进行传递
     $total_fee = $online_paymoney;//$data['online_paymoney'];   //付款金额  //必填 通过支付页面的表单进行传递
     $body = "购买:".$goods_name."在线支付".$online_paymoney; //"购买".$data['goods_name']."在线支付".$data['online_paymoney'];  //订单描述 通过支付页面的表单进行传递
     $show_url = "http://".$_SERVER['HTTP_HOST'].U("Home/order/createpay",array("id"=>$data['order_id']));  //商品展示地址 通过支付页面的表单进行传递
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
   
	 //echo "<pre>";print_r($parameter);exit();
	//建立请求
	 $alipaySubmit = new \AlipaySubmit($alipay_config);
	 $html_text = $alipaySubmit->buildRequestForm($parameter,"get", " ");
	 return $html_text;
}

//计算此批次的订单统计信息，
//$type  1：订单数据中的余额和在线支付金额  2:通过计算动态算出来的余额和在线支付金额

	public function order_total($order_id=0,$user_id=0,$type)
    {  
	    $orderdb = D("Orders");
		$total=array();
	    //调出并循环出此批次所有订单信息
		$orderlist=M("orders")->where("parent_id=$order_id")->select();
		//定义此批次订单信息列表
		$orderall=array();
		//商品总价
		$goods_amount=0;
		//余额支付
		$ye_paymoney=0;
		//在线支付
		$online_paymoney=0;
		//合计支付
		$order_amount=0;
		//运费或者优惠金额
		$yunfei_amount=get_shipping_fee($order_id ,$sendtype);
		//在生成订单时的优惠金额
		$order_youhui=0;
		//是否有实物商品
		$is_entity=0;
		foreach($orderlist as $key=>$val){
		  //调取订单商品信息
		  $orderinfo1 = $orderdb->getinfo($val['order_id'],$this->_userid);
		  if(!$orderinfo1){
		     //无此订单
			 return -1;
		  }
		  $orderall[$key]=$orderinfo1;
		  $goods_amount=price_format($goods_amount+$orderinfo1['goods_amount']);
		  $ye_paymoney=price_format($ye_paymoney+$orderinfo1['ye_paymoney']);
		  $online_paymoney=price_format($online_paymoney+$orderinfo1['online_paymoney']);
		  $order_amount=price_format($order_amount+$orderinfo1['order_amount']);
		  $order_youhui=price_format($order_youhui+$orderinfo1['order_youhui']);
		  if($orderinfo1['is_entity']==1){
		   $is_entity=1;
		  }
		}
		//判断是不是有实体商品，如果没有快递，不用再算快递费用
		if($is_entity==0){
		    $amount=$goods_amount-$order_youhui;
		}else{
		  //计算优惠金额和默认快递金额的订单总额
		  if($sendtype==1){
		  //商品总额+快递费用-优惠金额（优惠口令）
		     $amount=$goods_amount+$yunfei_amount-$order_youhui;
		  }elseif($sendtype==2){
		  //商品总额-自提优惠-优惠金额（优惠口令）
			  $amount=$goods_amount-$yunfei_amount-$order_youhui;
		  }
		}
		//判断优惠完成后是否小于0，如果小于0就默认为0,并且格式化金额
		if($amount<0){
		  $amount=0;
		}else{
		  $amount=price_format($amount);
		}
		$total['goods_amount']=$goods_amount;
		$total['ye_paymoney']=$ye_paymoney;
		$total['online_paymoney']=$online_paymoney;
		$total['order_amount']=$order_amount;
		$total['yunfei_amount']=$yunfei_amount;
		$total['order_youhui']=$order_youhui;
		$total['is_entity']=$is_entity;
		$total['orderall']=$orderall;
		return $total;
		
	}	
    
   //点击支付时，需要将确认的信息，写入数据库，主要是讲每个订单的余额支付和在线支付金额整理好还有会优惠后的，写入进行去
    //$param为一个费用信息数组，此数组为优惠类型和运费类型
	//$type  0:余额支付，1：在线支付
 	public function order_pay_save($order_id=0,$param=array(),$user_id=0,$type)
    {  
        //获取订单信息
        $order_id = $order_id?$order_id:0;
		//送货地址id
		$address_id=$param['address_id']?$param['address_id']:0;
		//修改保存送货类型
		$sendtype = $param['sendtype']?$param['sendtype']:0;
		//用户id
		$user_id = $user_id?$user_id:0;
//******************************数据验证***********************************************************
		//判断送货类型，是否是0,1或2
		if($sendtype==0){
		
		}elseif($sendtype==1){
		
		}elseif($sendtype==2){
		
		}else{
		   return -1;
		  //$this->error("参数有误！"); 
		}
		if(!$order_id){
		   return -2;
		  //$this->error("参数有误！"); 
		}
		//获取父订单信息
        $orderinfo = $this->orderdb->getinfo($order_id);
        if (empty($orderinfo)) {
            return -3;
			//$this->error("该订单不存在");
        }
        if ($orderinfo['order_status'] != 0) {
		    return -4;
            //$this->error("订单已经过期");
        }
		if(!$user_id){
		   return -5;
		  //$this->error("参数有误！"); 
		}
		//此批次订单信息
        $orderlist=M("orders")->where("parent_id=$order_id")->select();		
		//循环商品信息验证处理
			   foreach($$orderlist as $val){
			     //判断是否为实物商品，并且需要地址信息的
				if($sendtype==1&&$val['is_entity']==1){
				   if($address_id){
				   
				   }else{
				       return -11;
					  //$this->error("请选择配送地址！"); 
				   }
				 }
				 //判断商品信息是否正确	
			     if($val['goods_id']){
					  $goodsinfo = M("goods")->where(array('goods_id'=>$val['goods_id']))->find(); 
					  //是否有此商品
					  if($goodsinfo){
						 /* 检查商品库存 */
						 if($goodsinfo['goods_storage']<$val['goods_num']){
						   return -6;
							//$this->error($goodsinfo['goods_name']."库存不足!");
						 }
						 //检查是否存在限时抢购的商品
							$PeriodService = new PeriodService();
							$Period = $PeriodService->get_period_sales($val['goods_id']);
							if($Period){
							  if($Period['shut']!=0){
							    return -7;
							    //$this->error('购买时间错误！');
							  }
							}
					  }else{
					    return -8;
					    //$this->error("无此商品！");
					  }
				  }else{
				        return -9;
				    //$this->error("数据有误！");
				  }
			   }
		//用户信息
		$userinfo=M("users")->where("user_id=".$user_id)->find();
		if(!$userinfo){
		  return -10;
		}	   
//****************************数据处理**********************************************************			   
		$orderdb = D("Orders");
		//用户余额
		$ye_amount=price_format($userinfo['user_money']);
		//清理购物车
		$model_cart = new CartService();
		$model_cart->clearCart();
		//需要支付的金额
		$goods_amount=0;
		$amount=0;
		//运费或者优惠金额
		$yunfei_amount=get_shipping_fee($order_id ,$sendtype);
		//在生成订单时的优惠金额
		$order_youhui=0;
		//是否有实物商品
		$is_entity=0;
		$orderdb = D("Orders");
		//此处重新分配余额,将运费加入到每个订单中
		foreach($orderlist as $key=>$val){
			//订单信息
			$orderinfo1 = $orderdb->getinfo($val['order_id']);
			//此订单运费
			$yunfei=get_shipping_fee($val['order_id'] ,$sendtype,1);
			//保存运费和类型
			if($orderinfo1['is_entity']==1){
				$orderdb->where("order_id=".$val['order_id'])->data(array('send_type'=>$sendtype,'shipping_fee'=>$yunfei))->save();
			}	
			//修改余额为原总金额+上运费信息
			$amount=0;
			if($sendtype==1){
				$amount=$orderinfo1['goods_amount']+$yunfei-$orderinfo1['order_youhui'];
			}else{
				$amount=$orderinfo1['goods_amount']-$yunfei-$orderinfo1['order_youhui'];
			}
			//判断总价是否小于0
			if($amount<0){
				$amount =0;
			}
			//修改余额支付和在线支付
			$orderdb->where("order_id=".$val['order_id'])->data(array('order_amount'=>$amount,'ye_paymoney'=>$amount,'online_paymoney'=>0))->save();	
			//将地址加入到订单中	
		    if($address_id&&$orderinfo1['is_entity']==1){
			  $addressService  =new AddressService();
			  $addr= $addressService->get_address_by_id($address_id);
			  $this->orderdb->where("order_id=".$val['order_id'])->data(array('province'=>$addr['province'],'city'=>$addr['city'],'district'=>$addr['district'],'address'=>$addr['address'],'name'=>$addr['consignee'],'tel'=>$addr['tel']))->save();	
		    }
			//产品总价  
		    $goods_amount=price_format($goods_amount+$orderinfo1['goods_amount']);
		    if($orderinfo1['is_entity']==1){
		     $is_entity=1;
		    }
			//优惠金额（优惠口令）
		    $order_youhui=price_format($order_youhui+$orderinfo1['order_youhui']);
		}
		//判断是不是有实体商品，如果没有快递，不用再算快递费用
		if($is_entity==0){
		      $amount=$goods_amount-$order_youhui;
		}else{
			  //计算优惠金额和默认快递金额的订单总额
			  if($sendtype==1){
			  //商品总额+快递费用-优惠金额（优惠口令）
				 $amount=$goods_amount+$yunfei_amount-$order_youhui;
			  }elseif($sendtype==2){
			  //商品总额-自提优惠-优惠金额（优惠口令）
				  $amount=$goods_amount-$yunfei_amount-$order_youhui;
			  }
		 }  
		//判断总金额是否足以使用余额支付
		if($amount>$ye_amount){
		   return -12;
		   //$this->error("数据有误！");
		}
	}	 
}

