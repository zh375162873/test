<?php
namespace Admin\Controller;
use BizService\UserService;
use BizService\OrderService;
use BizService\GoodsService;
class IndexController extends BaseController {
    public $shop_id,$goods,$order_service;
    public function _initialize(){
        parent::_initialize();
        $shop_info=get_shop_proxy();
        $this->shop_id = $shop_info['shop_id'];
        $this->order_service = new OrderService();
		$this->goods = new GoodsService();
    }
    public function index(){
        $this->display("layout");
    }
    public function statistics(){
        $shop_service = new \BizService\ShopService();
        $shop_info = $shop_service->get_shop_info($this->shop_id);

        $live_time = datediff($shop_info[0]['shop_time'],time());

        //订单统计
        $order_count = $this->order_service->shangcheng_order_count($this->shop_id);
        //评论统计
        $pl_count = $this->order_service->pinglun_count($this->shop_id);
		//商品统计
		
		$goods_state1=$this->goods->getGoodsState(1);
		$goods_state2=$this->goods->getGoodsState(2);
		$goods_state3=$this->goods->getGoodsState(3);

        $this->assign("shop_info",$shop_info[0]);
        $this->assign("live_time",$live_time);
        $this->assign("order_count",$order_count);
        $this->assign("pl_count",$pl_count);
		$this->assign("goods_state1",$goods_state1);
		$this->assign("goods_state2",$goods_state2);
		$this->assign("goods_state3",$goods_state3);
        $this->display("index");
    }
    public function add(){
        $user = new UserService();
        dump($user->addUser());
    }

    public function update(){
        $user = new UserService();
        dump($user->updateUser());
    }

    public function login(){
        $this->display();
    }
}