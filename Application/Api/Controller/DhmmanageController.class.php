<?php
namespace Api\Controller;
use Think\Controller;
use BizService\ShopService;
class DhmmanageController extends BaseController {
    public $shopservice;
    public function __construct(){
        parent::__construct();
        $this->shopservice = new ShopService();
    }
  
    /**
     * 兑换码验证
     * @param unknown $dhm_code 兑换码
     * @param unknown $store_id 商户id
     * @param unknown $proxy_id 代理商id
     * 返回array(
     *     error 错误编号 0成功 1 不存在数据  2操作失败
     *     data  返回数据
     *  )
     */
    public function dhm_yanzheng(){
        $dhm_code = I("post.dhm_code",0);
        $store_id = I("post.store_id",0);
        $proxy_id = I("post.proxy_id",0);
    
        $arr=$this->shopservice->get_shop_info_by_proxy($proxy_id);
        $shop_id = $arr[0]['shop_id'];
        //处理兑换码数据

        $info = D("Home/DhmManage")->yanzheng_dhm($dhm_code,$store_id,$shop_id);
        if($proxy_id==39 &&  $info['error']==1 ){
            $info = D("Home/OldChecknum")->yanzheng_dhm($dhm_code,$store_id);
        }
        echo json_encode($info); 
    }
    /**
     * 商户兑换码列表
     * @param unknown $store_id 商户id
     * @param unknown $proxy_id 代理商id
     * @param unknown $status 兑换码状态 (可为空)
     */
    
    public function dhm_store_list(){

        $status = I("post.status",0);
        $store_id = I("post.store_id",0);
        $proxy_id = I("post.proxy_id",0);
        if(!$store_id||!$proxy_id){
            $info['error']=1;
            $info['msg']="参数不全";
        }else{
            $arr=$this->shopservice->get_shop_info_by_proxy($proxy_id);
            $shop_id = $arr[0]['shop_id'];
            //获取商户兑换码列表
            $info = D("Home/DhmManage")->list_dhm($store_id,$shop_id,$status);
        }
        if($proxy_id==39){
            $info_append = D("Home/OldChecknum")->list_dhm($store_id,$status);

            $info=array_merge($info, $info_append);
            // var_dump($info_append);exit;
        }
        //用echo输出json格式数组
        echo json_encode($info);
    }
}