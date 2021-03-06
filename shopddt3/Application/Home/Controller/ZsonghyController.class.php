<?php
/*
 * 赠送好友控制器
 * @auth 王春一
 */
namespace Home\Controller;

header("Content-type:text/html;charset=utf-8");

class ZsonghyController extends BaseController
{
    public $dhm_db, $wxjss, $signPackage,$order;

    public function _initialize(){
        parent::_initialize();

        $this->dhm_db = D("DhmManage");
        $this->order = D('Orders');
        vendor("WeixinJssdk.WeixinJssdk");
        $this->wxjss = new \WeixinJssdk();
        $this->signPackage = $this->wxjss->GetSignPackage();
    }

    //赠送好友
    public function donation()
    {
        $dhm_id = I("get.dhm_id");//兑换码id
        $show = I("get.show", 0);
        $d_id = I("get.did", session("userId"));//赠送人ID
        if ($d_id) {
            $this->assign('did', $d_id);
        }
        $dhminfo = $this->dhm_db->where(array("id" => $dhm_id))->field("order_id,dhm_code,goods_id")->find();
        $goods_info = D("OrdersGoods")->where(array("order_id" => $dhminfo['order_id'], "goods_id" => $dhminfo['goods_id']))->find();
        $order_info = $this->order->get_order_info(array('order_id'=>$dhminfo['order_id']));
        $store = get_merchant_info($order_info[0]['store_id']);
        $data = array(
            "order_id" => $goods_info['order_id'],
            "goods_id" => $goods_info['goods_id'],
            "goods_name" => $goods_info['goods_name'],
            "goods_image" => $goods_info['goods_image'],
            "dhm_code" => $dhminfo['dhm_code'],
            'send_type' => $order_info[0]['send_type'],
            'store_id' => $order_info[0]['store_id'],
            'address' => $store['address']
        );
        $service = "http://".$_SERVER['HTTP_HOST'];
        $url = $service."".U("Zsonghy/donation", array("dhm_id" => $dhm_id, "did" => $d_id, "show" => 1));
        $this->assign("share_sign", $this->signPackage);
        $this->assign("url", $url);
        $this->assign("show", $show);
        $this->assign("service",$service);
        $this->assign("dhm_id",$dhm_id);
        $this->assign("info", $data);
        $this->display("donation");
    }
    //赠送后状态改变
    public function donation_chanage(){
        $dhm_id = I("post.dhm_id",0);
        if($dhm_id){
         if($this->dhm_db->where(array("id"=>$dhm_id))->data(array("zs_status"=>1))->save()){
            echo 1;
         }else{
            echo 2;
         }
        }else{
           echo 0;
        }
    }
    
}