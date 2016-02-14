<?php

namespace BizService;

/**
 * 接口操作
 *
 * @author 王春一
 */
class OthershareService extends BaseService
{
    
    /**
     * 兑换码验证
     * @param unknown $dhm_code 兑换码
     * @param unknown $store_id 商户id
     * 返回array(
     *     error 错误编号 0成功 1 不存在数据  2操作失败
     *     data  返回数据
     *  )
     */
    public function dhm_yanzheng($dhm_code,$store_id,$proxy_id){

       $shop=new ShopService();
       $arr=$shop->get_shop_info_by_proxy($proxy_id);
       $shop_id = $arr[0]['shop_id'];
      
        //处理兑换码数据
       $arr = D("Home/DhmManage")->yanzheng_dhm($dhm_code,$store_id,$shop_id);
       return $arr; 
    }
    /**
     * 商城兑换码列表
     * @param unknown $store_id 商户id
     * @param unknown $proxy_id 代理商（不能为空）
     * @param unknown $status 兑换码状态
     */
    
    public function dhm_shop_list($proxy_id,$store_id,$status){
        $data=array();
        if(!$proxy_id){
            $data['error']=1;
            $data['msg']="参数不全";
        }else{
            $shop=new ShopService();
            $arr=$shop->get_shop_info_by_proxy($proxy_id);
            $shop_id = $arr[0]['shop_id'];
            //获取商户兑换码列表
            $info = D("Home/DhmManage")->list_shop_dhm($shop_id,$store_id,$status);
            if($proxy_id==39){
                $info_old = D("Home/OldChecknum")->list_dhm();
                // var_dump($info_old);exit;
                foreach ($info_old as $key => $value) {
                    $info_old[$key]['order_sn'] = '老商城';
                    $info_old[$key]['goods_serial'] = '老商城';
                }
                $info = array_merge($info,$info_old);
            }
            // var_dump($info);exit;
            if(!empty($info)){
                $data['error']=0;
                $data['msg']="成功！";
                $data['info']=$info;
            }else{
                $data['error']=2;
                $data['msg']="数据为空";
            }
        }


        //用echo输出json格式数组
        return json_encode($data);
    }
    /**
     * 统计核销码
     * $condition 数组
     * @param unknown user_id
     * @param unknown proxy_id
     * @param unknown start_time
     * @param unknown end_time
     * @param unknown status 1未消费的2已消费的3有退款的4退款完成
     * @param unknown goods_type 1默认商品2积分商品3现金商品
     */
    public function dhm_count($condition){
        $data=array();
        if(!empty($condition['user_id'])){
            $data['buyer_id']=$condition['user_id'];
        }
        if(!empty($condition['proxy_id'])){
            $shopservice = new ShopService();
            $arr=$shopservice->get_shop_info_by_proxy($condition['proxy_id']);
            $shop_id = $arr[0]['shop_id'];
            $data['shop_id'] = $shop_id;
        }
        
        if(!empty($condition['start_time'])&&!empty($condition['end_time'])){
            $data['add_time'] = array(array("gt",$condition['start_time']),array("lt",$condition['end_time']));
        }
        elseif(!empty($condition['start_time'])&&empty($condition['end_time'])){
            $data['add_time'] = array("gt",$condition['start_time']);
        }
        elseif(empty($condition['start_time'])&&!empty($condition['end_time'])){
            $data['add_time'] = array("lt",$condition['end_time']);
        }
        if(!empty($condition['status'])){
            if($condition['status']==1){
                $data['status']=0;
                $data['refund_status']=0;
            }elseif ($condition['status']==2){
                $data['status']=1;
                $data['refund_status']=0;
            }elseif($condition['status']==3){
                $data['status']=0;
                $data['refund_status']=1;
            }elseif($condition['refund_status']==4){
                $data['status']=0;
                $data['refund_status']=2;
            }
        }
        $data['type']=1;
        
        $Dhm_count = D("Home/DhmManage")->where($data)->count();
        return $Dhm_count;
    }
    
    
}