<?php
namespace Home\Controller;

use BizService\CartService;
use BizService\GoodshomeService;
use Think\Controller;

header("Content-type:text/html;charset=utf-8");

class CartController extends BaseController
{
    private $goods;
    public function _initialize()
    {
        parent::_initialize();

        $this->goods = new GoodshomeService();
    }

    /**
     * 购物车首页
     */
    public function index()
    {

        $model_cart = new CartService();

        //购物车列表
        $cart_list = $model_cart->listCart('db', array('buyer_id' => session("userId")));
        //获取最新的购物车商品信息
        $model_cart->get_goods_cart_list($cart_list);
        $model_cart->getCartNum('db',array('buyer_id'=>session("userId")));
        $this->assign('empty','<div class="noData"><p>您还未添加任何商品~</p><a href="'.U("home/index/index").'">去逛逛</a></div>');
        $this->assign("cart", $cart_list);
        $cart_price = $model_cart->get_cart_all_price();
        $cart_price = !empty($cart_price)?price_format($cart_price):0;
        $this->assign("cart_money", $cart_price);
        $this->display();
    }

    /**
     * ajax返回购物车数量及价格
     */
    public function ajax_load(){
        $model_cart = new CartService();
        //购物车列表
        $cart_list = $model_cart->listCart('db', array('buyer_id' => session("userId")));
        $model_cart->getCartNum('db',array('buyer_id'=>session("userId")));
        $return = array('cart_price'=>$model_cart->get_cart_all_price(),'cart_nums'=>$model_cart->get_cart_goods_num());
        exit(json_encode($return));
    }
    /**
     * 加入购物车，登录后存入购物车表
     * 存入COOKIE，由于COOKIE长度限制，最多保存5个商品
     * 登录前保存的信息以goods_id为下标
     *
     */
    public function add()
    {
        $model_cart = new CartService();
        if (is_numeric($_GET['goods_id'])) {
            $goods_id = intval($_GET['goods_id']);
            //购买数量小于1时，强制为1
            $quantity = intval($_GET['quantity'])<1?1:intval($_GET['quantity']);
            $qd_code = I('goods_code')?I('goods_code'):'';//渠道优惠口令
            if ($goods_id <= 0) return;
            $goods_info = $this->goods->get_online_goods_info($goods_id,'goods_id,goods_storage,storage_type,storage_num,goods_name,goods_price,goods_marketprice,shop_id,goods_image,is_virtual,virtual_limit');
            //如果是渠道优惠商品，一并保存到购物车中
            if($qd_code){
                $goods_info[0]['user_input'] = json_encode(array('qd_code'=>$qd_code));
            }
            $msg = $this->_check_goods($goods_info[0], $_GET['quantity']);
            if ($msg) {
                exit(json_encode(array('state' => 'false', 'msg' => $msg)));
            }
        }

        //已登录状态，存入数据库,未登录时，存入COOKIE
        if (session('userId')) {
            $save_type = 'db';
            $goods_info[0]['buyer_id'] = session('userId');
        } else {
            $save_type = 'cookie';
        }
        $insert = $model_cart->addCart($goods_info[0], $save_type, $quantity);
        if ($insert) {
            //购物车商品种数记入cookie
            setddtCookie('cart_goods_num', $model_cart->get_cart_goods_num(), 2 * 3600);
            $data = array('state' => 'true', 'num' => $model_cart->get_cart_goods_num(), 'amount' => price_format($model_cart->get_cart_all_price()));
        } else {
            $data = array('state' => 'false');
        }
        exit(json_encode($data));
    }

    /**
     * 购物车更新商品数量
     */
    public function update()
    {
        $cart_id = intval(abs($_GET['cart_id']));
        $quantity = intval(abs($_GET['quantity']));

        if (empty($cart_id) || empty($quantity)) {
            exit(json_encode(array('state' => 'false', 'msg' => "购物车更新失败！")));
        }

        $model_cart = new CartService();


        //存放返回信息
        $return = array();

        $cart_info = $model_cart->getCartInfo(array('cart_id' => $cart_id, 'buyer_id' => session('userId')));

        //普通商品
        $goods_id = intval($cart_info['goods_id']);
        $goods_info = $this->goods->get_online_goods_info($goods_id,'goods_storage,goods_price');
        //$goods_info = $logic_buy_1->getGoodsOnlineInfo($goods_id, $quantity);
        if (empty($goods_info)) {
            $return['state'] = 'invalid';
            $return['msg'] = '商品已被下架';
            $return['subtotal'] = 0;
            //todo  此处需要删除购物车中该条商品
            //QueueClient::push('delCart', array('buyer_id' => session("userId"), 'cart_ids' => array($cart_id)));
            exit(json_encode($return));
        }

        if (intval($goods_info[0]['goods_storage']) < $quantity) {
            $return['state'] = 'shortage';
            $return['msg'] = '库存不足';
            $return['subtotal'] = $goods_info[0]['goods_price'] * $quantity;
            $model_cart->editCart(array('goods_num' => $goods_info[0]['goods_storage']), array('cart_id' => $cart_id, 'buyer_id' => session("userId")));
            exit(json_encode($return));
        }


        $data = array();
        $data['goods_num'] = $quantity;
        $data['goods_price'] = $goods_info[0]['goods_price'];
        $update = $model_cart->editCart($data, array('cart_id' => $cart_id, 'buyer_id' => session("userId")));
        if ($update) {
            $return = array();
            $return['state'] = 'true';
            $return['subtotal'] = $goods_info[0]['goods_price'] * $quantity;
            $return['goods_price'] = $goods_info[0]['goods_price'];
            $return['goods_num'] = $quantity;
        } else {
            $return = array('state' => false,'msg' => "购物车更新失败");
        }
        exit(json_encode($return));
    }

    /**
     * 根据购物车id删除购物车商品
     */
    public function delCart(){
        $cart_id = I("cart_id",0,'intval');
        $model_cart = new CartService();
        $row = $model_cart->delCart('db',array('cart_id'=>$cart_id));
        if(false !== $row){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
    /**
     * 检查商品是否符合加入购物车条件
     * @param unknown $goods
     * @param number $quantity
     */
    private function _check_goods($goods_info, $quantity)
    {
        $model_cart = new CartService();
        if (!is_numeric($quantity)) {
            return "购买数量只能为数字";
        }
        if (empty($quantity)) {
            return "商品数量不能为空";
        }
        if (empty($goods_info)) {
            return "商品不存在或已下架";
        }
        $condition['goods_id'] = $goods_info['goods_id'];
        $condition['buyer_id'] = session('userId');
        $cart_goods	= $model_cart->checkCart($condition);

        //购物车已有数量+再次购买数量
        $limit_num = $cart_goods['goods_num']+$quantity;
        if($limit_num > $goods_info['virtual_limit'] && $goods_info['virtual_limit'] > 0){
            return "此商品为限购商品,最多购买为".$goods_info['virtual_limit'];
        }

        if (intval($goods_info['goods_storage']) < 1) {
            return "商品库存不足";
        }

        if($goods_info['storage_type']){
            //调拨库存,加入购物车时候，不能大于调拨库存
            if ($limit_num > intval($goods_info['storage_num'])) {
                return "商品库存不足";
            }
        }else{
            if ($quantity > intval($goods_info['goods_storage'])) {
                return "商品库存不足";
            }
        }

//        if ($goods_info['is_virtual']) {
//            exit(json_encode(array('msg'=>'该商品不允许加入购物车，请直接购买')));
//        }
    }
}