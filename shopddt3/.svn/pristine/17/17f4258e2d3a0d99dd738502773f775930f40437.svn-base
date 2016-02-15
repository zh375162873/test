<?php
/**
渠道提成信息模型
*/
namespace Admin\Model;
use Think\Model;
class Commission_orderModel extends Model 
{ 
  protected $tableName = 'commission_order'; 
  protected $fields = array(
	  'id', // id
	  'shop_id', //	商城ID
	  'order_id', //	订单ID
	  'order_sn',//	订单编号
	  'channel_id',//  渠道ID
	  'referee_id',//  推广人ID
	  'buyer_id',//购买人ID
	  'order_fee',//订单总金额
	  'commission_fee',//	提成金额
	  'income_rate',//	单位百分比（%）
	  'channel_money',// 	该订单渠道提成数
	  'referee_money',//	该订单推广人员提成数
	  'user_status',// 	推广人员状态0待确认（未消费），1待结算（已消费），2已结算
	  'qd_status',// 	渠道状态0待确认（未消费），1待结算（已消费），2已结算  订单整体正常完成（订单包含的所有商品正常核销，确认收货）以后的收益才参与结算
	  'qd_calc_time1',// 	渠道待结算时间
	  'qd_calc_time2',// 	渠道已结算时间
	  'user_calc_time1',// 	推广人员待结算时间
	  'user_calc_time2',// 	推广人员已结算时间
	  'create_time',// 	订单创建时间
	  '_pk'=>'id'//主键
  );

/**
 * 获取单个订单分成详细信息
 * @param  milit   $id 
 * @param  boolean $field 查询字段
 * @return array     信息
 * @author zhanghui
 */
 public function getinfobyid($id=0,$field = true){
	/* 获取产品信息 */
	$map = array();
	if(is_numeric($id)){ //通过ID查询
		$map['goods_id'] = $id;
	} 
	return $this->field($field)->where($map)->find();
 } 
 



 



}

?>