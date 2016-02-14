<?php
namespace Admin\Controller;
use Think\Controller;
use BizService\UserService;
use Org\Util\Date;
use BizService\OrderService;
use BizService\ExeclService;
use BizService\DeliveryService;

class DeliveryController extends BaseController {
    public $shop_id,$users,$orders,$refundreturn_db,$orderservice;
    public function _initialize(){
        parent::_initialize();
        $shop_info=get_shop_proxy();
        $this->shop_id = $shop_info['shop_id']?$shop_info['shop_id']:1;
        $this->users = new UserService();
        $this->orderservice = new OrderService();
        $this->orders = D("Orders");
        $this->refundreturn_db = D("RefundReturn");
    }
    
    public function index(){
        $order_list=$this->deliveryOrderList_Format();
        $this->assign("orderlist",$order_list['list']);
        $this->assign("search_form",$order_list['searchForm']);
        $this->assign("page",$order_list['page']);

        $this->display("list");
    }
	
   //导出excel订单列表
    public function orderlist_exe(){
        $data=$this->deliveryOrderList_Format(1);
        $order_list = $data['list'];
        foreach ($order_list as $key => $value) {
            $order_list[$key]['order_sn']= "'".$value['order_sn']."'";
        }  
        $ExeclService = new ExeclService();
        $ExeclService->downMoreColumnDateToExel($order_list,"发货列表信息",array('order_sn','goods_name','buyer_name','buyer_nickname','goods_price','goods_num','shipping_fee','order_amount','delivery_status_info','payment_time','delivery_time'),array('订单编号','商品名称','用户账号','用户昵称','单价','数量','运费','总价','发货状态','下单时间','发货/收货时间'),array(20,25,20,10,10,10,10,10,10,25,25),175);
    }  
	public function addDelivery(){
        $deliveryAdd = array();
        $deliveryAdd['orderId'] = I('post.order_id',0,'intval');
        $deliveryAdd['deliveryType'] = I('post.delivery_type','','strval');
        $deliveryAdd['deliverySn'] = I('post.delivery_sn','','strval');
        $deliveryAdd['deliveryRemark'] = I('post.delivery_remark','','strval');
        $Delivery = new DeliveryService();
        $deliveryRes = $Delivery->addDeliveryMission($deliveryAdd);
    }
    public function getDeliveryInfo(){

        $Delivery = new DeliveryService();
        $deliveryId = I('post.delivery_id',0,'intval');
        $deliveryInfo = $Delivery->getDeliveryInfo($deliveryId);
        $deliveryInfo['log'] = $Delivery->getDeliverylog($deliveryId);
        echo json_encode($deliveryInfo);
    }
    public function updateDeliveryInfo(){

        $Delivery = new DeliveryService();
        $deliveryId = I('post.delivery_id',0,'intval');
        $infoText = I('post.info_text','','strval');
        $deliveryRes = $Delivery->updateDeliveryInfo($deliveryId,$infoText);
        if($deliveryRes){
            echo json_encode(array('err'=>0,'msg'=>'更新物流成功!','add_time'=>date("Y-m-d H:i"),'log_text'=>$infoText));
        }else{
            echo json_encode(array('err'=>1,'msg'=>'更新物流失败!'));
        }
    }
    public function deliveryOrderList_Format($excel=0){
        $order=new OrderService(); 
        $Delivery = new DeliveryService();
        $goodsclass = I('get.goodsclass',0,'intval');
        $keywords = trim(I('get.keywords','','strval'));
        $delivery_status = trim(I('get.delivery_status',-1,'intval'));
        // var_dump($delivery_status);exit;
        $begin_time = trim(I('get.begin_time','','strval'));
        $end_time = trim(I('get.end_time','','strval'));
        $ordername = trim(I('get.ordername','','strval'));
        $ordertype = trim(I('get.ordertype','ASC','strval'));
        $searchForm = array(
            'goodsclass'=>$goodsclass,
            'keywords'=>$keywords,
            'delivery_status'=>$delivery_status,
            'begin_time'=>$begin_time,
            'end_time'=>$end_time,
            'ordername'=>$ordername,
            'ordertype'=>$ordertype,
            );
        if($excel==1){
            $searchForm['excel'] = 1;
        }else{
            $searchForm['excel'] = 0;
        }
        $order =$order->get_delivery_order_list($searchForm);
        $orderlist = $order['list'];
        // var_dump( $orderlist);exit;
        $data=array();
        for($i=0;$i<count($orderlist);$i++){
            $data[$i]['order_sn']= $orderlist[$i]['order_sn'];
            $data[$i]['goods_name']=$orderlist[$i]['goods_name'];
            $data[$i]['buyer_name']= $orderlist[$i]['buyer_name'];
            $data[$i]['buyer_nickname']=$orderlist[$i]['buyer_nickname'];
            $data[$i]['goods_price']= $orderlist[$i]['goods_price'];
            $data[$i]['goods_num']=$orderlist[$i]['goods_num'];
            $data[$i]['shipping_fee']=$orderlist[$i]['shipping_fee'];
            $data[$i]['order_amount']= $orderlist[$i]['order_amount'];
            $data[$i]['delivery_id']= $orderlist[$i]['delivery_id'];
            $data[$i]['order_id']= $orderlist[$i]['order_id'];
            $data[$i]['address']= $orderlist[$i]['address'];
            $data[$i]['delivery_status']= $orderlist[$i]['delivery_status'];
            $data[$i]['address_all'] = $Delivery->getAllAddress($orderlist[$i]);
            if ($orderlist[$i]['delivery_status'] == 1) {
                $data[$i]['delivery_status_info']= '已发货';
                $data[$i]['delivery_time'] = date("Y-m-d H:i",$orderlist[$i]['delivery_time']);
            }else if($orderlist[$i]['delivery_status'] == 2){
                $data[$i]['delivery_status_info']= '已收货';
                $data[$i]['delivery_time'] = $orderlist[$i]['confirm_time'];
            }else{
                $data[$i]['delivery_status_info']= '未发货';
                $data[$i]['delivery_time'] = '/';
            }
            $data[$i]['payment_time']=$orderlist[$i]['payment_time'];
        }
            $arr = array('list'=>$data,'searchForm'=>$searchForm,'page'=>$order['page']);
        return $arr;
    }
    public function confirmDelivery(){
        $delivery_id = I('post.delivery_id',0,'intval');
        $Delivery = new DeliveryService();
        $data = $Delivery->confirmDelivery($delivery_id);
        if($data){
            echo json_encode(array('error'=>0,'msg'=>'确认收货成功!','add_time'=>date('Y-m-d H:i'),'log_text'=>'已收货'));exit;
        }else{
            echo json_encode(array('error'=>1,'msg'=>'确认收货失败!'));exit;
        }
    }
}

