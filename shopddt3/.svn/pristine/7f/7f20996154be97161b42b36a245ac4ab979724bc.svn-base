<?php
/**
配送模型
*/
namespace Admin\Model;
use Think\Model;
class DeliveryModel extends Model 
{    // $deliveryData = array(
		// 	'delivery_sn' =>'',
		// 	'order_id' =>0,
		// 	'add_time' =>0,
		// 	'status' =>0,
		// 	'shipping_id' =>0,
		// 	'shipping_name' =>'',
		// 	'user_id' =>0,
		// 	'action_user' =>'',
		// 	'consignee' =>'',
		// 	'tel' =>'',
		// 	'address' =>'',
		// 	'province' =>0,
		// 	'city' =>0,
		// 	'district' =>0,
		// 	'remark' =>'',
		// 	'email' =>'',
		// 	'shipping_fee' =>0,
		// 	'update_time' =>0,
		// );

	public function addMission($deliveryData){
		$data = $this->data($deliveryData)->add();
		return $data;
	}
	public function getDeliveryInfo($deliveryId){
		if(!$deliveryId)
			return false;
		$data = $this-> where('delivery_id='.$deliveryId) ->find();
		return $data;
	}
	public function confirmDelivery($deliveryId,$deliveryData){
		if(empty($deliveryId)||empty($deliveryData))
			return false;
		$data = $this-> where('status=0 and delivery_id='.$deliveryId)->save($deliveryData);
		return $data;
	}

}