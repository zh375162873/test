<?php
/*
 * 现金流水支付记录模型
 * 金额流水记录
 */
namespace Home\model;
use Think\Model;
class MoneyManageModel extends Model {
    /*
     * 记录金额支付流水
     */
	protected $fields=array("id","shop_id","buyer_id","total_money","zh_money","online_money","ac_id","type","addtime","content","_pk"=>"id"); 
	protected $_auto = array (
			array('addtime','time',1,'function'), 
	);
	
	/* 
	 * 添加金额记录
	 * 只是添加相关金额（账户金额和在线支付金额）的流水，账户金额添加
	 */
	public function addMoney(){
		
	}
   /**
    * 消费金额记录
    * @param unknown $orderinfo
    * @param unknown $type
    * @return boolean
    */
    public function delMoney($orderinfo,$type){
   	    //初始化数据
   	    $info=array(
   	        "shop_id"=>$orderinfo['shop_id'],
   	        "buyer_id"=>$orderinfo['buyer_id'],
   	        "total_money"=>$orderinfo['order_amount'],
   	        "zh_money"=>$orderinfo['ye_paymoney'],
   	        "online_money"=>$orderinfo['online_paymoney'],
   	        "ac_id"=>$orderinfo['order_id'],
   	        "type"=>$type,
   	        "addtime"=>time(),
   	        "content"=>"购买商品：".$orderinfo['goods']['goods_name']."总金额：".$orderinfo['order_amount']."账户支付：".$orderinfo['ye_paymoney']."在线支付金额".$orderinfo['online_paymoney'],
   	    );
   	    
        if($this->data($info)->add()){
            return true;
        }else{
            return false;
        }
    }
	
	/**
	 * 用户资金记录
	 * @param unknown $data
	 * @param unknown $type
	 * @param unknown $admin_name
	 * @return boolean
	 */
	public function dhm_jl($data,$type,$admin_name){
	    //初始化数据
	    $info=array(
	        "shop_id"=>$data['shop_id'],
	        "buyer_id"=>$data['buyer_id'],
	        "total_money"=>$data['order_amount'],
	        "zh_money"=>$data['ye_paymoney'],
	        "online_money"=>$data['online_paymoney'],
	        "ac_id"=>$data['order_id'],
	        "type"=>$type,
	        "addtime"=>time(),
	        "content"=>$data['content'],
	        "admin_name" => $admin_name,
	    );
	    
	    if($this->data($info)->add()){
	        return true;
	    }else{
	        return false;
	    }
	}
    
}