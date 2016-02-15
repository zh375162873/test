<?php
/*
 * 老商城核销码管理模型
 * 对老商城核销码的管理核销操作
 */
namespace Home\model;
use Think\Model;


class OldChecknumModel extends Model {
	
	//验证核销码
	/*
	 * $type 1:普通商品兑换码；2：积分商城兑换码；3：兑奖活动商品兑换码
	 * return data['error']=1(数据为空)，2(操作失败)，3(核销码不能使用了)，0(核销成功)
	 */
	public function yanzheng_dhm($dhm_code,$store_id){
	    //验证验证码
	    $dhm_code=trim($dhm_code);
	    $arr=$this->where(array("orl_check_num"=>$dhm_code,"orl_sold_id"=>$store_id))->field("orl_goods_id,orl_goods_name as goods_name,orl_check_num as dhm_code,orl_create_time as add_time,orl_use_time as dh_time,orl_is_used as status")->find();
	    $data = array();  
	    if($arr){
	        //判断核销码状态
	        if($arr['status']==1){
	            $data['error']=3;
	            $data['msg']="核销码已经核销";
	        }else{
    	        if($this->where(array("orl_check_num"=>$dhm_code,"orl_sold_id"=>$store_id))->data(array("orl_is_used"=>1,"orl_use_time"=>time()))->save()){
    	            $data['error'] = 0;
    	            $data['msg'] ="核销成功";
    	        }else{
    	            $data['error'] = 2; //操作失败
    	            $data['msg'] ="核销操作失败";
    	        }
	        }
	        $data['data']=$arr;
	    }else{
	        $data['error'] = 1;//数据为空
	        $data['msg'] ="数据为空";
	    }
	    return $data;
	}
	
	//核销码列表(外测调用)
	public function list_dhm($store_id,$status){
		$map = array();
		$map['orl_check_num']=array('neq','');
		if($store_id){
		$map['orl_sold_id']=$store_id;

		}
        if($status!=''){ 
        $map['orl_is_used'] = $status;
        }
	    
       $infolist = $this->where($map)->field("orl_goods_id,orl_buy_user_id as buyer_id,orl_sold_id as store_id,orl_goods_price as goods_price,orl_goods_name as goods_name,orl_check_num as dhm_code,orl_is_used as status,orl_create_time as add_time,orl_use_time as dh_time")->order('orl_id desc')->select();
       return $infolist;
	}
	
	// //核销码商城列表(内用)
	// public function list_shop_dhm($shop_id,$store_id,$status=''){
	//     $map = array();
	//     if($store_id){
	//         $map['store_id']=$store_id;
	//     }
	//     if($shop_id){
	//         $map['shop_id']=$shop_id;
	//     }
	//     if($status == ''){
	       
	//     }else{
	//         $map['status'] = $status;
	//     }
	    
	//     $infolist = $this->where($map)->field("id,goods_id,order_id,store_id,buyer_id,dhm_code,add_time,dh_time,status,refund_status,type")->order('add_time DESC')->select();
	//     for($i=0;$i<count($infolist);$i++){
	//         $arr = D("OrdersGoods")->where(array("order_id"=>$infolist[$i]['order_id'],"goods_id"=>$infolist[$i]['goods_id']))->field("goods_name,goods_price")->find();
	//         $infolist[$i]['goods_name']=$arr['goods_name'];
	//         $infolist[$i]['goods_price']=$arr['goods_price'];
	//         $order = D("Orders")->where(array("order_id"=>$infolist[$i]['order_id']))->field("order_sn")->find();
	//         $infolist[$i]['order_sn']=$order['order_sn'];
	//         $goods_serial = $this->GoodsstoreService->getinfo($infolist[$i]['goods_id'],array("goods_serial"),4);
	//         $infolist[$i]['goods_serial']=$goods_serial['goods_serial'];
	//     } 

	//     return $infolist;
	// }
	public function userChecknum($user_id){
		if(!$user_id){
			return false;
		}
		$data =$this->where('orl_is_used = 0 AND orl_check_num !="" AND orl_buy_user_id='.$user_id)->field('orl_goods_name as goods_name,orl_goods_id as goods_id,orl_check_num as dhm_code,orl_create_time as add_time')->order('orl_id desc')->select();
		return $data;
	}
	
	
}