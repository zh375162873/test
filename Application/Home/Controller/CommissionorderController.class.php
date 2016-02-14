<?php

namespace Home\Controller;

use BizService\GeohashService;
use Org\Util\Date;
use BizService\UserService;
use BizService\CommissionService;
use BizService\ExtendService;

header("Content-type:text/html;charset=utf-8");

class CommissionorderController extends BaseController
{
    public $commission_order_db,$userid,$extend_channel_db,$userservice_db,$CommissionService,$shop_id,$extendservice;
    public function _initialize(){
        parent::_initialize();
        $this->commission_order_db = D("CommissionOrder");
        $this->extend_channel_db = D("Admin/ExtendChannel");
        $this->userservice_db = new UserService();
        $this->CommissionService = new CommissionService();
        $this->extendservice = new ExtendService();
        if($_GET['userid']){
            $this->userid = $_GET['userid'];
            $ExtendChannel = D('Admin/ExtendChannel');
            $sess_userId = session('userId');
            $channelId = $ExtendChannel->findChannelIdByUserId($sess_userId);
            if(empty($channelId)){
                echo "<script>alert('该用户不是你的推广人员！');window.history.back();</script>";
            }
            $channelInfo = $ExtendChannel->channeldataCount($channelId);
            $type = false;
            foreach ($channelInfo as $key => $value) {
                if($value['user_id'] ==$this->userid){
                    $type = true;
                }
            }
            if($type){
               $this->assign("userid",$this->userid);
            }
        }else{
            $this->userid = session('userId');
           
        }
        $shop_proxy =  get_shop_proxy();
        $this->shop_id = $shop_proxy['shop_id'];
    }
    
    //推广人首页
    public function tgrindex(){
        $param = array(
            "shop_id" => $this->shop_id,
            "referee_id" => $this->userid,
        );
        //计算注册人统计信息
        $extend_id = D('Admin/ExtendChannel')->findExtendIdByUserId($this->userid,1);
        $today_zhuce = $this->extendservice->countReg($extend_id,0);
        $today_order = $this->commission_order_db->get_order_total(1,$this->userid);
        $today_shouyi = $this->commission_order_db->get_shouyi_total(1,$this->userid);
        $dzf = $this->commission_order_db->daipay_total($this->userid,1,"dzf");
        $extend_info = $this->extend_channel_db->findInfoByUserId($this->userid);
        $extend_info['payment'] = json_decode($extend_info['payment'],true);
        // var_dump($extend_info);exit;
        //$yzf = $this->commission_order_db->daipay_total($this->userid,1,"yzf");
        $yzf = $this->CommissionService->ChannelmemberPay_money($param);
        $userinfo = $this->userservice_db->userInfo($this->userid);
        $user['nick_name'] = $userinfo['nick_name'];
        $user['user_name'] = substr_replace($userinfo['user_name'],"****",3,4); 
        
        $this->assign("user",$user);
        $this->assign("dzf",$dzf);
        $this->assign("yzf",$yzf);
        $this->assign("payment",$extend_info['payment']);
        $this->assign("today_zhuce",$today_zhuce);
        $this->assign("today_order",$today_order);
        $this->assign("today_shouyi",$today_shouyi);
        $this->display("tuiguang_mine");
    }
    //推广人注册人统计
    public function tgrzhucelist(){
        $pages = I("post.curPage",1,"intval");
        $pageNum = I("post.pageNum",15,"intval");
        
        $extend_id = D('Admin/ExtendChannel')->findExtendIdByUserId($this->userid,1);
        $days['day1'] = $this->extendservice->countReg($extend_id,1);
        $days['day7'] = $this->extendservice->countReg($extend_id,7);
        $days['day30'] = $this->extendservice->countReg($extend_id,30);
       
        $zhuce = $this->extendservice->regList($extend_id,$pageNum,$pages);
        for($i=0;$i<count($zhuce['list']);$i++){
          $zhuce['list'][$i]['reg_time'] = Date("Y-m-d",$zhuce['list'][$i]['reg_time']);
          $zhuce['list'][$i]['user_name'] = substr_replace($zhuce['list'][$i]['user_name'],"****",3,4);
        }
        
        $this->assign("list",$zhuce['list']);
        $this->assign("days",$days);
        $this->display("zuijinzhuce_mine");
    }
    
    public function ajax_tgrzhucelist(){
        $pages = I("post.curPage",1,"intval");
        $pageNum = I("post.pageNum",15,"intval");
        $zhuce = $this->extendservice->regList($this->userid,$pageNum,$pages);
        $data = $zhuce['list'];
        for($i=0;$i<count($data);$i++){
            $data[$i]['reg_time'] = Date("Y-m-d",$data[$i]['reg_time']);
            $data[$i]['user_name'] = substr_replace($data[$i]['user_name'],"****",3,4);
        }
        $this->ajaxReturn($data);
    }
    
    //推广人订单统计列表
    public function tgrorderlist(){
        //初始化数据
        $pages = I("post.curPage",1,"intval");
        $pageNum = I("post.pageNum",15,"intval");
        $daynum = I("get.daynum");
        if($daynum){
            $this->assign("daynum",$daynum);
        }
        $days['day1'] = $this->commission_order_db->get_order_total(1,$this->userid);
        $days['day7'] = $this->commission_order_db->get_order_total(7,$this->userid);
        $days['day30'] = $this->commission_order_db->get_order_total(30,$this->userid);
        $list = $this->commission_order_db->get_order_list($this->userid,$pages,$pageNum,1,$daynum);
        $this->assign("list",$list);
        $this->assign("days",$days);
        $this->display("zuijindingdan_mine");
    }
    
    public function ajax_tgrorderlist(){
        $pages = I("post.curPage",1,"intval");
        $pageNum = I("post.pageNum",15,"intval");
        $daynum = I("get.daynum");
        
        $list = $this->commission_order_db->get_order_list($this->userid,$pages,$pageNum,1,$daynum);

        $this->ajaxReturn($list);
    }
    
    //推广人收益统计列表
    public function tgrshouyilist(){
        //初始化参数
        $pages = I("post.curPage",1,"intval");
        $pageNum = I("post.pageNum",15,"intval");
        $daynum = I("get.daynum");
        if($daynum){
            $this->assign("daynum",$daynum);
        }
        
        $days['day1'] = $this->commission_order_db->get_shouyi_total(1,$this->userid);
        $days['day7'] = $this->commission_order_db->get_shouyi_total(7,$this->userid);
        $days['day30'] = $this->commission_order_db->get_shouyi_total(30,$this->userid);

        $list = $this->commission_order_db->get_shouyi_list($this->userid,$pages,$pageNum,1,$daynum);

        $this->assign("list",$list);
        $this->assign("days",$days);
        $this->display("zuijinshouyi_mine");
    }
    
    public function ajax_tgrshouyilist(){
        $pages = I("post.curPage",1,"intval");
        $pageNum = I("post.pageNum",15,"intval");
        $daynum = I("get.daynum");
        
        $list = $this->commission_order_db->get_shouyi_list($this->userid,$pages,$pageNum,1,$daynum);
        $this->ajaxReturn($list);
    }
    
    //推广人待支付
    public function tgrdzf(){
        $money = array();
        $money['dqr'] = $this->commission_order_db->daipay_info($this->userid,1,0);
        $money['djs'] = $this->commission_order_db->daipay_info($this->userid,1,1);
        $money['yjs'] = $this->commission_order_db->daipay_info($this->userid,1,2);
        
        $this->assign("money",$money);
        $this->display("daizhifu_mine");
    }
    
    //推广人已支付
    public function tgryzf(){
        $param = array(
            "shop_id" => $this->shop_id,
            "referee_id" => $this->userid,
        );
        $list  = $this->CommissionService->channelmember_list($param);
        $money = $this->CommissionService->ChannelmemberPay_money($param);
      
        for($i=0;$i<count($list['list']);$i++){
            $start_date = Date("Y/m/d",$list['list'][$i]['create_time']);
            $end_date = Date("Y/m/d",$list['list'][$i]['pay_time']);
            $list['list'][$i]['start_date'] = $start_date;
            $list['list'][$i]['end_date'] = $end_date;
        }

        $this->assign("list",$list['list']);
        $this->assign("money",$money);
       // $this->assign("money",$money);
        $this->display("yizhifu_mine");
    }
    
    public function ajax_tgryzf(){
        $param = array(
            "shop_id" => $this->shop_id,
            "referee_id" => $this->userid,
        );
        $list  = $this->CommissionService->channelmember_list($param);
        
        for($i=0;$i<count($list['list']);$i++){
            $start_date = Date("Y/m/d",$list['list'][$i]['create_time']);
            $end_date = Date("Y/m/d",$list['list'][$i]['pay_time']);
            $list['list'][$i]['start_date'] = $start_date;
            $list['list'][$i]['end_date'] = $end_date;
        }
        $data = $list['list'];
        $this->ajaxReturn($data);
    }
    
    //推广人账号设置
    public function tgrpaymode(){
        
        $this->display("paymode");
    }
    
    //我的二维码
    public function tgrerweima(){
        $arr = $this->extend_channel_db->findInfoByUserId($this->userid);
        $url = C('SHOP_URL').'/home/index/index?ddt_from='.base64_encode($arr['identity']);
     
        $this->assign("url",$url);
        $this->display("erweima");
    }
    
}