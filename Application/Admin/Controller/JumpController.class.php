<?php
/**
 * Created by ddt.
 * User: 谢林
 * Date: 2015/9/16 14:59
 * Description:后台管理控制器基类，所有后台控制器需要继承该控制器
 */

namespace Admin\Controller;
use Think\Controller;
use BizService\ShopService;

class JumpController extends Controller {
    public function jumpIn(){
        //接收密码串
        $key=I('get.key','');
        $addr=I('get.addr','');
        $proxy_id=I('get.proxy_id',0,'intval');
        $proxy_name=I('get.proxy_name',0,'strval');
        if($key==md5('admin'.substr(time(),-4).$proxy_id)){

            $shop = new ShopService();
            $shop_info = $shop->get_shop_info_by_proxy($proxy_id);
            if($shop_info){
                //写入状态session,跳转主页
                session('proxyKeyStatus','on');
                session('proxyId', $proxy_id);
                session('proxyName', $proxy_name);
                session('shopId',$shop_info[0]['shop_id']);
            }else{
                //跳转至未开通提示页面
                redirect(U('shopmanage/shop_close'));
            }

            if($addr =='extend'){
                redirect('./../extend/index');
            }else{
                redirect('./../index/index');
            }
        }else{
            //跳转核心平台主页
            $url='location:'.C('WIFI_URL').'/proxy/default/index';
            header($url);
        }
    }
}