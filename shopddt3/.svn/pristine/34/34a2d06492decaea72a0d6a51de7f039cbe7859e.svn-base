<?php
/**
 * 抽奖活动模块
 * 涵盖订单流程和管理
 * 还有积分和现金红包充值接口
 */
namespace Home\Controller;
use BizService\PriceorderService;
use BizService\UserService;

class PriceorderController extends BaseController {
    public $orderdb,$price_orderservice,$goods_db,$users;

	public function _initialize(){
		parent::_initialize();
		//控制器初始化验证
		$this->orderdb = D("PriceOrders");
		$this->goods_db = D("Prizegoods");
		$this->users = new UserService();
		$this->price_orderservice=new \BizService\PriceOrderService();
		if(empty(session("userId"))){
			// $this->error("用户没有登录，请先登录!");
		}else{
			$this->_userid=session("userId");
		}
	}
	
    public function index(){
        echo '抽奖活动模块';
    }
    
    //积分奖品和现金红包获取充值
    /*
     * $type=1:积分 ；2：现金红包
     */
    public function add_duijiang($type=1){
    	//判断type类型
        $num = I("get.num",0);
    	if($type==1){
    		/*
    		 * 积分充值
    		 * 先获取用户账户信息，给用户积分充值
    		 * 之后进行相关流水记录
    		 */
    	    
    	    if($this->users->changeUserPoints($this->_userid,$num)){
    	        $Integral=D("LsIntegral");
    	        $arr=$Integral->add_integral_ls($num,0);
    	    }
    	}
    	elseif($type==2){
    		/*
    		 * 现金红包
    		 * 先获取用户账户信息，给用户账户充值
    		 */
    	    if($this->users->changeUserMoney($this->_userid,$num)){
    	        $Price=D("LsPrice");
    	        $arr=$Price->add_price_ls($num,2);
    	    }
    	}
    }
    
    //生成订单，需要做一个跳转，防止刷新页面生成多余订单
    //仅仅生成订单
    public function create(){
       //判断请求类型
       if(IS_GET){
       	//初始化参数
       	$goods_id=$_POST['id']?0:trim($_POST['id']);
       	$num=$_POST['num']?0:trim($_POST['num']);
       	if($goods_id&&$num){
       		$this->error('参数不全');
       	}
       	
       	//获取商品数据
       	$goods_arr=$this->goods_db->getinfobyid($goods_id,array("goods_id","goods_name","shop_id","store_id","store_name","goods_price","goods_marketprice","goods_storage","goods_image"));
       	//判断购买数量库存是否足够
       	if($num>$goods_arr['goods_storage']){
       		$this->error('商品库存不足');
       	}else{
       	    $goods_arr['goods_num']=$num;   
       	}
       	
       	
       	/* 
       	 * 计算订单金额，由于订单没有其他额外金额支付（物流），所以商品总额和订单总额一样
       	 * 首先得判断商户余额状况，如果足够就直接余额支付，如果不足够就计算看需要计算线支付金额和余额支付金额。
       	 */
        $money_info=array();
       	$money_info['order_amount']=$goods_arr['goods_price']*$num;
       	$money_info['goods_amount']=$goods_arr['goods_price']*$num;
       	//获取用户余额账户情况，计算
       	$userinfo = $this->users->userInfo($this->_userid);
       	$ye_money = $userinfo['user_money'];
       	if($money_info['order_amount']>$ye_money){
       		$money_info['ye_paymoney']=$ye_money;
       		$money_info['online_paymoney']=$money_info['order_amount']-$ye_money;
       	    $type=1;//需要在线支付
       	}else{
       		$money_info['ye_paymoney']=$ye_money;
       		$money_info['online_paymoney']=0;
       		$type=2;//纯余额支付
       	}
       	
       	
       	//提交订单
        $order_id=$this->orderdb->createorder($goods_arr,$money_info);
         if($order_id){
           	$this->redirect('priceorder/createpay', array('id'=>$order_id), 1, '页面跳转中...');
          }else{
           	$this->error('订单生成失败！');
          }
       }else{
       	    $this->error('非法请求');
       }
    }
    //订单详情页和支付页面
    public function createpay(){
    	//获取订单信息
    	$order_id=I("get.id",0);
    	$orderinfo=$this->orderdb->where(array("order_id"=>$order_id))->find();
    	
    	//处理需要支付的数据
        

        //判断支付的方式，如果金额足够就直接余额支付，不用调用第三方接口了
        if($orderinfo['online_paymoney']==0){
            $this->assign("orderinfo",$orderinfo);
            $this->display();
        }else{
        	//生成支付按钮和相关订单信息
        	$pay['alipay']=$this->price_orderservice->online_pay($orderinfo);//支付宝
        	echo $pay['alipay'];exit();
        	
        	$pay['wxpay']=$this->price_orderservice->wxpay($orderinfo,0);//微信
        	
        	//获取模板
        	$this->assign("pay",$pay);
        	$this->assign("orderinfo",$orderinfo);
        	$this->display();
        }
     }
    
    //支付成功后的回调函数
    public function return_do(){
        /*
         * 初始化数据
         * 牵扯订单号的返回和支付好的返回
         */
        
    	
        
     	//改变订单状态
     	 $order=D("PriceOrders");
     	 $order->changestatus();//改变状态,同时记录操作流程
     	 $orderinfo=$order->getinfo();
     	 
     	//生成核销码  
     	 $dhm=D("DhmManage");
     	 $data=array();//核销基本信息；里面包含数量、商城、商家、用户等等
     	 $dnm->create_dhm($data);
     	 
     	 
    }
    
    /*
     * 用户中心订单管理 
     */
    // 我的订单
    public function orderlist(){
    	
    	//订单列表
    	$order=D("Orders");
    	$arr=$order->searchorder();
    	
    }
    
    //订单详情
    public function orderinfo(){
    	
    	//订单详情
    	$order=D("Orders");
    	$order->getinfo($order_sn);
    	
    }
    
    /*
     * 退款流程开发
     */
    public function refund_money(){
    	//初始化参数 order_id
    	
    	//获取订单详情
    	$orders=D("Orders");
    	$orders->getinfo();
    	
    }
    
}