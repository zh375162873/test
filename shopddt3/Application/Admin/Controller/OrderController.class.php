<?php
namespace Admin\Controller;
use Think\Controller;
use BizService\UserService;
use Org\Util\Date;
use BizService\OrderService;
use BizService\ExeclService;
use BizService\AddressService;

class OrderController extends BaseController {
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
		$this->order_list();
    }
	
	//订单列表
	public function order_list(){
	   
	    /**
	     * 处理订单搜索数据
	     */
	   if($_GET){
	        $order = empty($_GET['order'])?0:intval($_GET['order']);
    	    $orderstatus = empty($_GET['orderstatus'])?0:intval($_GET['orderstatus']);//订单状态
    	    $class = empty($_GET['class'])?0:intval($_GET['class']);//商品分类
    	    $begin_time = empty($_GET['begin_time'])?'':trim($_GET['begin_time']);
    	    $end_time = empty($_GET['end_time'])?'':trim($_GET['end_time']);
    	    $order_sn =empty($_GET['order_sn'])?'':trim($_GET['order_sn']);
            $sub_search = empty($_GET['sub_search'])?0:intval($_GET['sub_search']);//按钮分类
    	    $order_by = 'add_time DESC';
    	    if($order){
    	     //排序方法
    	      switch ($order){
    	          case 1:
    	              $order_by = "order_sn ASC";
    	              break;
    	          case 2:
    	              $order_by = "order_sn DESC";
    	              break;
    	          case 3:
    	              $order_by = "goods_num ASC";
    	              break;
    	          case 4:
    	              $order_by = "goods_num DESC";
    	              break;
    	          case 5:
    	              $order_by = "order_amount ASC";
    	              break;
    	          case 6:
    	              $order_by = "order_amount DESC";
    	              break;
    	          case 7:
    	              $order_by = "add_time ASC";
    	              break;
    	          case 8:
    	              $order_by = "add_time DESC";
    	              break;
    	          case 9:
    	              $order_by = "finnshed_time ASC";
    	              break;
    	          case 10:
    	              $order_by = "finnshed_time DESC";
    	              break;
    	      }
    	    }
    	 
    	    $data=array();
    	    if($orderstatus==1){
    	        $data['order_status'] = 1;
    	        $data['refund_state'] = 0;
    	        $data['evaluation_state'] = 0;
    	    }
    	    elseif($orderstatus==2){
    	        $data['order_status'] = 2;
    	        //$data['refund_state'] = array("in",array("0","2","3"));
    	        $data['evaluation_state'] = 0;
    	    }
    	    elseif($orderstatus==3){
    	        $data['order_status'] = 2;
    	        //$data['refund_state'] = array("in",array("0","2","3"));
    	        $data['evaluation_state'] = 1;
    	    }
    	    elseif($orderstatus==4){
    	        $data['order_status'] = 1;
    	        $data['refund_state'] = array("neq",0);
    	        $data['evaluation_state'] = 0;
    	    }
    	    
    	    if($begin_time&&$end_time){
    	        $data['add_time'] = array(array('gt',strtotime($begin_time)),array('lt',strtotime($end_time)),'and');
    	    }
    	    elseif($begin_time){
    	        $data['add_time'] = array('gt',strtotime($begin_time));
    	    }
    	    elseif ($end_time){
    	        $data['add_time'] = array('lt',strtotime($end_time));
    	    }
    	    
    	    if($order_sn){
    	        //获取对应的order_id
    	        $goodselect=D("OrdersGoods")->where(array("goods_name"=>array('like','%'.$order_sn.'%'),"goods_serial"=>array('like','%'.$order_sn.'%'),'_logic'=>'or'))->distinct(true)->field("order_id")->select();

    	        $arr = '';
    	        for($i=0;$i<count($goodselect);$i++){
    	            $arr .= $goodselect[$i]['order_id'];
    	            if($i!=(count($goodselect)-1)){
    	                $arr .=',';
    	            }
    	        }
    	        
    	        $where['order_id']=array('in',$arr);
    	        $where['order_sn'] = array('like', '%'.$order_sn.'%');
    	        $where['buyer_name'] = array('like', '%'.$order_sn.'%');
    	        $where['buyer_nickname'] = array('like', '%'.$order_sn.'%');
    	        $where['_logic'] = 'or';
    	        $data['_complex'] = $where;
    	    }
            
    	    $this->assign("order",$order);
    	    $this->assign("orderstatus",$orderstatus);
    	    $this->assign("begin_time",$begin_time);
    	    $this->assign("end_time",$end_time);
    	    $this->assign("order_sn",$order_sn);
	    }
	    
	    //商品分类
	   // $goodsclass=D("goodsclass")->where('shop_id='.$this->shop_id)->select();
	    
	    //订单列表
       if($sub_search){
            $orderlist = self::orderlist_exe($data,$order_by);
        }else{
            $orderlist = $this->orders->order_list($this->shop_id,$data,$order_by);
        }
	    $this->assign("goodsclass",$goodsclass);
	    $this->assign("orderlist",$orderlist['list']);
	    $this->assign("page",$orderlist['page']);
	    $this->display("list");
	}
   //导出excel订单列表
    public function orderlist_exe($data,$order_by){
        // $orderlist = $this->orders->order_list_excel($this->shop_id);
        $temp_arr = $this->orders->order_list($this->shop_id,$data,$order_by,1);
        $orderlist = $temp_arr['list'];
        $data=array();
        for($i=0;$i<count($orderlist);$i++){
            $data[$i]['order_sn']= $orderlist[$i]['order_sn'].' ';
            $data[$i]['goods_name']=$orderlist[$i]['goods_name'];
            $data[$i]['buyer_name']= $orderlist[$i]['buyer_name'];
            $data[$i]['nick_name']=$orderlist[$i]['nick_name'];
            $data[$i]['goods_price']= $orderlist[$i]['goods_price'];
            $data[$i]['goods_num']=$orderlist[$i]['goods_num'];
            $data[$i]['order_amount']= $orderlist[$i]['order_amount'];
            $data[$i]['payment_time']=$orderlist[$i]['payment_time'];
            $data[$i]['finnshed_time']= $orderlist[$i]['finnshed_time'];
            $data[$i]['order_status']="";
//            if($orderlist[$i]['order_status']==1){
//             $data[$i]['order_status'] .= "未消费";
//            }elseif ($orderlist[$i]['order_status']==2){
//              $data[$i]['order_status'] .= "已消费";
//            }elseif ($orderlist[$i]['order_status']==3){
//              $data[$i]['order_status'] .= "已取消";
//            }
//            if($orderlist[$i]['$vo.refund_state']==1){
//                $data[$i]['order_status'] .= "  有退款";
//            }
//            if($orderlist[$i]['evaluation_state']==0){
//               $data[$i]['order_status'] .= "  待评价";
//            }elseif ($orderlist[$i]['evaluation_state']==0){
//               $data[$i]['order_status'] .= "  已评价";
//            }

            if($orderlist[$i]['is_entity'] == 0) {
                if ($orderlist[$i]['order_status'] == 1) {
                    $data[$i]['order_status'] .= "未消费";
                } elseif ($orderlist[$i]['order_status'] == 2) {
                    $data[$i]['order_status'] .= "已消费";
                } elseif ($orderlist[$i]['order_status'] == 3) {
                    $data[$i]['order_status'] .= "已取消";
                }
                if ($orderlist[$i]['refund_state'] > 0) {
                    $data[$i]['order_status'] .= ",有退款";
                }
            }else {
                if ($orderlist[$i]['send_type'] == 1) {
                    if ($orderlist[$i]['delivery_status'] == 0) {
                        $data[$i]['order_status'] .= "未发货";
                    } elseif ($orderlist[$i]['delivery_status'] == 1) {
                        $data[$i]['order_status'] .= "已发货";
                    } elseif ($orderlist[$i]['delivery_status'] == 2) {
                        $data[$i]['order_status'] .= "已收货";
                    }
                } else {
                    if ($orderlist[$i]['order_status'] == 1) {
                        $data[$i]['order_status'] .= "未提货";
                    } elseif ($orderlist[$i]['order_status'] == 2) {
                        $data[$i]['order_status'] .= "已提货";
                    }
                }
            }
            if($orderlist[$i]['evaluation_state'] == 0){
                $data[$i]['order_status'] .= ",待评价";
            }elseif($orderlist[$i]['evaluation_state'] == 1){
                $data[$i]['order_status'] .= ",已评价";
            }
        }
        $ExeclService = new ExeclService();
        $ExeclService->downMoreColumnDateToExel($data,"订单列表信息",array('order_sn','goods_name','buyer_name','nick_name','goods_price','goods_num','order_amount','payment_time','finnshed_time','order_status'),array('订单编号','商品名称','用户账号','用户昵称','单价','数量','总价','下单时间','消费时间','订单状态'),array(20,20,35,10,10,15,25,25,25,20),200);
            
    }  
	
	
   //订单详情
     public function orderinfo(){
         $order_id = empty($_GET['id'])?0:trim($_GET['id']);
       
         if(!$order_id){
             $this->error("参数不全");
         }
         
         $info = $this->orders->getinfo($order_id);
         $Address = new AddressService();
         $temp = $Address->get_region_list($info['info']['province'],false);
         $info['info']['province'] =   $temp['region_name'];
         $temp = $Address->get_region_list($info['info']['city'],false);
         $info['info']['city'] =   $temp['region_name'];
         $temp = $Address->get_region_list($info['info']['district'],false);
         $info['info']['district'] =   $temp['region_name'];
         $info['info']['nick_name'] = isset($info['info']['buyer_nickname'])?$info['info']['buyer_nickname']:'——';
         if($info['info']['delivery_status']==0){
            $info['info']['delivery_info'] = '未发货';
         }elseif($info['info']['delivery_status']==1){
            $info['info']['delivery_info'] = '已发货';
         }elseif($info['info']['delivery_status']==2){
            $info['info']['delivery_info'] = '已收货';
         }
         $this->assign("info",$info);
         $this->assign("dhm",$info['dhm']);
         $this->display("info");
     }
     
   //查看退款信息
     public function refund_info(){
         $dhm_id = empty($_GET['dhm_id'])?0:intval($_GET['dhm_id']);
         $order_id = empty($_GET['order_id'])?0:intval($_GET['order_id']);
         $info = $this->refundreturn_db->where(array("dhm_id"=>$dhm_id,"order_id"=>$order_id))->find();
         $info['add_time'] = empty($info['add_time'])?0:Date("Y-m-d H:i:s",$info['add_time']);
         $info['seller_time'] = empty($info['seller_time'])?0:Date("Y-m-d H:i:s",$info['seller_time']);
         $this->assign("info",$info);
         $this->assign("order_id",$order_id);
         $this->display("refund_info");
     }
     
   //处理退款申请
    public function refund_chuli(){
        $refund_id = empty($_POST['refund_id'])?0:intval($_POST['refund_id']);
        $refundcontent = empty($_POST['refund_content'])?0:trim($_POST['refund_content']);
        $seller_status = empty($_POST['seller_status'])?0:intval($_POST['seller_status']);
        if(empty($refund_id)){
            $this->error("缺少参数！！！");
        }
        
        $data =array(
            "seller_state" => $seller_status,
            "refund_state" => $seller_status==2?2:1,
            "seller_message" => $refundcontent,
            "seller_time" => time(),
        );
      
        if($this->refundreturn_db->seller_chuli($refund_id,$data)){
            $this->success("操作成功！");
        }else{
            $this->error("操作失败！");
        }
    }
   
    //置为已消费
    public function zhi_yxf(){
        $id = empty($_POST['id'])?0:trim($_POST['id']);
     
        if($this->orders->zhi_yxf($id)){
            $data['error']=0;//操作成功
            $data['message']="操作成功";
            $data['id']=$id;
            $this->ajaxReturn($data);
        }else{
            $data['error']=2;//操作失败
            $data['message']="操作失败";
            $data['id']=$id;
            $this->ajaxReturn($data);
        }
    }

    public function zhi_th(){
        $order_id = empty($_POST['id'])?0:trim($_POST['id']);
     	$info = $this->orders->getinfo($order_id);

 		$data = array('error'=>1,'message'=>'');
 		// ['message']="";
       	// $data['error']=1;//操作失败
     	foreach ($info['dhm'] as $key => $value) {
	        if($this->orders->zhi_yxf($value['id'])){
	            $data['error']=0;//操作成功
	            $data['message'].=$value['dhm_code']."提货成功;";
	        }else{
	            $data['message'].=$value['dhm_code']."提货失败;";
	        }
     	}
     	$orderData = array(
			'delivery_status' =>2,
			'order_status' =>2,
			'confirm_time' =>time()
			);
		$confirm_tihuo = D('Admin/Orders')-> where('order_id='.$order_id)->save($orderData);
		if($confirm_tihuo){
			$data['message']='批量提货成功!'.$data['message'];
		}
        $this->ajaxReturn($data);
    }
    
    public function test(){
       $userid = 8;
       $arr = $this->orderservice->user_order_list($userid);

       echo "asdfasdf--<pre>";print_r($arr);exit();
    }
    
}

