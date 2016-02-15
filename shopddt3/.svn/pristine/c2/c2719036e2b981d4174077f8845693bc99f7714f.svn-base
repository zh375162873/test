<?php

namespace BizService;

/**
 * 订单流程Service
 *
 * @author 王春一
 */
class IntegralOrderService extends BaseService {

	
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


}