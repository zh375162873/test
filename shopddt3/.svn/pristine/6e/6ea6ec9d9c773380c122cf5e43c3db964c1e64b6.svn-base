<?php
/*
 * 积分流水记录统计模型
 * 对用户账户的积分流水记录
 */
namespace Home\model;
use Think\Model;
class LsIntegralModel extends Model {
    protected $fields=array("id","shop_id","buyer_id","integral_num","ac_id","type","content","addtime","_pk"=>"id");
    protected $_auto=array(
    		array("addtime","time",1,"function"),
    );
	
    //添加积分账户流水
    public function add_integral_ls($num,$type){
    	
    }
    
    //积分购买商品相应积分减少，家相关记录
    public function del_integral_ls($orderinfo){
    	//初始化数据
        $data=array(
    	    "shop_id" => $orderinfo['shop_id'],
            "buyer_id" => $orderinfo['buyer_id'],
            "integral_num" => $orderinfo['integral_amount'],
            "order_id" =>$orderinfo['order_id'],
            "type" => 1,
            "content" => "购买".$orderinfo['goods_num']."个商品".$orderinfo['goods_name']."消费".$orderinfo['integral_amount']."积分",   
            "addtime" => time(),
        );
        if($this->data($data)->add()){
            return true;
        }else{
            return false;
        }
    }
    
    /*
     * 积分账户流水列表
     * 积分账户的进账流水（购买商品时的积分（前期没有），活动获取积分），出账记录（积分商城的消费）
     */
    public function list_integral_ls(){
    	
    }
    
    
}