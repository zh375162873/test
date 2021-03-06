<?php
namespace Admin\Controller;
use BizService\ShareService;
use BizService\ShopService;
use Think\Upload;
use BizService\SystemService;
class ShopController extends BaseController {
    public function _initialize(){
        parent::_initialize();
    }


    /**
     * 获取商城列表
     */
    public function shoplist(){

    }

    /**
     * 获取属性设置
     */
    public function shop_set(){
        $shop = get_shop_proxy();
        $shop_service = new ShopService();
        if(IS_POST){
            $data['shop_id'] = $shop['shop_id'];
            $data['shop_name'] = I("shopName",'','string');
            $data['shop_company_name'] = I("company_name",'','string');
            $data['shop_img'] = I("shop_img",'','string');
            $data['shop_title'] = I("shoptitle",'','string');
            $data['shop_keywords'] = I("keywords",'','string');
            $data['kefu_phone'] = I("kefu_telephone",'','string');
            $data['shop_phone'] = I("telephone",'','string');
            $data['shop_email'] = I("email",'','string');
            $data['shop_address'] = I("address",'','string');
            $result = $shop_service->update_shop($data);
            if(false!==$result){
                $this->success("修改成功!");
            }else{
                $this->error("修改失败,请重试!");
            }
        }else{
            $shop_info = $shop_service->get_shop_info($shop['shop_id']);
            $this->assign("shop",$shop_info[0]);
            $this->display();
        }
    }

    /**
     * 显示设置
     */
    public function show_set(){
        $system = new SystemService();
        $shop = get_shop_proxy();
        if(IS_POST){
            $is_show_out = I("is_show_out")=="on"?1:0;
            $is_show_expired = I("is_show_expired")=="on"?1:0;
            $row = $system->update_set($shop['shop_id'],"SHOW_SOLD_OUT",$is_show_out);
            $row1 = $system->update_set($shop['shop_id'],"SHOW_EXPIRED",$is_show_expired);
            if(false!==$row || false!==$row1){
                $this->success("设置成功！");
            }else{
                $this->error("设置失败，请重试！");
            }
        }else{
            $show['is_show_out'] = $system->get_code($shop['shop_id'],'SHOW_SOLD_OUT');//在商品列表中显示已售完的商品
            $show['is_show_expired'] = $system->get_code($shop['shop_id'],'SHOW_EXPIRED');//在商品列表中显示已过期的商品
            $this->assign("show",$show);
            $this->display();
        }
    }

    /**
     * 系统设置
     */
    public function sys_set(){
        $system = new SystemService();
        $shop = get_shop_proxy();
        if(IS_POST){
            $auto_confirm_time = I("auto_confirm_time",15,'intval');
            $row = $system->update_set($shop['shop_id'],"ORDER_AUTO_RECEIVE",$auto_confirm_time);
            if(false!==$row){
                $this->success("设置成功！");
            }else{
                $this->error("设置失败，请重试！");
            }
        }else{
            $show['auto_confirm_time'] = $system->get_code($shop['shop_id'],'ORDER_AUTO_RECEIVE');
            $this->assign("info",$show);
            $this->display();
        }
    }
    /**
     * 分享设置
     */
    public function share_set(){
        $shop = get_shop_proxy();
        $share_service = new ShareService();
        if(IS_POST){
            $data['shop_id'] = $shop['shop_id'];
            $data['shop_thumb'] = I("upload_validate",'','string');
            $data['shop_title'] = I("shoptitle",'','string');
            $data['shop_desc'] = I("share_desc",'','string');
            $data['goods_type'] = I("goods_type",'','intval');
            $data['goods_thumb'] = I("goods_thumb_upload_validate",'','string');
            $data['goods_title'] = I("goods_prefix",'','string');
            $data['is_show_title'] = I("is_show_title",'','string')=="on"?1:0;
            $data['goods_desc'] = I("goods_share_desc",'','string');

            if($data['shop_id']==""){
                $this->error("参数错误，请重新登录！");
            }
//            if($data['shop_thumb']==""){
//                $this->error("请上传分享商城的缩略图！");
//            }
            if($data['shop_title']==""){
                $this->error("请上传分享商城的标题！");
            }
            if($data['shop_desc']==""){
                $this->error("请上传分享商城的描述！");
            }
            if($data['goods_type']==1){
                if($data['goods_thumb']==""){
                    $this->error("请上传分享商品的缩略图！");
                }
            }
            if($data['goods_title']==""){
                $this->error("请上传分享商品的标题！");
            }
            if($data['goods_desc']==""){
                $this->error("请上传分享商品的描述！");
            }
            $row = $share_service->share_set($data);
            if(false!==$row){
                $this->success("设置成功！");
            }else{
                $this->error("设置失败！");
            }
        }else{
            $share = $share_service->get_share_set($shop['shop_id']);
            $this->assign("share",$share[0]);
            $this->display();
        }
    }

    /* 文件上传 */
    public function upload(){
        /* 返回标准数据 */
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');

        //设置上传参数
        $config['maxSize'] = 3145728 ;// 设置附件上传大小
        $config['exts']  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $config['rootPath'] = './Uploads/'; // 设置附件上传根目录
        $config['autoSub'] = false;
        $config['savePath'] = 'share/'; // 设置附件上传（子）目录
        /* 调用文件上传组件上传文件 */
        $upload = new Upload($config);

        $info = $upload->upload();

        /* 记录图片信息 */
        if($info){
            $return['status'] = 1;
            $return = array_merge($info['download'], $return);
        } else {
            $return['status'] = 0;
            $return['info']   = $upload->getError();
        }

        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }
}