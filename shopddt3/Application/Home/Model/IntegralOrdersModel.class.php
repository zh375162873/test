<?php
/*
 * 积分订单主表处理
 * 关联相关积分订单商品模型，积分订单扩展模型
 * 
 */
namespace Home\model;
use BizService\IntegralOrderService;
use Think\Model\AdvModel;
use BizService\UserService;
class IntegralOrdersModel extends AdvModel {
    public $integralorder_service,$inte_orders_goods,$orderlog,$lsintegral,$users,$dhmmanage,$_userid;
    public function __construct(){
        parent::__construct();
        $this->integralorder_service=new IntegralOrderService();
        $this->inte_orders_goods=D("IntegralOrdersgoods");
        $this->orderlog=D("OrdersLog");
        $this->lsintegral=D("LsIntegral");
        $this->users=new UserService();
        $this->dhmmanage=D("DhmManage");
        //$this->_userid=I("session.userId");
        $this->_userid=1;
        //没有用户操作，需要添加对用户账户操作；
    }
	
	
	//相关字段验证
	protected $_auto = array(
			array("add_time","time",1,"function"),
			array("finish_time","time",1,"function"),
	);
	
	//生成订单处理
	public function createorder($goods_arr){
		//初始化订单数据
		    $info = $this->users->userInfo($this->_userid);
	    
		    $order_sn=$this->getorder_sn();
			$data['order_sn']=$order_sn;
			$data['shop_id']=$goods_arr['shop_id'];
			$data['store_id']=$goods_arr['store_id'];
			$data['store_name']=$goods_arr['stroe_name'];
			$data['buyer_id']=$this->_userid;//需要获取用户id
			$data['buyer_name']=$info['user_name'];
			$data['tuijian_userid']=$info['parent_id'];//可从商品中获取，或者通过用户id获取相关的推荐人信息
			$data['goods_id']=$goods_arr['goods_id'];
			$data['goods_name']=$goods_arr['goods_name'];
			$data['goods_image']=$goods_arr['goods_image'];
			$data['goods_price']=$goods_arr['goods_marketprice'];
			$data['goods_num']=$goods_arr['goods_num'];
            $data['add_time']=time();
            $data['integral_num']=$goods_arr['integral_num'];
            $data['status']=0;
            $data['integral_amount']=$goods_arr['integral_amount'];
            
           
            $this->startTrans();
            $order_id=$this->data($data)->add();
            $order_id=intval($order_id);
            if($order_id){
                $goods=array(
                    "order_id" => $order_id,
                    "shop_id" => $goods_arr['shop_id'],
                    "store_id" => $goods_arr['store_id'],
                    "buyer_id" => $goods_arr['buyer_id'],
                    "goods_id" => $goods_arr['goods_id'],
                    "goods_name" => $goods_arr['goods_name'],
                    "goods_integral" =>$goods_arr['integral_num'],
                    "market_price" => $goods_arr['goods_marketprice'],
                    "goods_num" => $goods_arr['goods_num'],
                    "goods_image" => $goods_arr['goods_image'],
                );
                //添加订单商品
                if($this->inte_orders_goods->data($goods)->add()){
                    //记录添加生成订单记录
                    $logarr=array(
                        "order_id"=>$order_id,
                        "log_msg"=>"用户username购买商品-".$goods_arr['goods_name'].",数量为".$goods_arr['goods_num'].",消费积分：".$goods_arr['integral_amount'],
                        "log_time"=>time(),
                        "log_role"=>"用户角色",//用户角色
                        "log_user"=>"1",//用户id
                        "log_orderstate"=>0,
                        "log_type"=>2,
                    );
                    
                    //记录订单操作流水
                    if($this->orderlog->add_log($logarr)){
                        //库存处理
                        $this->commit();
                        return $order_id;
                        
                    }else{
                        $this->rollback();
                    }
                }else{
                    $this->rollback();
                }
            }else{
            	    $this->rollback();
            }
	}
	
	//订单处理
	public function notiy_order($orderinfo){
	     //初始化数据
	    $zh_integral = -$orderinfo['integral_amount'];
       
	    $this->startTrans();
	     //减少用户账户积分数
	     if($this->users->changeUserPoints($this->_userid,-$orderinfo['integral_amount'])){
	         //改变订单状态
	         if($this->changestatus($orderinfo['order_id'],1)){
	             //记录积分账户流水
	             if($this->lsintegral->del_integral_ls($orderinfo)){
	                 //生成兑换码
	                 if($this->dhmmanage->create_dhm($orderinfo,2)){
	                     $this->commit();
	                     return true;
	                 }else{
	                     $this->rollback();
	                 }
	             }else{
	                 $this->rollback();
	             }
	         }else{
	             $this->rollback();
	         }
	     }else{
	         $this->rollback();
	     }
	    
	}
	
	
	
	//更改订单状态
	public function changestatus($orderid,$status){
	    if($this->where(array("order_id"=>$orderid))->data(array("status"=>$status))->save()){
	       $arr=$this->where(array("order_id"=>$orderid))->find();
	       $logarr=array(
                        "order_id"=>$arr['order_id'],
                        "log_msg"=>"用户username购买商品-".$arr['goods_name'].",数量为".$arr['goods_num'].",消费积分：".$arr['integral_amount'],
                        "log_time"=>time(),
                        "log_role"=>"用户角色",//用户角色
                        "log_user"=>"1",//用户id
                        "log_orderstate"=>$status,
                        "log_type"=>2,
                 );
	        $this->orderlog->add_log($logarr);
	        return true;
	    }else{
	        return false;
	    }
	}
	
	//订单搜索
	public function list_order($rule){
		
		
	}
	
	//获取订单号
	public function getinfo($order_id){
	    $arr=$this->where(array("order_id"=>$order_id))->find();
	    $arr['goods']=$this->inte_orders_goods->where(array("order_id"=>$order_id))->find();
	    return $arr;
	}
	
	
	//获取不重复的订单号
	public function getorder_sn(){
	    $order_sn=$this->integralorder_service->create_ordersn();
	    $arr=$this->where(array("order_sn"=>$order_sn))->find();
		if($arr){
			$this->getorder_sn();
		}else{
			return $order_sn;
		}
	}
	
}

