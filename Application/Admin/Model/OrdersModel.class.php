<?php
/**
现金商品信息模型
*/
namespace Admin\Model;
use Think\Model;
use BizService\UserService;
use Org\Util\Date;
class OrdersModel extends Model 
{    
 public $users;

 public function __construct(){
     parent::__construct();
     $this->users = new UserService();
 }
    
  //定义自动填充
 protected $_auto = array ( 
   array('addtime','time',1,'function'),    
   array('payment_time','time',2,'function'), 
   array('finish_time','time',2,'function'),
 );
 
 //订单列表
 public function order_list($shop_id,$data,$order_by,$excel=0){
    $data['shop_id']=$shop_id;
    if($data['order_status']==0){
        $data['order_status']=array('neq',0);
    }
    if(empty($order_by)){
        $order_by = "add_time DESC";
    }
    $count = $this->where($data)->count();
    $page = new \Think\Page($count,PAGE_SIZE);
    $show = $page->show();
    foreach($data as $k=>$v){
        if($k !='_complex') {
            $condition_array['a.' . $k] = $v;
        }else{
            foreach ($v as $key => $value) {
                if($key!='_logic'){
                    $condition_array['_complex']['a.'.$key] = $data['_complex'][$key];
                }else{
                    $condition_array['_complex'][$key] = $data['_complex'][$key];
                }
            }
        }
    }

    $order = explode(' ', $order_by);
    if($order[0]=='goods_name'||$order[0]=='goods_price'||$order[0]=='goods_num'){
      $order[0] = 'b.'.$order[0];
    }else{
      $order[0] = 'a.'.$order[0];
    }
    $order_by = implode(' ',$order);
    if($excel){
      $arr = $this->alias('a')->join('ddt_orders_goods as b ON a.order_id=b.order_id')->where($condition_array)->order($order_by)->field('a.*,b.goods_name,b.goods_price,b.goods_num')->select();
    }else{
      $arr = $this->alias('a')->join('ddt_orders_goods as b ON a.order_id=b.order_id')->where($condition_array)->order($order_by)->limit($page->firstRow.",".$page->listRows)->field('a.*,b.goods_name,b.goods_price,b.goods_num')->select();
    }
    for($i=0;$i<count($arr);$i++){
        $arr[$i]['payment_time'] = Date("Y-m-d H:i:s",$arr[$i]['payment_time']);
        $arr[$i]['finnshed_time'] = empty($arr[$i]['finnshed_time'])?'':Date("Y-m-d H:i:s",$arr[$i]['finnshed_time']);
		    $arr[$i]['confirm_time'] = empty($arr[$i]['confirm_time'])?'':Date("Y-m-d H:i:s",$arr[$i]['confirm_time']);
        $users = $this->users->userInfo($arr[$i]['buyer_id']);
        $arr[$i]['user_name']=$users['user_name'];
        $arr[$i]['nick_name']=$users['nick_name'];
    }
    $datas= array();  
    $datas['list'] = $arr;
    $datas['page'] = $show;
    return $datas;
 }
 public function order_list_excel($shop_id,$data){
    $data['shop_id']=$shop_id;
    if($data['order_status']==0){
        $data['order_status']=array('neq',0);
    }
    
    $arr = $this->where($data)->order("add_time DESC")->select();
    for($i=0;$i<count($arr);$i++){
        $arr[$i]['payment_time'] = Date("Y-m-d H:i:s",$arr[$i]['payment_time']);
        $arr[$i]['finnshed_time'] = empty($arr[$i]['finnshed_time'])?'':Date("Y-m-d H:i:s",$arr[$i]['finnshed_time']);
        $goods = D("OrdersGoods")->where(array("order_id"=>$arr[$i]['order_id']))->field("goods_name,goods_price,goods_num")->find();
        $arr[$i]['goods_name']=$goods['goods_name'];
        $arr[$i]['goods_price']=$goods['goods_price'];
        $users = $this->users->userInfo($arr[$i]['buyer_id']);
        $arr[$i]['user_name']=$users['user_name'];
        $arr[$i]['nick_name']=$users['nick_name'];
    }

    return $arr;
 }
 public function getOrderById($id,$field="*"){
     return $this->field($field)->where(array('order_id'=>$id))->find();
 }
 //订单详情
  public function getinfo($orderid){
      $info=$this->where(array("order_id"=>$orderid))->find();
      $goods = D("OrdersGoods")->where(array("order_id"=>$info['order_id']))->find();
      $dhm = D("DhmManage")->where(array("order_id"=>$info['order_id'],"type"=>1,"shop_id"=>$info['shop_id']))->select();
      
      $userinfo = $this->users->userInfo($info['buyer_id']);
      $data=array();
      $data['info']=$info;
      $data['goods']=$goods;
      $data['dhm']=$dhm;
      $data['userinfo']=$userinfo;
      
      return $data;
  }
  //置为已消费
  public function zhi_yxf($id){
      //获取核销码信息
      $dhm_info = D("DhmManage")->where(array("id"=>$id))->find();
  
      if(D("DhmManage")->where(array("id"=>$id))->data(array("status"=>1,"dh_time"=>time()))->save()){
          $this->where(array("order_id"=>$dhm_info['order_id']))->data(array("order_status"=>2,"finnshed_time"=>time(),"confirm_time"=>time()))->save();
          return true;
      }else{
          return false;
      }
  
  }
  //订单金额变动，根据核销码进行处理,订单金额变动的时候，订单渠道也要进行变动。
  public function cl_order_money($dhm_id){
      $dhm_info = D("DhmManage")->where(array("id"=>$dhm_id))->field("order_id,goods_id")->find();
      $order_id = $dhm_info['order_id'];
      $goods_id = $dhm_info['goods_id'];
      $orderinfo = $this->where(array("order_id"=>$order_id))->field("extend_discount,order_amount,goods_num,refund_state")->find();
      $goods = D("OrdersGoods")->where(array("order_id"=>$order_id,"goods_id"=>$goods_id))->field("goods_price,goods_divided")->find();
      if($orderinfo['extend_discount']>0){
          $goods_price = $goods['goods_price']*(1-($orderinfo['extend_discount']/100));
      }else{
          $goods_price = $goods['goods_price'];
      }
      $order_amount = $orderinfo['order_amount']-$goods_price;
      
      if($this->where(array("order_id"=>$order_id))->data(array("order_amount"=>$order_amount))->save()){
          //处理订单分成详情表,退款完成后，就要进行订单总额金额变动，订单提成金额、推广人员金额、渠道提成金额
          $fc_order = D("CommissionOrder")->where(array("order_id"=>$order_id))->find();

          $arr = array(
              "order_fee" => $order_amount,
              "commission_fee" => $fc_order['commission_fee']-$goods['goods_divided'],
              "channel_money" => $fc_order['channel_money']*($orderinfo['goods_num']-1)/$orderinfo['goods_num'],
              "referee_money" => $fc_order['referee_money']*($orderinfo['goods_num']-1)/$orderinfo['goods_num'],
          );
           
          if(D("CommissionOrder")->where(array("order_id"=>$order_id))->data($arr)->save()){
           
          }else{
              return false;
          }
          
          return true;
      }else{ 
          return false;
      }
      
  }
  
  
  
}

?>