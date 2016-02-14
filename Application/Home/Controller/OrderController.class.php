<?php
/*
 * 普通商品订单支付和管理模块
 */
namespace Home\Controller;

use BizService\OrderService;
use Think\Crypt\Driver\Think;
use BizService\UserService;
use BizService\GoodsstoreService;
use BizService\ExtendService;
use Org\Util\Date;
use BizService\OthershareService;
use BizService\PeriodService;
use BizService\CartService;
use BizService\AddressService;
use BizService\DeliveryService;
use BizService\OrderDisputeService;

class OrderController extends BaseController
{
    public $orderdb, $goods_model, $users, $orderservice, $_userid, $refunddb, $refundreason, $pinglundb, $dhm_db,$extendservice;

    public function _initialize(){
        parent::_initialize();
        //初始化模型
        $this->orderdb = D("Orders");
        $this->refunddb = D("RefundReturn");
        $this->refundreason = D("RefundReason");
        $this->pinglundb = D("PinglunManage");
        $this->dhm_db = D("DhmManage");
        $this->goods_model = new GoodsstoreService();
        $this->users = new UserService();
        $this->orderservice = new OrderService();
        $this->extendservice = new ExtendService();

       // echo "<pre>";print_r($_SESSION);exit();
        //判断用户是否登录
       if (!session("userId")) {
            $this->error("用户没有登录，请先登录!");
        } else {
            $this->_userid = session("userId");
        }
 
        $this->assign("userid", $this->_userid); 
    }
 
    public function index()
    { 
      $other_service = new OthershareService();
      $proxy_id = 79;
      
      $arr =$other_service->dhm_shop_list($proxy_id);
      echo "<pre>";print_r($arr);exit();
      echo '我是Home模块';
    }

    //生成订单，需要做一个跳转，防止刷新页面生成多余订单
    //仅仅生成订单
    public function create()
    {
        //判断请求类型
        if (IS_POST) {
		    $OrderService = new OrderService(); 
            //初始化参数
            $goods_id = I("post.goods_id",0,"intval");
            $num = I("post.number",0,"intval");
            $goods_code = I("post.goods_code",0);
//******************************************数据验证，保证所有商品信息正常才能生成订单****************************		 			
			if($goods_id){
			     $input=json_encode(array('qd_code'=>$goods_code));
				 $goodslist[0]=array('goods_id'=>$goods_id,'goods_num'=>$num,'user_input'=>$input);
			}else{  
				 //获取购物车里的商品列表
				 $model_cart = new CartService();
				 //购物车列表
				 $cart_list = $model_cart->listCart('db', array('buyer_id' => session("userId")));
				 $goodslist=$cart_list;	
			}
			/* 检查购物车中是否有商品 */
			if($goodslist){
			
			}else{
			  $this->error("购物车中无可用商品!");
			}
			   //循环商品信息验证处理
			   foreach($goodslist as $val){
			     if($val['goods_id']&&$val['goods_num']>0){
					  $goodsinfo = M("goods")->where(array('goods_id'=>$val['goods_id']))->find();
					  //是否有此商品
					  if($goodsinfo){
					     //已下架验证
					     if($goodsinfo['goods_state']!=1){
						   $this->error($goodsinfo['goods_name']."已下架，请选择其他商品!");
					     }
						 /* 检查商品库存 */
						 if($goodsinfo['goods_storage']<$val['goods_num']){
							$this->error($goodsinfo['goods_name']."库存不足!");
						 }
						 //检查是否存在限时抢购的商品
						 $PeriodService = new PeriodService();
						 $Period = $PeriodService->get_period_sales($val['goods_id']);
						 if($Period){
							if($Period['shut']!=0){
							    $this->error('此为限时限购商品，购买时间未开始！');
							}
						 }
						 //验证是否为限购商品2016-1-16zhanghui
					     if($goodsinfo['virtual_limit']>0){  
					     //调取此用户订单，看总数是否超过限购
						   $goodsnum=M()->query("select sum(a.goods_num) as num from ddt_orders_goods as a ,ddt_orders as b  where b.order_id=a.order_id and b.buyer_id=".$this->_userid." and b.order_status>0 and  a.goods_id=".$val['goods_id']." and a.buyer_id=".$this->_userid);
						     if($goodsnum[0]['num']>$goodsinfo['virtual_limit']||$val['goods_num']>=$goodsinfo['virtual_limit']){
						       $this->error('购买数量超过限购数量');
						     }
					      } 		
					  }else{
					    $this->error("无此商品！");
					  }
				  }else{
				    if($val['goods_id']<=0){
				     echo "<script type='text/javascript'>alert('产品信息有误，请从新提交！');location.replace(document.referrer);</script>"; 
				    }
					if($val['goods_num']<=0){
					 echo "<script type='text/javascript'>alert('购买数量有误，请从新提交！');location.replace(document.referrer);</script>"; 
					}
					exit(); 
				    //$this->error("数据有误！");
				  }
			   }
//**********************************数据处理，数据已经验证正常，循环生成订单信息********************************
			//默认父订单id
			$parent_id=0;
			//赠品父订单id，暂时预留，不做
			$gift_order_id=0;
			/* 插入订单表 */
			foreach($goodslist as $val){
			  //获取购物车中的json输入数据
			  $user_input=json_decode($val['user_input'],true);
			  $goods_code="";
			  if($user_input['qd_code']){
			    $goods_code=$user_input['qd_code'];
			  }
			  $order_id=$OrderService->create_order($val['goods_id'],$val['goods_num'],$parent_id,$gift_order_id,$goods_code);
			  if($order_id&&$parent_id==0){
			    $parent_id=$order_id;   
			  }
			  if($order_id==-10){
				  $this->error('购买时间错误！');
			  }elseif($order_id==-11){
				  $this->error('参数不全');
			  }elseif($order_id==-6){
				  $this->error('购买数量超过限购数量');
			  }elseif($order_id==-1){
				  $this->error('购买数量不能为空');
			  }elseif($order_id==-2){
				  $this->error('购买数量库存不足');
			  }elseif($order_id==-3){
				  $this->error('优惠码已经过期了');
			  }elseif($order_id==-4){
				  $this->error('优惠码购买已被全部使用了');
			  }
			}
//****************************数据处理完成后跳转**************************************************************			
			//跳转到支付页面
			if ($parent_id) {
                $this->redirect('order/createpay', array('id' => $parent_id));
            } else {
                $this->error('订单生成失败！');
            }
        } else {
            $this->error('非法请求');
        }
    }
    
    
    

    //订单详情页和支付页面，并且动态计算总订单信息
    public function createpay()  
    {
    	
		//订单信息（主订单id）
		$order_id = I("get.id",0,"intval");
		//快递类型，1：快递，2：自提
		$sendtype = I("get.sendtype",1,"intval");
		//收货地址id
		$ad_id = I("get.ad_id",0,"intval");
		//是使用余额，0：使用 1：不使用
		$is_balance = I("get.is_balance",1,"intval");
        //验证是否有订单id
		if(!$order_id){
          $this->redirect("index/index");
        }
		//判断送货类型，是否是0,1或2
		if($sendtype==0){
		
		}elseif($sendtype==1){
		
		}elseif($sendtype==2){
		
		}else{
		  $this->error("参数有误！"); 
		}
		if(!$order_id){
		  $this->error("参数有误！"); 
		}
		
		//主订单信息
		$orderinfo = $this->orderdb->getinfo($order_id,$this->_userid);		
		if($orderinfo){
		   //判断此订单是否支付过了，如果支付过了不能再进入
		   if($orderinfo['order_status']>0){
		     $this->error("此订单已支付，不可重复支付！"); 
		   }
		}else{
		  $this->error("无此订单信息！"); 
		}
		//用户信息
        $userinfo = $this->users->userInfo($this->_userid);
		//用户余额
		$ye_amount=price_format($userinfo['user_money']);
		//主订单订单编号
        $params['order_sn'] = $orderinfo['order_sn'];
		//处理需要支付的数据，使用哪种支付，是微信支付还是支付宝支付
		$http_agent = 0;
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $http_agent = 1;
            $this->assign("http_agent", $http_agent);
        }else{
		    $this->assign("http_agent", 0);
		}
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
		  $orderinfo1 = $this->orderdb->getinfo($val['order_id'],$this->_userid);
		  if(!$orderinfo1){
		     $this->error("无此订单信息！"); 
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
		  //调取默认收货地址
		  $addressService  =new AddressService();
		  $addr_list = $addressService->get_address(session('userId'));
		  $address = array();
		  $i = 0;
		  if($addr_list){
			  foreach($addr_list as $addr){
			   if($addr['id']==$ad_id||$ad_id==0){
				$province = $addressService->get_region_list($addr['province'], false);
				$city = $addressService->get_region_list($addr['city'], false);
				$area = $addressService->get_region_list($addr['district'], false);
				$address[$i]['id'] = $addr['id'];
				$address[$i]['name'] = $addr['consignee'];
				$address[$i]['addr'] = "{$province['region_name']}{$city['region_name']}{$area['region_name']}{$addr['address']}";
				$address[$i]['tel'] = $addr['tel'];
				$i ++;
				}
			  }
		      $this->assign("address",$address['0']);
		  }
		}
		
		//判断优惠完成后是否小于0，如果小于0就默认为0,并且格式化金额
		if($amount<0){
		  $amount=0;
		}else{
		  $amount=price_format($amount);
		}
		
		//使用余额
		if($is_balance==1){
			//判断用户余额是否够用，如果够用，全部使用余额支付，在线支付金额设为0
			if($ye_amount>=$amount){
			   $ye_paymoney=$amount;
			   $online_paymoney=0;
			}else{
			   //如果余额不够，就计算下余额支付金额和在线支付金额
			   $am=$amount-$ye_amount;
			   $ye_paymoney=$ye_amount;
			   $online_paymoney=$am; 
			}
		 //不使用余额	
		 }elseif($is_balance==0){
		      //计算下余额支付金额和在线支付金额
			   $am=$amount;
			   $ye_paymoney=0;
			   $online_paymoney=$am; 
		 }else{
		   $this->error("参数有误！"); 
		 }
		
		//生成支付按钮和相关订单信息
		if ($http_agent == 1) {
			$pay=2;//微信
		}else{
			$pay=1;//支付宝
		}
	    //获取模板
	    $this->assign("pay", $pay);
		
		
		
		$this->assign("userinfo", $userinfo);
		$this->assign("is_entity",$is_entity);
		$this->assign("amount", $amount);
		$this->assign("orderall",$orderall);
		$this->assign("yunfei_amount",$yunfei_amount);
		$this->assign("goods_amount",$goods_amount);
		$this->assign("ye_paymoney",$ye_paymoney);
		$this->assign("online_paymoney",$online_paymoney,2);
		$this->assign("order_amount",$order_amount);
		$this->assign("order_id",$order_id);
		$this->assign("orderinfo", $orderinfo);
		$this->assign("sendtype",$sendtype);
		$this->assign("order_youhui",$order_youhui);
		$this->assign("is_balance",$is_balance);
        $this->display("createpay");
    }  
	
	
	//在线支付跳转页面，此处通过传来的支付类型，跳转到不同的支付页面，自动跳转到对应的支付界面。如果支付宝就跳到支付宝页面，微信就跳转到微信界面
	public function pay_online(){
	    //C('SHOW_PAGE_TRACE','true');
	     //父订单id
		$order_id = I("order_id",0,"intval");
		//支付类型 1：支付宝， 2：微信 
		$pay_type=I("pay_type",0,"intval");
		//选择的送货类型，1：快递  ，2：自提
		$sendtype=I("sendtype",0,"intval");
		//送货地址id
		$address_id=I("address",0,"intval");
		//是否使用余额(默认使用)
		$is_balance=I("is_balance",1,"intval");
		//判断是否有父订单id
		if(!$order_id){
            $this->redirect("index/index");
        }
//		//清理购物车(已移到支付成功后，再清除购物车)
//		$model_cart = new CartService();
//		$model_cart->clearCart();
		//用户信息
		$userinfo=M("users")->where("user_id=".$this->_userid)->find();
		//用户余额
		$ye_amount=price_format($userinfo['user_money']);
		//父订单信息
		$orderinfo = $this->orderdb->getinfo($order_id); 
		if($orderinfo){
		
		}else{
		  $this->error("无此订单信息！"); 
		}
		//**************此批次订单信息,此处进行提交支付前的统一操作出***********************
			//调出并循环出此批次所有订单信息,此处重新分配余额支付金额和在线支付金额,将确定好的运费加入到每个订单中
		$orderlist=M("orders")->where("parent_id=$order_id")->select();
		foreach($orderlist as $key=>$val){
			   //订单信息
		       $orderinfo1 = $this->orderdb->getinfo($val['order_id']);
			   //判断是否为实物商品，并且需要地址信息的
			   if($sendtype==1&&$orderinfo1['is_entity']==1){
				   if($address_id){
				   
				   }else{
				   echo "<script type='text/javascript'>alert('请选选择快递地址！');location.replace(document.referrer);</script>";exit();
					//$this->error("请选择配送地址！"); 
				   }
				}
			  //此订单运费
			  $yunfei=0; 
			  $yunfei=get_shipping_fee($val['order_id'] ,$sendtype,1);
			  //将运费和送货类型写入订单
			  if($orderinfo1['is_entity']==1){
			    $this->orderdb->where("order_id=".$val['order_id'])->data(array('send_type'=>$sendtype,'shipping_fee'=>$yunfei))->save();
			  }
			  //此单加入运费后的总金额
			  $amount=0;
			  if($sendtype==1){
				$amount=$orderinfo1['goods_amount']+$yunfei-$orderinfo1['order_youhui'];
			  }else{
				$amount=$orderinfo1['goods_amount']-$yunfei-$orderinfo1['order_youhui'];
			  }
			  //判断总价是否小于0,小于0，就默认为0，并且格式化
			  if($amount<0){
			    $amount =0;
			  }else{
			    $amount =price_format($amount);
			  }
           //使用余额			  
          if($is_balance){			  
			  //如果剩余的用户余额够，就全部使用余额支付此子订单金额
			  if($amount<=$ye_amount){
			     $this->orderdb->where("order_id=".$val['order_id'])->data(array('order_amount'=>$amount,'ye_paymoney'=>$amount,'online_paymoney'=>0))->save();	
				 $ye_amount=$ye_amount-$amount;
			  }else{
			   //如果不够支付此订单，就结算还剩多少钱可以用于余额，多少钱要在线支付
			     $ye=$ye_amount;//余额支付金额
			     $on=$amount-$ye_amount;//在线支付金额
				 $ye_amount=0;
				 $this->orderdb->where("order_id=".$val['order_id'])->data(array('order_amount'=>$amount,'ye_paymoney'=>$ye,'online_paymoney'=>$on))->save();	
		 	  }
          }else{
          //不使用余额
              $this->orderdb->where("order_id=".$val['order_id'])->data(array('order_amount'=>$amount,'ye_paymoney'=>0,'online_paymoney'=>$amount))->save();	
          }
			  //将地址加入到订单中	
			   if($address_id&&$orderinfo1['is_entity']==1){
			      $addressService  =new AddressService();
				  $addr= $addressService->get_address_by_id($address_id);
		          $this->orderdb->where("order_id=".$val['order_id'])->data(array('province'=>$addr['province'],'city'=>$addr['city'],'district'=>$addr['district'],'address'=>$addr['address'],'name'=>$addr['consignee'],'tel'=>$addr['tel']))->save();	
			   } 
		}
		//金额变动项目
		$param=array();
		//运费
		$param['sendtype']=$sendtype;  
		//是否使用余额
		$param['is_balance']=$is_balance;  
		//选择显示的支付跳转
		switch($pay_type){
		   //支付宝
		   case 1:
		    $pay['alipay'] = $this->orderservice->online_pay_alipay($orderinfo,$param);//支付宝
		    $this->assign("pay", $pay);
		    $this->display("pay_online_alipay");
			break;
		   //微信
		   case 2:	
		   	$this->redirect("Wxpay/js_api_call",array('id'=>$order_id,'sendtype'=>$sendtype));
		}	
	}


    //余额支付
    public function yezhifu()
    {
		//获取订单信息
        $order_id = I("order_id",0,"intval");
		//送货地址id
		$address_id=I("address",0,"intval");
		//修改保存送货类型
		$sendtype = I("sendtype",0,"intval");
		//判断送货类型，是否是0,1或2
		if($sendtype==0){
		
		}elseif($sendtype==1){
		
		}elseif($sendtype==2){
		
		}else{
		  $this->error("参数有误！"); 
		}
		if(!$order_id){
		  $this->error("参数有误！"); 
		}
		//获取父订单信息
        $orderinfo = $this->orderdb->getinfo($order_id);
        if (empty($orderinfo)) {
            $this->error("该订单不存在");
        }
        if ($orderinfo['order_status'] != 0) {
            $this->error("订单已经过期");
        }
	    $orderlist=M("orders")->where("parent_id=$order_id")->select();
		$orderdb = D("Orders");
		//用户信息
		$userinfo=M("users")->where("user_id=".$this->_userid)->find();
		//用户余额
		$ye_amount=price_format($userinfo['user_money']);
//		清理购物车(已移到支付成功后，再清除购物车)
//		$model_cart = new CartService();
//		$model_cart->clearCart();
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
			//判断是否为实物商品，并且需要地址信息的
			if($sendtype==1&&$orderinfo1['is_entity']==1){
			   if($address_id){
			   
			   }else{
			    echo "<script type='text/javascript'>alert('请选选择快递地址！');location.replace(document.referrer);</script>";exit();
				//$this->error("请选择配送地址！"); 
			   }
			}
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
			$orderdb->where("order_id=".$val['order_id'])->data(array('order_amount'=>$amount,'ye_paymoney'=>$amount,'online_paymoney'=>0))->save();	
			//将地址加入到订单中	
				   if($address_id&&$orderinfo1['is_entity']==1){
					  $addressService  =new AddressService();
					  $addr= $addressService->get_address_by_id($address_id);
					  $this->orderdb->where("order_id=".$val['order_id'])->data(array('province'=>$addr['province'],'city'=>$addr['city'],'district'=>$addr['district'],'address'=>$addr['address'],'name'=>$addr['consignee'],'tel'=>$addr['tel']))->save();	
				   }  
		  
		  $goods_amount=price_format($goods_amount+$orderinfo1['goods_amount']);
		  if($orderinfo1['is_entity']==1){
		   $is_entity=1;
		  }
		  $order_youhui=price_format($order_youhui+$orderinfo1['order_youhui']);
		}
		//$orderlist=M("orders")->where("parent_id=$order_id")->select();
		
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
		   $this->error("数据有误！");
		}
		
        //定义订单号。
		$parameter['out_trade_no']=$orderinfo['order_sn'];
		//支付成功后，修改订单信息
        if ($this->orderdb->notiy_order($parameter,0)) {
            $this->redirect("order/orderlist");
        } else {
            $this->error("操作失败",U("index/index"));
        }
    }


    /*
     *************用户中心订单管理********************* 
     */
    //我的订单
    public function orderlist()
    {
        //订单列表
        //$userid= $this->userdb->getid();//获取用户id
        $userid = $this->_userid;

        $page = I("post.curPage", 1);
        //$rule为筛选规则 1-支付未消费，2-已消费，3-退款单；
		$rule =0;
        if($_GET['rule']){
            $rule = I("get.rule",0);
        }else{
            $rule = I("post.rule",0);
        }
        
        $pagenum = I("post.pageNum",10);

        $arr=$this->orderdb->list_order($userid,$page,$pagenum,$rule);
        $this->assign("rule",$rule);
        $this->assign("arr",$arr['arr']);
        $this->assign("info",$arr['info']);
        if($rule == 1){
            $msg = "您没有未消费商品~";
        }elseif($rule == 2){
            $msg = "您没有消费商品~";
        }elseif($rule == 3){
            $msg = "您没有退款商品~";
        }else{
            $msg = "您未购买任何商品~";
        }
		//获取未支付的订单
		$arr_status0=$this->orderdb->list_order_status0($userid);
		$this->assign("arr_status0",$arr_status0);
        //如果有未支付的订单，就不要显示为空提示
		if($arr_status0){
		
		}else{
          $this->assign('empty','<div class="noData"><p>'.$msg.'</p><a href="'.U("home/index/index").'">去逛逛</a></div>');
		}
        $this->display("order_list");
    } 
    
    public function ajax_orderlist(){
        $userid = $this->_userid;
        $pagenum = I("post.pageNum",0,"intval");
        $curPage = I("post.curPage");
        $rule = I("post.rule");
        
        $list = $this->orderdb->ajax_list_order($userid,$curPage,$pagenum,$rule);
        
        if(empty($list)){
            $type=3;
        }else{
            for($i=0;$i<count($list);$i++){
                //判断订单支付状态
                $string = "";
                if($list[$i]['order_status']==2){
                    $string .="<div class='colorFf'>已消费";
                    if($list[$i]['evaluation_state']==0){
                        $string .="<a href=".U('order/pinglun',array('id'=>$list[$i]['order_id']))." class='pingJia'>评价</a></div>";
                    }else{
                        $string .="<a href='#' class='pingJia'>已评价</a></div>";
                    }
                }
                elseif (($list[$i]['order_status']==1)&&($list[$i]['refund_state']==3)){
                        $string .="<div class='colorFf'>退款已完成</div>";
                }
                else{
                    for($j=0;$j<count($list[$i]['dhm']);$j++){
                        if($list[$i]['dhm'][$j]['status']==0&&$list[$i]['dhm'][$j]['refund_status']==0){
                            $string .= "<div class='colorC4'>未消费";
                        }
                        elseif($list[$i]['dhm'][$j]['status']==0&&$list[$i]['dhm'][$j]['refund_status']==1){
                            $string .= "<div class='colorFf'>正在退款中";
                        }
                        /* elseif($list[$i]['dhm'][$j]['status']==0&&$list[$i]['dhm'][$j]['refund_status']==2){
                            $string .= "<div class='colorFf'>退款完成";
                        }
                        elseif($list[$i]['dhm'][$j]['status']==0&&$list[$i]['dhm'][$j]['refund_status']==3){
                            $string .= "<div class='colorFf'>退款驳回";
                        } */
                        $string .= "<span class='dhm format_code'>".$list[$i]['dhm'][$j]['dhm_code']."</span></div>";
                    }
                }
                $list[$i]['string'] = $string;
            }
            if(count($list)==$pagenum){
                $type=1;
            }else{
                $type=2;
            }
        }
         
        $data['list'] = $list;
        $data['type'] = $type;
       
        $this->ajaxReturn($data);
    }
    
    //订单详情
    public function orderinfo()
    {
        //初始化数据
		$userid = $this->_userid;
        $order_id = I("get.id",0,"intval");
        if (!$order_id) {
            $this->error("缺少参数");
        }
         
        //订单详情
        $orderinfo = $this->orderdb->getinfo($order_id,$userid);
		if(!$orderinfo){
		$this->error("无此订单信息");
		}
        $youxiaoqi = $this->goods_model->getinfo($orderinfo['goods_id'],array('start_date','end_date'),4);
		$goods=M('goods')->where("goods_id=".$orderinfo['goods']['goods_id'])->find();
		if($goods){
		//获取商家信息
          $store = get_merchant_info($goods['store_id']);
          $orderinfo['store'] = $store;
        }
		//获取物流信息
 		$DeliveryService=new DeliveryService();
  		$address_info = array('province'=>$orderinfo['province'],'city'=>$orderinfo['city'],'district'=>$orderinfo['district'],'address'=>$orderinfo['address']);
  		$orderinfo['address_all']=$DeliveryService->getAllAddress($address_info);
		$deliverinfo=$DeliveryService->getDeliveryInfo($orderinfo['delivery_id']);

		if($deliverinfo){
		  $this->assign("deliverinfo", $deliverinfo);
		}
		//距离
		if(session('lat')){
			$n_latitude = session('lat')?session('lat'):"34.26567";
            $n_longitude = session('lng')?session('lng'):"108.953435";
			$distance = getDistance($n_latitude, $n_longitude, $orderinfo['store']['latitude'], $orderinfo['store']['longitude']);
			$dis=number_format($distance/1000, 2, '.', '');
			$this->assign("dis", $dis);
		}else{
		    $this->assign("dis", 0);
		}
		//售后
		$sale_service = new OrderDisputeService();
		$sale_info = $sale_service->dispute_info($order_id);
		
        $start_time = Date("Y-m-d",$youxiaoqi['start_date']);
        $end_time = Date("Y-m-d",$youxiaoqi['end_date']);
        $orderinfo['payment_time'] = date("Y-m-d H:i:s", $orderinfo['payment_time']);

        $orderinfo['goods_price'] = sprintf("%0.2f",$orderinfo['goods_amount']/$orderinfo['goods_num']);

        $this->assign("orderinfo", $orderinfo);
        $this->assign("dhm", $orderinfo['dhm']);
        $this->assign("start_time",$start_time);
        $this->assign("end_time", $end_time);
		$this->assign("sale_info", $sale_info[0]);
        $this->display("order");
    }
     
	 
	//物流详情页面
    public function deliverinfo()
    {
        //初始化数据
        $deliver_id = I("get.id",0,"intval");
        if (!$deliver_id) {
            $this->error("缺少参数");
        }
        $DeliveryService=new DeliveryService();
		$deliverlist=$DeliveryService->getDeliverylog($deliver_id);
        $deliverinfo=$DeliveryService->getDeliveryInfo($deliver_id);
		//获取订单信息
		$order=M('orders')->where("order_id=".$deliverinfo['order_id'])->find();
		//产品信息
		$goods=M('goods')->where("goods_id=".$order['goods_id'])->find();
        $this->assign("deliverlist", $deliverlist);
		$this->assign("goods", $goods);
		$this->assign("deliverinfo", $deliverinfo);
        $this->display("deliverinfo");
    }

    /**
     * 评论模块
     */
    public function pinglun()
    {
        $order_id = I("get.id",0,"intval");
        if (!$order_id) {
            $this->error("缺少参数");
        }
        $orderinfo = $this->orderdb->getinfo($order_id);
		$condition['order_id'] = $order_id;
		$condition['buyer_id'] = session('userId');
		$row = $this->pinglundb->get_commet_info($condition);
		$this->assign("commet_info",$row);
        $this->assign("orderinfo", $orderinfo);
        $this->assign("orderid", $order_id);
        $this->assign("goodstype", 0);
        $this->display("pinglun");
    }

    public function add_pl()
    {
        $order_id = I("post.orderid",0,"intval");
        $goodstype = I("post.goodstype",0,"intval");
        $parentid = I("get.parentid",0,"intval");
        $desc = I("post.desc");
        $mc_score = I("post.score",0,"intval");

		$condition['order_id'] = $order_id;
		$condition['buyer_id'] = session('userId');
		$row = $this->pinglundb->get_commet_info($condition);
		if($row){
			$this->error("同一订单不能重复评价！");
		}
        $pl_info = array(
            "order_id" => $order_id,
            "goodstype" => $goodstype,
            "desc" => $desc,
            "mc_score" => $mc_score,
            "parentid" => $parentid,
        );
     
        if ($this->pinglundb->addplun($pl_info)) {
            $error = 1;
            $this->ajaxReturn($error);
        } else {
            $error = 2;
            $this->ajaxReturn($error);
        }
    }


    /**
     * 退款流程开发
     */
    public function refund_order()
    {
        //初始化参数 order_id
        $order_id = I("get.id", 0,"intval");
        $dhm_id = I("dhm_id", 0,"intval");
        if (!$order_id) {
            $this->error("缺少参数");
        }

        //获取订单详情
        $orderinfo = $this->orderdb->getinfo($order_id);
        $refundinfo = $this->refundreason->order("sort ASC")->select();

        $this->assign("refundinfo", $refundinfo);
        $this->assign("orderinfo", $orderinfo);
        $this->assign("dhm_id", $dhm_id);
        $this->display("refund_order");
    }

    /**
     * 提交退款申请
     */
    public function refund_tijiao()
    {
        //初始化参数
        $order_id = I("get.id",0,"intval");
        if (!$order_id) {
            $this->error("缺少参数");
        }
        $reason = I("post.reason");
        $dhm_id = I("post.dhm_id",0,"intval");

        $method = I("post.method");
        $reason_tj = array();
        $reason_tj['info'] = $reason;
        $reason_tj['dhm_id'] = $dhm_id;
        $reason_tj['method'] = $method;
        if($method !=1){
        $reason_tj['zh'] = I("post.num");
        $reason_tj['telephone'] = I("post.tel");
         } 

        if ($this->refunddb->sh_reason($order_id, $reason_tj)) {
            $this->ajaxReturn(1);
        } else {
            $this->ajaxReturn(2);
        }

    }
   /**
    * 退款取消
    */
    public function refund_qx(){
        //初始化数据
        $orderid = I("post.orderid",0,"intval");
        $dhm_id = I("post.dhm_id",0,"intval");
      
        $data=array();
        if($this->refunddb->refund_quxiao($orderid,$dhm_id)){
            $data['error']=1;
            $data['msg']="退款取消成功！";
        }else{
            $data['error']=2;
            $data['msg']="退款取消失败！";
        }
        $this->ajaxReturn($data);
    }
	
	
	 /**
    * 确认收货
    */
    public function confirmDelivery(){
        //初始化数据
        $orderid = I("get.orderid",0,"intval");
		$delivery_id = I("get.delivery_id",0,"intval");
		//此处可以添加验证，暂时省略，有时间再做。
		$DeliveryService=new DeliveryService();
		$deliverinfo=$DeliveryService->confirmDelivery($delivery_id);
        echo "<script type='text/javascript'>alert('确认收货成功！');location.replace(document.referrer);</script>";exit();
    }
	
 //商家位置页面
    public function  storemap()
    {
        //调取商家信息
        $store_id = I('store_id');
        $goods_id = I('goods_id');
        $info = get_merchant_info($store_id);
        $this->assign('info', $info);
        $this->display('storemap');
    }
}

