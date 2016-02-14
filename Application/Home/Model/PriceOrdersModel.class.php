<?php
/*
 * 兑换奖品订单主表处理
 * 关联相关兑换奖品订单商品模型，兑换奖品订单扩展模型，兑换奖品订单支付模型
 * 
 */
namespace Home\model;
use BizService\PriceOrderService;
use Think\Model\AdvModel;
use BizService\UserService;
use BizService\GoodsstoreService;
class PriceOrdersModel extends AdvModel {
    public $ordergoods,$orderlog,$orderpay,$lsprice,$dhmMange,$price_orderservice,$moneymanage,$users,$goodservice;
    public function __construct(){
        parent::__construct();
        $this->ordergoods=D("PriceOrdersGoods");
        $this->orderlog=D("OrdersLog");
        $this->orderpay=D("PriceOrdersPay");
        $this->lsprice=D("LsPrice");//现金账户流水记录
        $this->moneymanage=D("MoneyManage");//现金流水记录
        $this->dhmMange=D("DhmManage");
        $this->price_orderservice=new PriceOrderService();
        $this->users = new UserService();
        $this->goodservice = new GoodsstoreService();
        $this->_userid=I("session.userId");
    }    
	
	//相关字段验证
	protected $_auto = array(
			array("add_time","time",1,"function"),
			array("payment_time","time",1,"function"),
			array("finnshed_time","time",1,"function"),
	);
	
	/*
	 * 生成订单处理
	 * 生成兑奖活动订单，同时记录订单商品
	 */
	public function createorder(){
		//初始化订单数据
		    $data=array();
			$data['order_sn']=$this->createorder_sn();
			$data['shop_id']=$goods_arr['shop_id'];
			$data['store_id']=$goods_arr['store_id'];
			$data['store_name']=$goods_arr['store_name'];
			$data['buyer_id']=1;
			$data['buyer_name']=$_SESSION["username"];
			$data['tuijian_id']=$goods_arr['tuijian_id'];//可从商品中获取，或者通过用户id获取相关的推荐人信息
            $data['add_time']=time();
            $data['goods_amount']=$money_info['goods_amount'];
            $data['order_amount']=$money_info['order_amount'];
            $data['ye_paymoney']=$money_info['ye_paymoney'];
            $data['online_paymoney']=$money_info['online_paymoney'];
            $data['goods_num']=$goods_arr['goods_num'];
            $data['evaluation_state']=0;
            $data['order_status']=0;
            
            $this->startTrans();
            $result=$this->data($data)->add();
			if($result){
			    //添加订单商品
			    $goodsdate=array(
			        "order_id" => intval($result),
			        "goods_id" => $goods_arr['goods_id'],
			        "goods_name" => $goods_arr['goods_name'],
			        "goods_plun" => $goods_arr['goods_plun'],
			        "market_price"=> $goods_arr['market_price'],
			        "goods_price" => $goods_arr['goods_price'],
			        "goods_num" => $goods_arr['goods_num'],
			        "goods_image" => $goods_arr['goods_image'],
			        "shop_id" => $goods_arr['shop_id'],
			        "store_id" => $goods_arr['store_id'],
			        "buyer_id" => 1,
			    );
			    if($this->ordergoods->data($goodsdate)->add()){
			        //记录添加生成操作订单记录
			        $logarr=array(
			            "order_id"=>intval($result),
			            "log_msg"=>"用户username购买商品-".$goods_arr['goods_name'].",数量为".$goods_arr['goods_num'],
			            "log_time"=>time(),
			            "log_role"=>"用户角色",//用户角色
			            "log_user"=>"用户id",//用户id
			            "log_orderstate"=>0,
			            "log_type"=>1,
			        );
			        if($this->orderlog->add_log($logarr)){//记录订单流水
			            //减少上架商品数量（需要张辉给我提供一个方法，减少一个库存后做相应数据处理）
			            if($this->goodservice->changestoragebyid($goods_arr['goods_id'],$goods_arr['goods_num'],$type=3)){
			                $this->commit();
			                return intval($result);
			            }else {
			                $this->rollback();
			            }
			        }else{
			            $this->rollback();//事务回滚
			        }
			    }else{
			        $this->rollback();//事务回滚
			    }
			}else{
				$this->rollback();
			}
	}
	
	
	/*
	 * 支付成功后的订单相关处理
	 */
	public function notiy_order($parameter){
	    //改变订单状态
	    $order_sn = $parameter['out_trade_no'];
	    $info = $this->where(array("order_sn"=>$order_sn))->find();
	    $payinfo = array(
	        "pay_sn" => $parameter['trade_sn'],
	        "buyer_id" => $info['buyer_id'],
	        "buyer_eamil" => $parameter['buyer_email'],
	        "out_trade_no" => $parameter['out_trade_no'],
	        "addtime" => time(),
	        "pay_type" => 1,
	    );
	    
	    
	    
	    $this->startTrans();
	    //改变状态,同时记录操作流程
	    //如果有余额就要先进行用户余额处理
	    if($info['ye_paymoney']>0)
	    {
	        $ye_paymoney = -$info['ye_paymoney'];
	        if(!$this->users->changeUserMoney($this->_userid,$ye_paymoney)){
	            $this->rollback();
	        }
	    }
	    //**记录支付宝相关返回信息，为退款做准备**
	    if($this->changestatus($order_sn, 1)&&$this->where(array("order_sn"=>$order_sn))->data(array("pay_sn"=>$parameter['trade_sn']))->save()&&$this->orderpay->data($payinfo)->add()){
	        $orderinfo=$this->where(array("order_sn"=>$order_sn))->find();
	        //生成核销码
	        if($this->dhmMange->create_dhm($orderinfo,3)){
	                //账户现金流水记录，现金流水记录
	           if($this->lsprice->del_price_ls($orderinfo,1)&&$this->moneymanage->delMoney($orderinfo,1)){
	               $this->commit();
	               return true;
	             }else{
	               $this->rollback();
	             }
	        }else {
	            $this->rollback();
	        }
	    }else{
	        $this->rollback();
	    }
	}
	
	
	

	/*
	 * 订单列表
	 */
	public function list_order(){
		
	}
	
	//判断订单号是否重复
	public function createorder_sn(){
	    $order_sn = $this->price_orderservice->create_ordersn();
		$arr=$this->where(array("order_sn"=>$order_sn))->find();
		if($arr){
				$this->createorder_sn();
		}else{
			return $order_sn;
		}
	}
	//更改订单状态
	public function changestatus($order_sn,$status){
	    if($this->where(array("order_sn"=>$order_sn))->data(array("order_status"=>$status))->save()){
	        $arr=$this->where(array("order_id"=>$orderid))->find();
	        $logarr=array(
	            "order_id"=>$arr['order_id'],
	            "log_msg"=>"用户username购买商品-".$arr['goods_name'].",数量为".$arr['goods_num'].",消费积分：".$arr['integral_amount'],
	            "log_time"=>time(),
	            "log_role"=>"用户角色",//用户角色
	            "log_user"=>"用户id",//用户id
	            "log_orderstate"=>$status,
	            "log_type"=>3,
	        );
	        $this->orderlog->add_log($logarr);
	        return true;
	    }else{
	        return false;
	    }
	}
	
	
	
}