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

class BaseController extends Controller {
    public function _initialize(){
        if (!session('proxyKeyStatus')) {
            //判断是否为ajax请求
            //判断是否从手机端请求
            //如果判断session过期后，输出0，从其他地方请求跳转至登录页面
            if(IS_AJAX){
                echo(0);exit;//未登录输出0,前台跳转
            }else{
                $url=C('PROXY_URL').'/proxy/default/index';
                echo("<script language='javascript'>window.location.href='{$url}'</script>");
            }
        }
    }
    public function _empty(){
        $this->error('模块构建中，暂时不可用', __APP__ . '/Index');
    }
}