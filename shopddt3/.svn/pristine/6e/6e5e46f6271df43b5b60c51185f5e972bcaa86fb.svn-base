<?php

namespace BizService;
use BizService\AddressService;
use BizService\BaseService;
/**
 * 配货Service
 *
 * @author 赵星
 */
class DeliveryService extends BaseService {

	public function addDeliveryMission($deliveryAdd){

		$orderInfo = D('Admin/Orders')->getinfo($deliveryAdd['orderId']);
		// var_dump($orderInfo);exit;
		$temp = $orderInfo['info'];
		$deliveryData = array(
			'delivery_sn' =>$deliveryAdd['deliverySn'],
			'order_id' =>$temp['order_id'],
			'add_time' =>time(),
			'status' =>0,
			'shipping_id' =>0,
			'shipping_name' =>$deliveryAdd['deliveryType'],
			'user_id' =>$temp['buyer_id'],
			'action_user' =>'',
			'consignee' =>$temp['name']?$temp['name']:'空',
			'tel' =>$temp['tel']?$temp['tel']:'空',
			'address' =>$temp['address']?$temp['address']:'空',
			'province' =>$temp['province'],
			'city' =>$temp['city'],
			'district' =>$temp['district'],
			'remark' =>$deliveryAdd['deliveryRemark'],
			'email' =>'',
			'shipping_fee' =>0,
			'update_time' =>time(),
		);
		D('Admin/Delivery')->startTrans();
		D('Admin/Orders')->startTrans();
		$data1 = D('Admin/Delivery')->addMission($deliveryData);
		$orderData = array(
			'delivery_id' =>$data1,
			'delivery_status' =>1,
			'delivery_time' =>time()
			);
		$data2 = D('Admin/Orders')-> where('order_id='.$temp['order_id'])->save($orderData);
		$data3 = $this->updateDeliveryInfo($data1,'已发货');
		if($data1&&$data2&&$data3){
			D('Admin/Delivery')->commit();
			D('Admin/Orders')->commit();
			echo json_encode(array('err'=>0,'msg'=>'发货成功!'));
		}else{
			D('Admin/Delivery')->rollback();
			D('Admin/Orders')->rollback();
			echo json_encode(array('err'=>1,'msg'=>'发货失败!'));
		}
		exit();
	}
	public function confirmDelivery($deliveryId){
		if(!$deliveryId){
			return false;
		}
		$deliveryData = array(
			'status' =>1,
			'update_time' =>time()
			);
		$data1 = D('Admin/Delivery')->confirmDelivery($deliveryId,$deliveryData);
		$orderData = array(
			'delivery_status' =>2,
			'order_status' =>2,
			'confirm_time' =>time()
			);
		$data2 = D('Admin/Orders')-> where('delivery_status=1 and delivery_id='.$deliveryId)->save($orderData);
		$data3 = $this->updateDeliveryInfo($deliveryId,'已收货',1);
		// var_dump($data.'+'.$data2.'+'.$data3);exit;
		if($data1&&$data2&&$data3){
			return true;
		}else{
			return false;
		}
		
	}
	public function getDeliveryInfo($deliveryId){
		$data = D('Admin/Delivery')->getDeliveryInfo($deliveryId);
		$data['add_time'] = date("Y-m-d H:i",$data['add_time']);
		$data['update_time'] = date("Y-m-d H:i",$data['update_time']);
		$data['address_all'] = $this->getAllAddress($data);
		return $data;
	}
	public function getDeliverylog($deliveryId){
		$data = D('Admin/DeliveryLog')->getDeliveryLog($deliveryId);
		foreach ($data as $key => $value) {
			$data[$key]['add_time'] = date("Y-m-d H:i",$value['add_time']);
		}
		return $data;
	}
	public function updateDeliveryInfo($deliveryId,$infoText,$is_user=0){
		$deliveryUpdate = array(
			'delivery_id' =>$deliveryId,
			'add_time' =>time(),
			'log_text' => $infoText,
			'action_user' =>'admin'
		);
		if($is_user){
			$deliveryUpdate['action_user'] = 'user';
		}
		$data = D('Admin/DeliveryLog')->data($deliveryUpdate)->add();
		return $data;
	}
	public function getAllAddress($data){
		$Address = new AddressService();
		$temp = $Address->get_region_list($data['province'],false);
		$address_all = $temp['region_name'];
		$temp = $Address->get_region_list($data['city'],false);
		$address_all .= $temp['region_name'];
		$temp = $Address->get_region_list($data['district'],false);
		$address_all .= $temp['region_name'];
		$address_all .= ' - '.$data['address'];
		return $address_all;
	}
}