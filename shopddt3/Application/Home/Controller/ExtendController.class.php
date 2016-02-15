<?php
namespace Home\Controller;
use BizService\ShopService;
use BizService\UserService;
use BizService\ExtendService;
use BizService\OrderService;
use BizService\CommissionService;

header("Content-type:text/html;charset=utf-8");

class ExtendController extends BaseController
{
    private $shop_id;
    public function _initialize(){
        parent::_initialize();
        $shop = get_shop_proxy();
        $this->shop_id = $shop['shop_id'];
    }
    public function countData($channelId,$timer=0,$type=0){
        $extend = new ExtendService();
        $Order = new OrderService();
        if(!$channelId){
            return false;
        }
        $countData = array('extendusercount'=>0,'orderscount'=>0,'dealcount'=>0,'goodscount'=>0);
        $countData['date'] = date('Y-m-d',strtotime('-'.$timer.' day'));
        
        $memberList = $extend->memberList($channelId);
        $countData['membercount'] = sizeof($memberList);
        foreach ($memberList as $key => $value) {
            foreach($value['user_list'] as $key1 => $value1){
                //得到推广生成会员的user_id
                $condition['user_id'] = $value1['user_id'];
                //输入今日时间戳，获取今日会员订单情况·
                $condition['start_time'] = strtotime($countData['date']);
                //是否只统计当日
                if($type){
                    $condition['end_time'] = $condition['start_time']+86400;
                    if($value1['reg_time']>=$condition['start_time']&&$value1['reg_time']<=$condition['end_time']){
                        $countData['extendusercount']+=1;
                    }
                }else{
                    //筛选今日注册人员
                    if($value1['reg_time']>$condition['start_time']){
                        $countData['extendusercount']+=1;
                    }
                }
                $order_List[$key][$key1]=$Order->user_order_count($condition);
                $memberList[$key]['orderscount']+=$order_List[$key][$key1]['order_num'];
                $memberList[$key]['dealcount']+=$order_List[$key][$key1]['order_money'];
                $memberList[$key]['goodscount']+=$order_List[$key][$key1]['order_goods_total'];
            }
        $countData['orderscount']+=$memberList[$key]['orderscount'];
        $countData['dealcount']+=$memberList[$key]['dealcount'];
        $countData['goodscount']+=$memberList[$key]['goodscount'];
        }
        return $countData;

    }
    public function myChannel()
    {   
        $extend = new ExtendService();
        $Order = new OrderService();
        $Commission = new CommissionService();
        $ExtendChannel = D('Admin/ExtendChannel');
        $userId = session('userId');
        $channelId = $ExtendChannel->findChannelIdByUserId($userId);
        $channelInfo = $ExtendChannel->findInfoByChannelId($channelId);
        $countData['today'] = $this->countData($channelId,0);
        $countData['today']['incomecount'] = D('Home/CommissionOrder')->get_shouyi_total(1,$channelId,2);
        $param=array();
        $param['channel_id']=$channelId;
        $param['shop_id']=$this->shop_id;   
        $countData['is_paid']=$Commission->ChannelPay_money($param); 
        
        $temp1 = $Commission->Channel_no_money($param);//未确认,获取渠道未确认总额
        $temp2 = $Commission->channel_list_first_money($param);//未结算,获取渠道未结算列表以及总额
        $temp3 = $Commission->channel_list_second_money($param);//未支付,获取渠道已结算未支付列表及总额
        $countData['wait_pay'] = $temp1+$temp2+$temp3;
        // var_dump($temp1);
        // var_dump($temp2);
        // var_dump($temp3);
        // exit;
        // var_dump($countData); 
        $this->assign('countdata', $countData);
        $this->assign('channelinfo', $channelInfo);
        
        $this->display();
    }

    // public function myExtend()
    // {
    //     $Extend = new ExtendService();
    //     $userId = session('userId');channeldataCount

    //     $ExtendChannel = D('Admin/ExtendChannel');
    //     $extendInfo = $ExtendChannel->findInfoByUserId($userId);
    //     $userName = session('userName');
    //     $userName = substr($userName, 0,3).'****'.substr($userName,7);
    //     $extendInfo = array_merge($extendInfo,$Extend->memberInfoform($extendInfo));

    //     // var_dump($extendInfo);
    //     $this->assign('user_name', $userName);
    //     $this->assign('extend_info', $extendInfo);
    //     $this->display();
    // }
    public function personList()
    {
        $extend = new ExtendService();
        $Order = new OrderService();
        $ExtendChannel = D('Admin/ExtendChannel');
        $userId = session('userId');
        $countData = array();
        $channelId = $ExtendChannel->findChannelIdByUserId($userId);
        $channelInfo = $ExtendChannel->findInfoByChannelId($channelId);
        if($channelId){
            $memberList = $extend->memberList($channelId);
            $countData['membercount'] = sizeof($memberList);
            foreach ($memberList as $key => $value) {
                foreach($value['user_list'] as $key1 => $value1){
                    if(1){
                        $condition['user_id'] = $value1['user_id'];
                        $order_List[$key][$key1]=$Order->user_order_count($condition);
                        $memberList[$key]['orderscount']+=$order_List[$key][$key1]['order_num'];
                        $memberList[$key]['dealcount']+=$order_List[$key][$key1]['order_money'];
                        $memberList[$key]['goodscount']+=$order_List[$key][$key1]['order_goods_total'];
                    }
                }
                // var_dump($value['user_list']);
                $memberList[$key]['username'] = substr($value['username'], 0,3).'****'.substr($value['username'],7);
            }
            // var_dump($memberList);exit;
            $this->assign('member_list', $memberList);
            $this->assign('countdata', $countData);
        }

        $this->display();
    }

    //修改分成比例页面
    public function editDistribute(){
        $ExtendChannel = D('Admin/ExtendChannel');
        $userId = session('userId');
        $channelId = $ExtendChannel->findChannelIdByUserId($userId);
        $channelInfo = $ExtendChannel->findInfoByChannelId($channelId);
        $this->assign('distribute',$channelInfo['distribute']);
        $this->display();
    }
        //修改分成比例
    public function actEditDistribute(){
        $extend = new ExtendService();
        $ExtendChannel = D('Admin/ExtendChannel');
        $userId = session('userId');
        $channelId = $ExtendChannel->findChannelIdByUserId($userId);
        $channelData = array();
        $channelData['distribute'] = I('post.distribute',0,'intval');
        if(D('Admin/ExtendChannel')->updateExtend($channelId, $channelData)){
             echo json_encode(array("error" => 0, "msg" => "渠道佣金分成比例设置为".$channelData['distribute']."%"));exit();
        }else{
             echo json_encode(array("error" => 1, "msg" => "设置渠道佣金分成比例失败!"));exit();
        }
    }
    //用户注册发送短信验证
    public function getCode_addextend()
    {
        $user = new UserService();
        $extend = new ExtendService();
        session('code', I('session.code', ''));
        $phone_num = I('post.username', '', 'strval');
        if (!preg_match('/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/', $phone_num)) {
            echo json_encode(array("code" => 0, "msg" => "请输入合法手机号码~!"));
            exit();
        } else {
            $userId = $user->checkUser($phone_num);
            if ($userId) {
                if(D('Admin/ExtendChannel')->findExtendIdByUserId($userId)){
                    echo json_encode(array("code" => 3, "msg" => "该人员已成为点点通旗下的推广人员~!"));
                    exit();
                }
            }else{
                echo json_encode(array("code" => 3, "msg" => "该手机号码未注册~!"));
                exit();
            }
            $code = strval(rand(1000, 9999));
            $data = "您好,您的推广人员申请验证码是：" . $code . " 欢迎您即将成为点点通商城的渠道推广人员,请勿将验证码告知他人!";
            if (!I('session.send_time', '')) {
                if (time() - session('send_time') < 30) {
                    $again_time = 30 - (time() - session('send_time'));
                    echo json_encode(array("code" => 0, "msg" => "请求过于频繁,请等待" . $again_time . "秒"));
                    exit();
                }
            }
            session('code', $code . "_" . $phone_num);
            // $co = $user->sendMsgForUser($phone_num, $data);
            // session('send_time', time());//重新赋值时间
            // if ($co == '0') {
                echo json_encode(array("code" => 1, "msg" => $data));
                exit();
            // }
        }
    }
    //核对验证码
    public function checkCode()
    {
        $user = new UserService();
        $phone_num = I('post.username', '', 'strval');
        $check_code = I('post.check_code', '', 'strval');
        echo $user->checkCode($phone_num, $check_code);
    }
    public function addExtend()
    {   
        $extend = new ExtendService();
        $userId = session('userId');
        $channelId = D('Admin/ExtendChannel')->findChannelIdByUserId($userId);
        $userName = I('post.username','','strval');
        $goodsCode = I('post.goodscode','','strval');
        $memberName = I('post.membername','','strval');
        return $extend->addExtendMember($channelId,$userName,$goodsCode,$memberName);
    }
    public function recentInfo(){

        $user = new UserService();
        $type = I('get.type','','strval');
        $userId = session('userId');
        $query=array('reg_time');
        $userInfo = $user->userInfo($userId ,$query);
        $days = intval(round((time()-$userInfo['reg_time'])/86400));
        $days = $days>30?30:$days;
        $channelId = D('Admin/ExtendChannel')->findChannelIdByUserId($userId);
        if($type != 'dealcount'){
            $countData['today'] = $this->countData($channelId,0);
            $countData['week'] = $this->countData($channelId,7);
            $countData['month'] = $this->countData($channelId,30);
            $recentData = array();

            for($i=0;$i<$days;$i++){
               $recentData[$i] =  $this->countData($channelId,$i,1);
            }

            $this->assign('count_data',$countData);
            $this->assign('recent_data',$recentData);
        }
        // var_dump($countData);

        if($type == 'membercount'){
            $this->display('recentRegister');
        }else if($type == 'ordercount'){
            $this->display('recentOrder');
        }else if($type == 'dealcount'){
            $Commission = new CommissionService();
            $income = D('Home/CommissionOrder')->get_shouyi_list($channelId,1,30,2);
            // var_dump($income);exit;
            $countData['today'] = D('Home/CommissionOrder')->get_shouyi_total(1,$channelId,2);
            $countData['week'] = D('Home/CommissionOrder')->get_shouyi_total(7,$channelId,2);
            $countData['month'] = D('Home/CommissionOrder')->get_shouyi_total(30,$channelId,2);
            // var_dump($countData);exit;
            $this->assign('count_data',$countData);
            $this->assign('recent_data',$income);
            $this->display('recentIncome');
        }
    }

    public function waitPay(){
        $Commission = new CommissionService();
        $userId = session('userId');
        $channelId = D('Admin/ExtendChannel')->findChannelIdByUserId($userId);
        $param=array();
        $param['channel_id']=$channelId;
        $param['shop_id']=$this->shop_id;  
        $waitPayList=$Commission->channel_list_first($param);
        $count = array();
        $count['daiqueren'] = $Commission->Channel_no_money($param);//待确认,获取渠道未确认总额
        $count['daijiesuan'] = $Commission->channel_list_first_money($param);//待结算,获取渠道未结算列表以及总额
        $count['yijiesuan'] = $Commission->channel_list_second_money($param);//未支付,获取渠道已结算未支付列表及总额
        $this->assign('wait_pay_list', $waitPayList);
        $this->assign('count', $count);
        // var_dump($waitPayList);exit;

        $this->display();
    }
    public function isPaid(){
        $Commission = new CommissionService();
        $userId = session('userId');
        $channelId = D('Admin/ExtendChannel')->findChannelIdByUserId($userId);
        $param=array();
        $param['channel_id']=$channelId;
        $param['shop_id']=$this->shop_id;  
        $isPaidList=$Commission->channel_list($param);
        $isPaidMoney=$Commission->ChannelPay_money($param); 
        $this->assign('is_paid_list', $isPaidList['list']);
        $this->assign('paid_money', $isPaidMoney);

        // var_dump($isPaidList);exit;
        $this->display();
    }

    public function paymentInfo(){
        $ExtendChannel = D('Admin/ExtendChannel');
        $userId = session('userId');
        $extendInfo = $ExtendChannel->findInfoByUserId($userId);
        $info = json_decode($extendInfo['payment'],true);
        // var_dump($info);exit;
        $this->assign('info',$info);
        $this->display();
    }

    public function updatePaymentInfo(){
        $ExtendChannel = D('Admin/ExtendChannel');
        $userId = session('userId');
        $extendId = $ExtendChannel->findExtendIdByUserId($userId);
        $info = array();
        $info['pay_type'] = I('post.pay_type','','strval');
        $info['true_name'] = I('post.true_name','','strval');
        $info['user_name'] = I('post.user_name','','strval');
        $info['remark'] = I('post.remark','','strval');
        $data['payment'] = json_encode($info);
        if($ExtendChannel->updateExtend($extendId,$data)){
            echo "<script>alert('修改个人支付信息成功!');window.location.href='/home/Commissionorder/tgrindex';</script>";
        }else{
            echo "<script>alert('修改个人支付信息失败!');window.location.href='/home/Commissionorder/tgrindex';</script>";
        }
        
    }
    public function location()
    {
        $this->display('location');
    }

}