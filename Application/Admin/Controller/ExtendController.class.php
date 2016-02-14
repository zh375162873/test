<?php
namespace Admin\Controller;
use BizService\ExtendService;
use BizService\GoodsService;
use BizService\OrderService;
use BizService\UserService;
use BizService\CommissionService;


class ExtendController extends BaseController {
        public $extend;
    public function _initialize(){
        parent::_initialize();
        // $shop = new ShopService;
        // if('ture'!=I('get.houtai',''))
        //     $shop->checkShop();

        // $extend = new ExtendService();
    }

    public function index(){
    	$search = I('post.search','','strval');
    	$extend = new ExtendService();
        $Order = new OrderService;
    	// var_dump($_SESSION);exit;
        $userName = trim(I('get.username','','strval'));
        $orderName = trim(I('get.ordername','','strval'));
        $orderType = trim(I('get.ordertype','','strval'));

        $searchForm['username'] = $userName;
        $searchForm['ordername'] = $orderName;
        $searchForm['ordertype'] = $orderType;

        $condition = array();
        //初始化所有推广人员个数
        $memberCount = 0;
        $proxyId=session('proxyId');
        $condition['proxy_id'] = $proxyId;
    	$channelList = $extend->channelList($searchForm);
        foreach ($channelList as $key => $value) {
            foreach ($value['channeldata'] as $key1 => $value1) {
                $channelList[$key]['membercount']=$key1+1;
                $channelList[$key]['extendcount'] += $value1['extend'];
                foreach($value1['user_list'] as $key2 => $value2){
                    $condition['user_id'] = $value2['user_id'];

                    $order_List[$key][$key1][$key2]=$Order->user_order_count($condition);
                    $channelList[$key]['orderscount']+=$order_List[$key][$key1][$key2]['order_num'];
                    $channelList[$key]['dealcount']+=$order_List[$key][$key1][$key2]['order_money'];
                    $channelList[$key]['goodscount']+=$order_List[$key][$key1][$key2]['order_goods_total'];
                }
                // dump($order_List[$key][$key1][$key2]);
                
            // dump($value1['user_id']);
            }
            $channelList[$key]['ave_extendcount'] = sprintf("%.3f", $channelList[$key]['extendcount']/$channelList[$key]['membercount']);
            $channelList[$key]['ave_orderscount'] = sprintf("%.3f", $channelList[$key]['orderscount']/$channelList[$key]['membercount']);
            $channelList[$key]['ave_dealcount'] = sprintf("%.3f", $channelList[$key]['dealcount']/$channelList[$key]['membercount']);
            //计算所有推广人员个数
            $memberCount += $channelList[$key]['membercount'];
            // dump($value['channeldata']);
        }
        if(!($searchForm['ordername'] == 'name'||$searchForm['ordername'] == 'user_name'||$searchForm['ordername'] == 'add_time')){
            $channelList = $extend->array_sort($channelList,$searchForm['ordername'],$searchForm['ordertype']);
        }
    	// var_dump($channelList);exit;
        // exit;   
        $pageList = array_page($channelList);
        // var_dump($pageList);exit;
        $this->assign('search_form',$searchForm);
        $this->assign('member_count',$memberCount);
        $this->assign('channel_list',$pageList['list']);
        $this->assign('page',$pageList['page']);
        $this->assign('count',$pageList['count']);
        $this->display();
    }

    //代理商后台同步登出
    public function jumpOut(){
        //销毁session
        session('proxyKeyStatus',null);
        //跳转核心平台主页
        $url='location:'.C('WIFI_URL').'/proxy/default/index';

        header($url);
    }
    public function addChannel(){
    	$channelName = I('post.channelname','','strval');
    	$userName = I('post.username','','strval');
        $memberName = I('post.membername','','strval');
        $distribute = I('post.distribute',10,'intval');
        $extend = new ExtendService();
        return $extend->addChannel($channelName,$userName,$memberName,$distribute);
    }

    public function addExtendMember(){
        $channelId = I('post.channelid',0,'intval');
        $userName = I('post.username','','strval');
        $goodsCode = I('post.goodscode','','strval');
        $memberName = I('post.membername','','strval');
        $extend = new ExtendService();
        return $extend->addExtendMember($channelId,$userName,$goodsCode,$memberName);
    }
    //返回推广人员列表  
    public function memberList(){
        $extend = new ExtendService();
        $Order = new OrderService;

        $channelId = I('get.channelid',-1,'intval');
        $userName = trim(I('get.username','','strval'));
        $orderName = trim(I('get.ordername','','strval'));
        $orderType = trim(I('get.ordertype','','strval'));

        $searchForm['channelid'] = $channelId;
        $searchForm['username'] = $userName;
        $searchForm['ordername'] = $orderName;
        $searchForm['ordertype'] = $orderType;
        $condition = array();
        $proxyId=session('proxyId');
        $condition['proxy_id'] = $proxyId;

        $channelList = $extend->channelList();
        $memberList = $extend->memberList($channelId,$searchForm);

        foreach ($memberList as $key => $value) {
            foreach($value['user_list'] as $key1 => $value1){
			    //渠道推广统计数据
				$shop_info=get_shop_proxy();
				$param=array();
				$param['shop_id']=$shop_info['shop_id'];
				$param['referee_id']=$value['user_id'];
				$CommissionService = new CommissionService();
				//待结算信息
		        $channelmember_list_first=$CommissionService->channelmember_list_first($param); 
				//待支付信息
				$channelmember_list_second=$CommissionService->channelmember_list_second($param); 

                $condition['user_id'] = $value1['user_id'];
                $order_List[$key][$key1]=$Order->user_order_count($condition);
                $memberList[$key]['orderscount']+=$order_List[$key][$key1]['order_num'];
                $memberList[$key]['dealcount']+=$order_List[$key][$key1]['order_money'];
                $memberList[$key]['goodscount']+=$order_List[$key][$key1]['order_goods_total'];
				$memberList[$key]['channelmember_list_first']=$channelmember_list_first;
				$memberList[$key]['channelmember_list_second']=$channelmember_list_second;
            }
        }

        if($searchForm['ordername'] == 'goodscount'||$searchForm['ordername'] == 'orderscount'||$searchForm['ordername'] == 'dealcount'||$searchForm['ordername'] == 'extend_goods'){
            $memberList = $extend->array_sort($memberList,$searchForm['ordername'],$searchForm['ordertype']);
        }
        // exit;
        $pageList = array_page($memberList);

        // var_dump(session('proxyId'));exit;   
        // var_dump($memberList);exit;  
		
		
		
        $this->assign('search_form',$searchForm);
        $this->assign('channel_list',$channelList);
        $this->assign('member_list',$pageList['list']);
        $this->assign('page',$pageList['page']);
        $this->assign('count',$pageList['count']);
        $this->display();
    }

    public function editMember(){
        $extendId = I('post.extendid','','strval');
        $channelId = I('post.channelid','','strval');
        $goodsCode = I('post.goodscode','','strval');
        $memberName = I('post.membername','','strval');
        $extend = new ExtendService();
        return $extend->editMember($channelId,$extendId,$goodsCode,$memberName);
    }

    public function editChannel(){
        $channelId = I('post.channelid',0,'intval');
        $channelName = I('post.channelname','','strval');
        $userName = I('post.username','','strval');
        $memberName = I('post.membername','','strval');
        $distribute = I('post.distribute',10,'intval');
        $extend = new ExtendService();
        return $extend->editChannel($channelId,$channelName,$userName,$memberName,$distribute);
    }

    //删除渠道账号
    public function deleteChannel(){
        $extend = new ExtendService();
        $channelId = I('post.id',0);
        $res = 0;
        if($channelId)
            $res=$extend->deleteChannel($channelId);
        if($res){
            echo json_encode(array("error"=>0,"msg"=>"删除用户成功!"));
        }else{
            echo json_encode(array("error"=>1,"msg"=>"删除用户失败!"));
        }
    }

    //删除推广人员账号
    public function deleteMember(){
        $extend = new ExtendService();
        $memberId = I('post.id',0);
        $res = 0;
        if($memberId)
            $res=$extend->deleteMember($memberId);
        echo $res?'删除推广人员成功':'删除推广人员失败';
    }

    public function editGoodsCode(){
        $extendId = I('post.extendid',0,'intval');
        $goodsCode = I('post.goodscode','','strval');
        $extend = new ExtendService();
        return $extend->editGoodsCode($extendId,$goodsCode);
    }

    // public function extendInfo(){
    //     $extend = new ExtendService();
    //     $channelId = I('post.id',0,'intval');
    //     $data = $extend->extendInfo($channelId);
    //     return $data;
    // }
    public function extendGoodsList(){
        //获取参数
        $extendId = I('get.extendId',0,'intval');
        $orderName = trim(I('get.ordername','','strval'));
        $orderType = trim(I('get.ordertype','','strval'));
        $searchForm['ordername'] = $orderName;
        $searchForm['ordertype'] = $orderType;

        $extend = new ExtendService();
        $good = new GoodsService();
        $Order = new OrderService();
        //输出channel_goods表中数据
        $list=$extend->extendGoodsList($extendId);
        //根据货号调用商品接口，找到商品名称、价格、市场价、库存、上架状态并计算出优惠价
        foreach ($list as $key => $value) {
            $arr = $good->getGoodsBySerial($list[$key]['goods_sn']);
            $list[$key]['goods_name'] = $arr['goods_name'];
            $list[$key]['goods_price'] = $arr['goods_price'];
            $list[$key]['goods_marketprice'] = $arr['goods_marketprice'];
            $list[$key]['goods_storage'] = $arr['goods_storage'];
            $list[$key]['goods_state'] = $arr['goods_state'];
            $list[$key]['begin_time'] = date("Y-m-d",$list[$key]['begin_time']);
            $list[$key]['end_time'] = date("Y-m-d",$list[$key]['end_time']);
            $list[$key]['extend_price'] = $arr['goods_price']*$list[$key]['discount']/100;
            
            $OrderInfo[$key]=$Order->extend_order($extendId,$list[$key]['goods_id']);
            // var_dump($OrderInfo[$key]);
            $list[$key]['buy_count'] = $OrderInfo[$key]['order_goods_total'];
        }
        // exit;
        //调用BaseService中的排序函数进行数组排序
        $extendGoodsList = $extend->array_sort($list,$orderName,$orderType);
        //分页
        $pageList = array_page($extendGoodsList);
        //输出
        $this->assign('extendid',$extendId);
        $this->assign('search_form',$searchForm);
        $this->assign('goods_list',$pageList['list']);
        $this->assign('page',$pageList['page']);
        $this->assign('count',$pageList['count']);
        $this->display();
    }
    //增加推广优惠商品
    public function addExtendGoods(){
        $data = array();
        $data = I('post.goodsData');
        $data['extendId'] =  I('get.extendid',0,'intval');
        $extend = new ExtendService();
        return $extend->addExtendGoods($data);
    }
    //修改推广优惠商品
    public function editExtendGoods(){
        $data = array();
        $id =  I('post.id',0,'intval');
        $data = I('post.goodsData');
        $extend = new ExtendService();
        return $extend->editExtendGoods($id,$data);
    }

    //删除推广优惠商品
    public function deleteExtendGoods(){
        $extend = new ExtendService();
        $extendGoodsId = I('post.id',0);
        $res = 0;
        if($extendGoodsId)
            $res=$extend->deleteExtendGoods($extendGoodsId);
        echo $res?'删除推广优惠商品成功':'删除推广优惠商品失败';
    }
    public function extendGoodsOrder(){
        $orderName = trim(I('get.ordername','','strval'));
        $orderType = trim(I('get.ordertype','','strval'));
        $searchForm['ordername'] = $orderName;
        $searchForm['ordertype'] = $orderType;
        $extendId = I('get.extendid',0,'intval');
        $goodsId = I('get.goodsid',0,'intval');
        $Order = new OrderService();
        if($goodsId){
            $orderList=$Order->extend_order($extendId,$goodsId);
        }else{
            $orderList=$Order->extend_order($extendId);
        }
        $orderList['list'] = $Order->array_sort($orderList['list'],$orderName,$orderType);
        $pageList = array_page($orderList['list']);
        // var_dump($orderList['list'][0]['dhm']);exit;
        // var_dump($orderList['list'][0]['goods']);exit;
        // var_dump($pageList);exit;            

        $this->assign('search_form',$searchForm);
        $this->assign('order_list',$pageList['list']);
        $this->assign('extendid',$extendId);
        $this->assign('goodsid',$goodsId);
        $this->assign('page',$pageList['page']);
        $this->assign('count',$pageList['count']);
        $this->display('goodsOrder');
    }
    public function extendUserList(){
        $extenduserId = I('get.extenduserid',0,'intval');
        $condition = array();
        $proxyId=session('$proxyId');
        $condition['proxy_id'] = $proxyId;
        $User = new UserService();
        $Order = new OrderService();
        if(!$extenduserId)
            exit;
        
        $user_List=$User->extendUserList($extenduserId);
        foreach ($user_List as $key => $value) {

            $condition['user_id'] = $value['user_id'];
            $order_List[$key]=$Order->user_order_count($condition);
            $user_List[$key]['order_num']=$order_List[$key]['order_num'];
            $user_List[$key]['order_money']=$order_List[$key]['order_money'];
        }
        // var_dump($user_List);
        // var_dump($order_List);
        // exit;
        $pageList = array_page($user_List);

        $this->assign('search_form',$searchForm);
        $this->assign('user_list',$pageList['list']);
        $this->assign('extendid',$extendId);
        $this->assign('goodsid',$goodsId);
        $this->assign('page',$pageList['page']);
        $this->assign('count',$pageList['count']);
        $this->display();
    }
    public function OrderList(){
        $extenduserId = I('get.extenduserid',0,'intval');
        $User = new UserService();
        $Order = new OrderService();
        if(!$extenduserId)
            exit;
        $user_List=$User->extendUserList($extenduserId);
        $orderList=array();
        foreach ($user_List as $key => $value) {
            $order_List[$key]=$Order->user_order_list($user_List[$key]['user_id']);
            $orderList= array_merge($orderList, $order_List[$key]);
        // var_dump($orderList[$key]);
        }

        // var_dump($user_List);
        // exit;
        $pageList = array_page($orderList);
        $this->assign('search_form',$searchForm);
        $this->assign('order_list',$pageList['list']);
        $this->assign('extendid',$extendId);
        $this->assign('goodsid',$goodsId);
        $this->assign('page',$pageList['page']);
        $this->assign('count',$pageList['count']);
        $this->display('goodsOrder');

    }
}