<?php
namespace Admin\Controller;
use Think\Controller;
use BizService\GoodsstoreService;
use BizService\ExtendService;
use BizService\DeliveryService;
use BizService\SystemService;
class CronController extends Controller{
    public $order,$ordergoods,$orderlog,$extend_service;
    public function __construct(){
        set_time_limit(0);
        parent::__construct();
        $this->order = D("Orders");
        $this->ordergoods = D("OrdersGoods");
        $this->orderlog = D("OrdersLog");
        $this->goodsstoreservice = new GoodsstoreService();
        $this->extend_service = new ExtendService();
    }
    
    //未支付订单处理
    public function chuli_wxf_order(){
        //超过20分钟未支付的订单即取消
        $limit_time = time()-(60*20);
        $arr = $this->order->where(array("order_status"=>0,'add_time'=>array("elt",$limit_time)))->select();
        //获取所有订单中商品的数量
        if($arr){
            for($i=0;$i<count($arr);$i++){
                //正数为销售，负数为退货
                $kucun = -$arr[$i]['goods_num'];
                $change = $arr[$i]['extend_num'];
                //todo 库存处理，目前仅处理现金商品
                $this->goodsstoreservice->changestoragebyid($arr[$i]['goods_id'],$kucun,$type=1);
                //优惠码处理（需要赵星给我提供接口）
                $this->extend_service->updateExtendGoodsStore($arr[$i]['extend_id'], $arr[$i]['goods_id'], $change);
                
                //删除订单，订单商品，订单流水操作记录
                $this->order->where(array("order_id"=>$arr[$i]['order_id']))->delete();
                $this->ordergoods->where(array("order_id"=>$arr[$i]['order_id']))->delete();
                $this->orderlog->where(array("order_id"=>$arr[$i]['order_id']))->delete();
            }
        }
        echo "成功！！！";
    }
    /**
     * 延迟自动收货，超过15天未确认订单
     */
    public function auto_lazy_receive(){
        $Delivery = new DeliveryService();
        $system = new SystemService();
        $shop = get_shop_proxy();
        $auto_confirm_time = $system->get_code($shop['shop_id'],'ORDER_AUTO_RECEIVE');
        $auto_confirm_time = $auto_confirm_time?$auto_confirm_time:15;//默认15天
        $auto_time = time()-($auto_confirm_time*86400);
        $auto_list = M('delivery')->field('delivery_id')->where("add_time <=".$auto_time." AND status=0")->select();
        foreach($auto_list as $order){
            $Delivery->confirmDelivery($order['delivery_id']);
        }
    }
}