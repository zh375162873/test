<?php
/*
 * 订单支付模型
 */
namespace Admin\Model;
use Think\Model;
use BizService\GoodsstoreService;
class RefundReturnModel extends Model {
    public $goodservice,$orders_db;
    public function __construct(){
        parent::__construct();
        $this->goodservice = new GoodsstoreService();
        $this->orders_db = D("Orders");
        
    }
    
    public function seller_chuli($refund_id,$data){
        $info = $this->where(array("id" => $refund_id))->field("dhm_id,goods_id,order_id")->find();
        $dhm_id = $info['dhm_id'];
        $statue = $data['seller_state'];
       
        //处理兑换码状态
        if($this->where(array("id"=>$refund_id))->data($data)->save()&&D("DhmManage")->where(array("id"=>$dhm_id))->data(array("refund_status"=>$statue))->save()){
           if($statue==2){
               /**
                * 退款状态 2和3中徘徊 订单状态在1和2中徘徊
                * 状况一：存在正在退款中的核销码，不做任何处理
                * 状况二：不存在退款中的核销码，存在未消费的订单，将订单退款状态设为2
                * 状况三：不存在未消费的订单，存在已经消费的订单，将订单状态设为2
                * 状况四：不存在未消费和已消费的核销码的订单，将订单退款状态设为3
                */
               $arr_tkz = D("DhmManage")->where(array("order_id"=>$info['order_id'],"status"=>0,"refund_status"=>1))->find();
               $arr_yxh = D("DhmManage")->where(array("order_id"=>$info['order_id'],"status"=>1))->find();
               $arr_wxh = D("DhmManage")->where(array("order_id"=>$info['order_id'],"status"=>0,"refund_status"=>0))->find();
               if(empty($arr_tkz)&&!empty($arr_yxh)&&empty($arr_wxh)){
                   D("Orders")->where(array("order_id"=>$info['order_id']))->data(array("order_status"=>2))->save();
               }
              
               if(empty($arr_tkz)&&empty($arr_yxh)&&empty($arr_wxh)){
                   D("Orders")->where(array("order_id"=>$info['order_id']))->data(array("refund_state"=>3))->save();
               }
               if(empty($arr_tkz)&&!empty($arr_wxh)){
                   D("Orders")->where(array("order_id"=>$info['order_id']))->data(array("refund_state"=>2))->save();
               }
               
               //相关库存处理
               $this->goodservice->changestoragebyid($info['goods_id'],-1,$type=1);
               //处理订单金额
               if($this->orders_db->cl_order_money($dhm_id)){
                   return true;
               }else{
                   return false;
               }
           }
           return true;
        }else{
            return false;
        }
    }
    
 

}

?>