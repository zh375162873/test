<?php
/**
 * Created by ddt.
 * User: 谢林
 * Date: 2015/9/21 10:54
 * Description:公共函数库
 */

/**
 * 根据域名获取商城基本信息
 */
function shop_info($domain){
	$domain = addslashes(cookie("city"));
	$regFrom = I('get.ddt_from', '');
	session('regFrom', $regFrom);
	//$domain = $_SERVER['HTTP_HOST'];
	//todo 后期考虑加缓存
	//if(empty(session('shopId'))){
			$shop_service = new \BizService\ShopService();
			$shop_info = $shop_service->get_shop_info_by_domain($domain,array('shop_id','shop_title','member_id','shop_company_name','shop_keywords'));

			//找不到城市，默认跳转到xian站
			if(empty($shop_info)){
				redirect(U('City/index',array('city'=>'xian')));
			}else{
				session('proxyId', $shop_info[0]['member_id']);//代理ID
				session('shopId', $shop_info[0]['shop_id']);//商城ID
				session('city',cookie("city"));//城市分站
			}
	//}
    return $shop_info[0];
}
/**
 * 根据两点间的经纬度计算距离
 * @param float $latitude 纬度值
 * @param float $longitude 经度值
 * @return float
 */
function getDistance($latitude1, $longitude1, $latitude2, $longitude2)
{
    $earth_radius = 6371000;
    $dLat = deg2rad($latitude2 - $latitude1);
    $dLon = deg2rad($longitude2 - $longitude1);
    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * asin(sqrt($a));
    $d = $earth_radius * $c;
    return round($d);   //四舍五入
}

/**
 * 获取当前主机域名
 * @param $url
 * @return mixed
 */
function get_domain()
{
    return $_SERVER['HTTP_HOST'];
}

/**
 * 判断当前用户是否登录
 *
 */
function is_login() {
	return session("userId");
}

/**
 * 计算运费
 *
 * @param int $parent_id 父订单id
 * @param int $sendtype 运费类型，快递，自提
 * @return int  运费金额
 */
	//计算运费
	 function get_shipping_fee($parent_id,$sendtype,$type=0){
		$shipping_fee=0;
		if($type>0){
		      $orderlist=M("orders")->where("order_id=$parent_id")->find();
		    //调取订单商品信息
			  $goodsinfo = M("goods")->where("goods_id=".$orderlist['goods_id']." and is_virtual=0")->find();
			  if($goodsinfo){
				$goods_sending = M("goods_sending")->where("goods_commonid=".$goodsinfo['goods_commonid'])->find();
				if($sendtype==1){
				 //快递所有商品
				  if($goods_sending){
					 //如果是包邮
					 if($goods_sending['type']==1){
					   return 0;
					 //如果是收费   
					 }else{
					   //检查是否启用多件包邮
					   if($goods_sending['is_free']==1&&$goods_sending['free_num']>=1){
						  if($orderlist['goods_num']>=$goods_sending['free_num']){
							 return 0;
						  }
					   }
					   //如果多件包邮不起作用，就要进行邮费计算了
					   $shipping_fee=$shipping_fee+$goods_sending['first_price']+($orderlist['goods_num']-1)*$goods_sending['add_price'];
					 }  
				  }
				//如果是自提   
				}elseif($sendtype==2){
				   if($goods_sending){
					  if($goodsinfo['is_take']==1&&$goodsinfo['take_type']>0){
						//总减
						if($goodsinfo['take_type']==1){
						  $shipping_fee=$shipping_fee+$goodsinfo['take_num'] ;
						  return $shipping_fee;
						//多件减
						}elseif($goodsinfo['take_type']==2){
						  $shipping_fee=$shipping_fee+$goodsinfo['take_num']*$orderlist['goods_num'];
						}
					  }else{
						return $shipping_fee;
					  }
				   }  
				}else{
				  return 0;
				}
			  }
		}else{
			//循环出此批次所有订单信息
			$orderlist=M("orders")->where("parent_id=$parent_id")->select();
			foreach($orderlist as $key=>$val){
			  //调取订单商品信息
			  $goodsinfo = M("goods")->where("goods_id=".$val['goods_id']." and is_virtual=0")->find();
			  if($goodsinfo){
				$goods_sending = M("goods_sending")->where("goods_commonid=".$goodsinfo['goods_commonid'])->find();
				if($sendtype==1){
				 //快递所有商品
				  if($goods_sending){
					 //如果是包邮
					 if($goods_sending['type']==1){
					   continue;
					 //如果是收费   
					 }else{
					   //检查是否启用多件包邮
					   if($goods_sending['is_free']==1&&$goods_sending['free_num']>=1){
						  if($val['goods_num']>=$goods_sending['free_num']){
							  continue;
						  }
					   }
					   //如果多件包邮不起作用，就要进行邮费计算了
					   $shipping_fee=$shipping_fee+$goods_sending['first_price']+($val['goods_num']-1)*$goods_sending['add_price'];
					 }  
				  }
				//如果是自提   
				}elseif($sendtype==2){
				   if($goods_sending){
					  if($goodsinfo['is_take']==1&&$goodsinfo['take_type']>0){
						//总减
						if($goodsinfo['take_type']==1){
						  $shipping_fee=$shipping_fee+$goodsinfo['take_num'] ;
						  continue;
						//多件减
						}elseif($goodsinfo['take_type']==2){
						  $shipping_fee=$shipping_fee+$goodsinfo['take_num']*$val['goods_num'];
						}
					  }else{
						continue;
					  }
				   }  
				}else{
				  return -1;
				}
			  }
			}
		}
		return $shipping_fee;
		
	}
	
/**
 * 计算当前没有任何优惠和运费的订单统计信息
 *
 * @param int $parent_id 父订单id
 * @return array  统计信息
 */
	//计算运费
	 function get_order_fee($parent_id){
	    //循环出此批次所有订单信息
		$orderlist=M("orders")->where("parent_id=$order_id")->select();
		$orderall=array();
		//商品总价
		$goods_amount=0;
		//余额支付
		$ye_paymoney=0;
		//在线支付
		$online_paymoney=0;
		//合计支付
		$order_amount=0;
		//运费或者优惠金额
		$yunfei_amount=get_shipping_fee($order_id ,1);
		foreach($orderlist as $key=>$val){
		  //调取订单商品信息
		  $orderinfo1 = $this->orderdb->getinfo($val['order_id']);
		  $orderall[$key]=$orderinfo1;
		  $goods_amount=$goods_amount+$orderinfo1['goods_amount'];
		  $ye_paymoney=$ye_paymoney+$orderinfo1['ye_paymoney'];
		  $online_paymoney=$online_paymoney+$orderinfo1['online_paymoney'];
		  $order_amount=$order_amount+$orderinfo1['order_amount'];
		}
	 
	 
	 
	 }	
	
