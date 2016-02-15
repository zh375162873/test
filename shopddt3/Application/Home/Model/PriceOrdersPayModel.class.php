<?php
/*
 * 兑换奖品订单支付模型
 */
namespace Home\model;
use Think\Model;
class PriceOrdersPayModel extends Model {
	protected $fields=array("id","pay_sn","buyer_id","content","_pk"=>"id");
	protected $_auto=array(
				
	);
	
	//添加支付订单
	public function add_payorder(){
	
	}
	
}