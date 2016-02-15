<?php
/**
推广人员提成信息模型
*/
namespace Admin\Model;
use Think\Model;
class Commission_user_moneyModel extends Model 
{ 
  protected $tableName = 'commission_user_money'; 
  protected $fields = array(
	  'id', // id
	  'shop_id', //	商城ID
	  'referee_id', //	推广人员ID
	  'pay_title',//	结算标题
	  'commission_total',//  佣金总额
	  'pay_total',//合计支付
	  'adjustment_type',//调整类型（0加，1减）
	  'adjustment_money',//	调整金额
	  'real_pay',//	实际支付
	  'pay_desc',// 	支付说明
	  'pay_status',//	支付状态(0待结算，1已结算，2已支付)
	  'calc_time',// 	结算日期
	  'pay_time',// 	支付日期
	  '_pk'=>'id'//主键
  );

/**
 * 获取单个结算详细信息
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