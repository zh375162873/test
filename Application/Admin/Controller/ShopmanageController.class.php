<?php
namespace Admin\Controller;

use Think\Controller;

class ShopmanageController extends Controller{
    public function _initialize(){

    }

    /**
     * 商城不存在提示
     */
    public function shop_close(){
        $this->error("您的商城暂未开通，请联系客服人员为您开通！");
    }
}