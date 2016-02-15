<?php
/**
 * Created by ddt.
 * User: 谢林
 * Date: 2015/9/16 14:59
 * Description:前台控制器基类，所有前台控制器需要继承该控制器
 */

namespace Home\Controller;

use BizService\ShopService;
use Think\Controller;

class BaseController extends Controller
{
    public function _initialize()
    {
        if (!session('userId')) {
        	$url = I('get.forward','');
        	if($url){
        		$this->redirect("index/userLogin?before_url=".$url);
        	}else{
        		$this->redirect("index/userLogin");
        	}
        }
        //session('city')来自于分站切换，如果找不到，则跳转默认站
        $shop_info = shop_info(session('city'));
        $this->assign("shop_base_info",$shop_info);
    }
}