<?php

namespace BizService;

/**
 * 多商城Service
 *
 * @author 谢林
 */
class ExtendService extends BaseService
{      
    

    /**
     * 代理商平台登录状态判断
     */
    public function checkShop()
    {
        if (session('proxyKeyStatus') != 'on') {
            $url = 'location:' . C('WIFI_URL') . '/proxy/default/index';
            header($url);
            exit;
        }
    }


    /**
     * 渠道列表
     * @return array
     */
    public function channelList($condition = null)
    {   
        $channelData = D('Admin/ExtendChannel')->channelList($condition);
        // $data = self::get_shop_info_by_proxy(79);
        // 增加未分组渠道
        $channelCount = sizeof($channelData);
        if(!$condition['username']){
            $channelData[$channelCount]['id']='0';
            $channelData[$channelCount]['name']='未分组';
            $channelData[$channelCount]['username']='————';
            $channelData[$channelCount]['user_name']='————';
            $channelData[$channelCount]['add_time']='————';
        }

        // dump($channelCount);exit;
        foreach ($channelData as $key => $value) {
            $channelData[$key]['username'] = D('Admin/Users')->findUserNameById($value['user_id']);  
            $channelData[$key]['channeldata'] = D('Admin/ExtendChannel')->channeldataCount($value['id']); 
            foreach ($channelData[$key]['channeldata'] as $key1 => $value1) {
               $channelData[$key]['channeldata'][$key1]['user_list']=D('Admin/Users')->extendUserList($value1['user_id']);
            // dump($channelData[$key]['channeldata'][$key1]['user_list']); 
            }
            // dump($channelData[$key]['channeldata']); 
        }
        // dump($channelData); 
        // exit;
        return $channelData;

    }

    /**
     * 推广人员列表
     * @param $channelId        渠道Id
     * @return array
     */
    public function memberList($channelId,$condition=null)
    {
        // D('Admin/ExtendChannel') = D('ExtendChannel');
        // D('Admin/Users') = D('Users');
        $memberData = D('Admin/ExtendChannel')->memberList($channelId,$condition);
   
        //向更新列表中部分字段的表达形式
        foreach ($memberData as $key => $value) {
            $memberData[$key] = array_merge($memberData[$key],$this->memberInfoform($value));
            if($condition){
            }
            
        }
        // dump($memberData);
        // exit;
        return $memberData;
    }   
    /**
     * 推广人员列表
     * @param $info     
     * @return array
     */
    public function memberInfoform($info)
    {
        $infoForm = array();
            // $memberData[$key]['username'] = D('Admin/Users')->findUserNameById($memberData[$key]['user_id']);
        $user_info_temp = D('Home/Users')->userInfo($info['user_id'],'nick_name');
        $infoForm['nick_name'] = $user_info_temp['nick_name'];

        $infoForm['parent'] = D('Admin/ExtendChannel')->findChannelNameById($info['parent_id']);
        $infoForm['identity'] = C('SHOP_URL').'?ddt_from='.base64_encode($info['identity']);
        $infoForm['user_list']=D('Admin/Users')->extendUserList($info['user_id']);
        $data = D('Admin/ChannelGoods')->extendGoodsList($info['id']);
        $infoForm['extend_goods'] = sizeof($data);  
        return $infoForm;
    }   

    /**
     * 推广人员数量
     * @param $channelId        渠道Id
     * @return array
     */
    public function memberCount($channelId)
    {
        // D('Admin/ExtendChannel') = D('ExtendChannel');
        $data = D('Admin/ExtendChannel')->memberCount($channelId);
        return $data;
    }


    /**
     * 推广人员列表
     * @param $channelId        渠道Id
     * @return array
     */
    public function extendInfo($channelId, $condition = null)
    {
        // D('Admin/ExtendChannel') = D('ExtendChannel');
        if (empty($condition)) {
            $extendData = D('Admin/ExtendChannel')->findInfoByChannelId($channelId);
            return $extendData;
        }
    }

    /**
     * 添加渠道
     * @param $channelName        渠道名称
     * @param $userName            用户名
     * @param $memberName        昵称
     * @return boolean
     */
    public function addChannel($channelName, $userName, $memberName,$distribute=10)
    {
        // D('Admin/ExtendChannel') = D('ExtendChannel');
        // D('Admin/Users') = D('Users');
        //检查是否输入存在
        if (empty($channelName) || empty($userName) || empty($memberName)) {
            echo json_encode(array("error"=>1,"msg"=>"有输入为空!"));exit;
        }
        // 检查是否存在渠道名称重复
        if (D('Admin/ExtendChannel')->findChannelIdByName($channelName)) {
            echo json_encode(array("error"=>1,"msg"=>"渠道名称重复!"));exit;//渠道名称重复
        }
        //检查是否负责人用户名存在
        if (D('Admin/Users')->findUserIdByName($userName) == 0) {
            echo json_encode(array("error"=>1,"msg"=>"用户名不存在!"));exit;//用户名不存在
        } else {
            $userId = D('Admin/Users')->findUserIdByName($userName);
            // 检查是否负责人用户已列为该商城渠道负责人
            if (D('Admin/ExtendChannel')->findChannelIdByUserId($userId)) {
                echo json_encode(array("error"=>1,"msg"=>"已经成为该商城渠道负责人!"));exit;//已经成为该商城渠道负责人
            } else {
                //再判断，是否是一般推广人员，是一般推广人员，进行数据更改提升为渠道
                if (D('Admin/ExtendChannel')->findExtendIdByUserId($userId)) {
                    $channelData = array();
                    $channelId = D('Admin/ExtendChannel')->findExtendIdByUserId($userId);
                    $channelData['name'] = $channelName;
                    $channelData['user_name'] = $memberName;
                    $channelData['parent_id'] = $channelId;
                    $channelData['distribute'] = $distribute;
                    $channelData['is_channel'] = 0;
                    $data = D('Admin/ExtendChannel')->updateExtend($channelId, $channelData);
                    if($data){
                        echo json_encode(array("error"=>0,"msg"=>"推广人员提升为渠道成功!"));exit;
                    }else{
                        echo json_encode(array("error"=>1,"msg"=>"推广人员提升为渠道失败!"));exit;
                    }
                } else {
                    //添加渠道
                    $channelData = array();
                    $channelData['proxy_id'] = session('proxyId');
                    $channelData['name'] = $channelName;
                    $channelData['user_id'] = $userId;
                    $channelData['user_name'] = $memberName;
                    $channelData['distribute'] = $distribute;
                    $channelData['identity'] = $userId . 'from' . session('proxyId');
                    $channelData['add_time'] = time();
                    $data = D('Admin/ExtendChannel')->addChannel($channelData);
                    if($data){
                        echo json_encode(array("error"=>0,"msg"=>"增加渠道成功!"));exit;
                    }else{
                        echo json_encode(array("error"=>1,"msg"=>"增加渠道失败!"));exit;
                    }
                    
                }
            }

        }
    }

    /**
     * 修改渠道
     * @param $channelName        渠道名称
     * @param $userName            用户名
     * @param $memberName        昵称
     * @return boolean
     */
    public function editChannel($channelId, $channelName, $userName, $memberName,$distribute=10)
    {
        // D('Admin/ExtendChannel') = D('ExtendChannel');
        // D('Admin/Users') = D('Users');
        //检查是否输入存在
        if ($channelId == 0 || empty($channelName) || empty($userName) || empty($memberName)) {
            echo json_encode(array("error"=>1,"msg"=>"有输入为空!"));exit;
        }

        // 检查是否存在渠道名称重复
        if (D('Admin/ExtendChannel')->findChannelIdByName($channelName, $channelId)) {
            echo json_encode(array("error"=>1,"msg"=>"渠道名称重复!"));exit;//渠道名称重复
        }
        //检查是否负责人用户名存在
        if (D('Admin/Users')->findUserIdByName($userName) == 0) {
            echo json_encode(array("error"=>1,"msg"=>"用户名不存在!"));exit;//用户名不存在

        } else {
            $userId = D('Admin/Users')->findUserIdByName($userName);
            // 检查是否负责人用户已列为该商城渠道负责人
            if (D('Admin/ExtendChannel')->findChannelIdByUserId($userId, $channelId)) {
                echo json_encode(array("error"=>1,"msg"=>"已经成为该商城渠道负责人!"));exit;//已经成为该商城渠道负责人
            } else {
                //再判断，是否是一般推广人员，是一般推广人员，并更改为渠道负责人，将原渠道负责人调至未分组
                D('Admin/ExtendChannel')->startTrans(); 
                if (D('Admin/ExtendChannel')->findExtendIdByUserId($userId)) {

                    //将原渠道负责人调至未分组
                    $toMemberData = D('Admin/ExtendChannel')->changeChannelToMember($channelId);
                    //更新负责人信息
                    $channelData = array();
                    $channelNewId = D('Admin/ExtendChannel')->findExtendIdByUserId($userId);//新渠道的Id
                    $channelData['name'] = $channelName;
                    $channelData['user_name'] = $memberName;
                    $channelData['parent_id'] = $channelNewId;
                    $channelData['distribute'] = $distribute;
                    $channelData['is_channel'] = 0;
                    $updateChannelData = D('Admin/ExtendChannel')->updateExtend($channelNewId, $channelData);
                    //修改渠道下所有人员的parent_id
                    $updateMemberParentIdData = D('Admin/ExtendChannel')->updateMemberParentId($channelNewId, $channelId);
                    if ($toMemberData || $updateChannelData || $updateMemberParentIdData){
                        D('Admin/ExtendChannel')->commit(); 
                        echo json_encode(array("error"=>0,"msg"=>"修改渠道成功,推广人员提升为渠道!"));exit;
                    }else{
                        D('Admin/ExtendChannel')->rollback(); 
                        echo json_encode(array("error"=>1,"msg"=>"修改渠道失败!"));exit;
                    }
                } else {
                    //将原渠道负责人调至未分组
                    $toMemberData = D('Admin/ExtendChannel')->changeChannelToMember($channelId);
                    //非推广人员，直接新增渠道信息
                    $channelData = array();
                    $channelData['proxy_id'] = session('proxyId');
                    $channelData['name'] = $channelName;
                    $channelData['user_id'] = $userId;
                    $channelData['user_name'] = $memberName;
                    $channelData['distribute'] = $distribute;
                    $channelData['goods_code'] = $goodsCode;
                    $channelData['identity'] = $userId . 'from' . session('proxyId');
                    $channelData['add_time'] = time();
                    $channelNewId = D('Admin/ExtendChannel')->addChannel($channelData);
                    //修改渠道下所有人员的parent_id
                    $updateMemberParentIdData = D('Admin/ExtendChannel')->updateMemberParentId($channelNewId, $channelId);
                    if ($toMemberData || $updateMemberParentIdData){
                        D('Admin/ExtendChannel')->commit(); 
                        echo json_encode(array("error"=>0,"msg"=>"修改渠道成功!"));exit;
                    }else{
                        D('Admin/ExtendChannel')->rollback(); 
                        echo json_encode(array("error"=>1,"msg"=>"修改渠道失败!"));exit;
                    }
                }

            }
        }
    }

    /**
     * 添加渠道
     * @param $channelId        渠道Id
     * @param $userName            用户名
     * @param $memberName        昵称
     * @return boolean
     */

    public function addExtendMember($channelId, $userName,$goodsCode, $memberName)
    {
        //检查是否输入存在
        if (empty($channelId) && empty($userName) && empty($memberName)) {
            echo json_encode(array("error"=>1,"msg"=>"有输入为空!"));exit();
        }
        //检查是否存在渠道
        if (!(D('Admin/ExtendChannel')->findUserIdByChannelId($channelId) || $channelId == 0)) {
            echo json_encode(array("error"=>2,"msg"=>"渠道不存在也不是未分组!"));exit();
        }
        if (D('Admin/ExtendChannel')->findExtendIdByLabel($goodsCode)) {
            echo json_encode(array("error"=>3,"msg"=>"有优惠标签重复!"));exit();
        }
        //检查是否用户名存在
        if (D('Admin/Users')->findUserIdByName($userName) == 0) {
            echo json_encode(array("error"=>4,"msg"=>"用户名不存在!"));exit();
        } else {
            $userId = D('Admin/Users')->findUserIdByName($userName);
            //检查是否用户已为该商城推广人员
            if (D('Admin/ExtendChannel')->findExtendIdByUserId($userId)) {
            echo json_encode(array("error"=>5,"msg"=>"已经成为商城渠道推广人员!"));exit();
            } else {
                $extendMemberData = array();
                $extendMemberData['proxy_id'] = session('proxyId');
                $extendMemberData['parent_id'] = $channelId;
                $extendMemberData['user_id'] = $userId;
                $extendMemberData['user_name'] = $memberName;
                $extendMemberData['goods_code'] = $goodsCode;
                $extendMemberData['is_channel'] = 1;
                $extendMemberData['identity'] = $userId . 'from' . session('proxyId');
                $extendMemberData['add_time'] = time();
                $data = D('Admin/ExtendChannel')->addExtendMember($extendMemberData);
                if($data){
                    echo json_encode(array("error"=>0,"msg"=>'添加商城渠道推广人员成功!'));exit();
                }
            
            }
        }
    }

    /**
     * 删除渠道
     * @param $userId        用户id
     * @return mixed
     */
    public function deleteChannel($channelId)
    {
        // D('Admin/ExtendChannel') = D('ExtendChannel');
        D('Admin/ExtendChannel')->startTrans(); 
        //将原渠道负责人调至未分组
        $toMemberData = D('Admin/ExtendChannel')->changeChannelToMember($channelId);
        //修改渠道下所有人员的parent_id
        $updateMemberParentIdData = D('Admin/ExtendChannel')->updateMemberParentId(0, $channelId);
        if ($toMemberData || $updateMemberParentIdData){
            D('Admin/ExtendChannel')->commit(); 
            return ture;
        }else{
            D('Admin/ExtendChannel')->rollback(); 
            return false;
        }
    }

    /**
     * 修改推广人员
     * @param $channelName        渠道名称
     * @param $userName            用户名
     * @param $memberName        昵称
     * @return boolean
     */
    public function editMember($channelId, $extendId,$goodsCode, $memberName)
    {
        // D('Admin/ExtendChannel') = D('ExtendChannel');
        // D('Admin/Users') = D('Users');
        //检查是否输入存在
        if (empty($channelId) && empty($extendId) && empty($memberName)) {
            echo json_encode(array("error"=>1,"msg"=>"有输入为空!"));exit;//有输入为空
        }
        if (D('Admin/ExtendChannel')->findExtendIdByLabel($goodsCode,$extendId)) {
            echo json_encode(array("error"=>1,"msg"=>"有优惠标签重复!"));exit;//有优惠标签重复
        }
        $extendMemberData = array();
        $extendMemberData['parent_id'] = $channelId;
        $extendMemberData['goods_code'] = $goodsCode;
        $extendMemberData['user_name'] = $memberName;
        $data = D('Admin/ExtendChannel')->updateExtend($extendId, $extendMemberData);
        if($data){
            echo json_encode(array("error"=>0,"msg"=>"修改推广人员信息成功!"));exit;
        }else{
            echo json_encode(array("error"=>1,"msg"=>"修改推广人员信息失败!"));exit;
        }
    }

    /**
     * 删除渠道
     * @param $userId        用户id
     * @return mixed
     */
    public function deleteMember($channelId)
    {
        // D('Admin/ExtendChannel') = D('ExtendChannel');
        $data = D('Admin/ExtendChannel')->delete($channelId);
        return $data;
    }

    /**
     * 修改推广优惠标签
     * @param $extendId        推广ID
     * @param $goodsCode       优惠标签
     * @return boolean
     */
    public function editGoodsCode($extendId,$goodsCode)
    {
        // D('Admin/ExtendChannel') = D('ExtendChannel');
        //检查是否输入存在
        if (empty($extendId) || empty($goodsCode)) {
            echo json_encode(array("error"=>1,"msg"=>"有输入为空!"));exit;//有输入为空
        }  
        if (D('Admin/ExtendChannel')->findExtendIdByLabel($goodsCode,$extendId)) {
            echo json_encode(array("error"=>1,"msg"=>"有推广标签重复!"));exit;//有优惠标签重复
        }
        $channelData['goods_code']=$goodsCode;

        $data = D('Admin/ExtendChannel')->where('id='.$extendId)->save($channelData);
        if($data){
            echo json_encode(array("error"=>0,"msg"=>"修改推广优惠标签成功!"));exit;
        }else{
            echo json_encode(array("error"=>1,"msg"=>"修改推广优惠标签失败!"));exit;
        }
    }



    public function extendGoodsList($extendId){
        // D('Admin/ChannelGoods') = D('ChannelGoods');
        $data = D('Admin/ChannelGoods')->extendGoodsList($extendId);
        return $data;
    }
    /**
     * 添加推广优惠
     * @param $channelId        渠道Id
     * @param $userName            用户名
     * @param $memberName        昵称
     * @return boolean
     */

    public function addExtendGoods($data)
    {
        // D('Admin/ChannelGoods') = D('ChannelGoods');
        $good = new GoodsService();
        //检查是否输入存在
        if($data['discount_type']==1){
            if (empty($data['goods_sn']) || empty($data['discount'])|| empty($data['quantity'])) {
                echo json_encode(array("error"=>1,"msg"=>"有输入为空!"));exit;//有输入为空
            }

        }else if($data['discount_type']==2){
            if (empty($data['goods_sn']) || !isset($data['discount_price'])|| empty($data['quantity'])) {
                echo json_encode(array("error"=>1,"msg"=>"有输入为空!!"));exit;//有输入为空
            }
        }else{
            echo json_encode(array("error"=>1,"msg"=>"选择方式错误!"));exit;//选择方式错误
        }
        //检查是否存在商品
        $goodsInfo = $good->getGoodsBySerial($data['goods_sn']);
        if (!$goodsInfo['goods_id']) {
            echo json_encode(array("error"=>1,"msg"=>"商品不存在!"));exit;//商品不存在
        }
        if (D('Admin/ChannelGoods')->findExtendGoodId($data)){
            echo json_encode(array("error"=>1,"msg"=>"已加入该用户的推广列表中!"));exit;//已加入该用户的推广列表中
        }
        
        $extendGoodsData = array();
        $extendGoodsData['proxy_id'] = session('proxyId');
        $extendGoodsData['channel_user'] = $data['extendId'];
        $extendGoodsData['goods_id'] = $goodsInfo['goods_id'];
        $extendGoodsData['goods_sn'] = $data['goods_sn'];
        $extendGoodsData['discount'] = $data['discount']?$data['discount']:100;
        $extendGoodsData['discount_type'] = $data['discount_type'];
        $extendGoodsData['discount_price'] = $data['discount_price']?$data['discount_price']:$goodsInfo['goods_price'];
        $extendGoodsData['quantity'] = $data['quantity'];
        $extendGoodsData['begin_time'] = strtotime($data['begin_time']);
        $extendGoodsData['end_time'] = strtotime($data['end_time'])+86399;
        $extendGoodsData['addtime'] = time();
        $data = D('Admin/ChannelGoods')->addExtendGoods($extendGoodsData);
        if($data){
            echo json_encode(array("error"=>0,"msg"=>"增加推广优惠成功!"));exit;
        }else{
            echo json_encode(array("error"=>1,"msg"=>"增加推广优惠失败!"));exit;
        }
           
    }
    /**
     * 修改推广优惠
     * @param $channelId        渠道Id
     * @param $userName            用户名
     * @param $memberName        昵称
     * @return boolean
     */

    public function editExtendGoods($id,$editdata)
    {
        //检查是否输入存在
        if($editdata['discount_type']==1){
            if (empty($id) || empty($editdata['discount'])|| empty($editdata['quantity'])) {
                echo json_encode(array("error"=>1,"msg"=>"有输入为空!"));exit;//有输入为空
            }
        }else if($editdata['discount_type']==2){
            if (empty($id) || !isset($editdata['discount_price'])|| empty($editdata['quantity'])) {
                echo json_encode(array("error"=>1,"msg"=>"有输入为空!"));exit;//有输入为空
            }
        }else{
            echo json_encode(array("error"=>1,"msg"=>"选择方式错误!"));exit;//选择方式错误
        }
        $editdata['begin_time'] = strtotime($editdata['begin_time']);
        $editdata['end_time'] = strtotime($editdata['end_time'])+86399;

        $data = D('Admin/ChannelGoods')->updateExtendGoods($id,$editdata);
        if($data){
            echo json_encode(array("error"=>0,"msg"=>"修改推广优惠成功!"));exit;
        }else{
            echo json_encode(array("error"=>1,"msg"=>"修改推广优惠失败!"));exit;
        }
           
    }

    /**
     * 删除推广优惠
     * @param $extendGoodsId        推广优惠id
     * @return mixed
     */
    public function deleteExtendGoods($extendGoodsId)
    {
        // D('Admin/ChannelGoods') = D('ChannelGoods');
        $data = D('Admin/ChannelGoods')->delete($extendGoodsId);
        return $data;
    }

    /**
     * 返回推广优惠商品信息（外部接口）
     * @param $goodsId         商品id
     * @param $goodsCode       商品推广码
     * @return mixed
     */
    public function getExtendGoods($goodsId,$goodsCode){

        // D('Admin/ChannelGoods') = D('Admin/ChannelGoods');
        // D('Admin/ExtendChannel') = D('Admin/ExtendChannel');
        if(!$goodsId||!$goodsCode) 
            return false;
            $extendId = D('Admin/ExtendChannel')->findExtendIdByGoodsCode($goodsCode);
            if($extendId){
                $data = D('Admin/ChannelGoods')->getExtendGoods($goodsId,$extendId);
            }
            return $data ? $data : false;
    }

    /**
     * 检查是否存在，返回当前商品在推广优惠商品查到的个数（外部接口）
     * @param $goodsId     商品id
     * @return mixed
     */
    public function checkExtendGoods($goodsId){
        // D('Admin/ChannelGoods') = D('Admin/ChannelGoods');
        $count = D('Admin/ChannelGoods')->checkExtendGoods($goodsId);
        return $count ? $count : false;
    }

    /**
     * 修改推广优惠商品的库存数量(外部接口)
     * @param $extendId       推广Id
     * @param $goodsId        商品Id
     * @param $change         修改内容
     * @return mixed
     */
    public function updateExtendGoodsStore($extendId,$goodsId,$change){
        if(!$extendId||!$goodsId||!$change)
            return false;
        // D('Admin/ChannelGoods') = D('Admin/ChannelGoods');
            $data = D('Admin/ChannelGoods')->where('channel_user='.$extendId.' and goods_id='.$goodsId)->setInc('quantity',$change);
        return $data ? $data : false;
    }


    /**
     * 推广注册人员列表(外部接口)
     * @param int $extendId       推广Id
     * @param int $pagenum        单页显示数量
     * @param int $page           当前页码
     * @return array $data        注册用户信息
     */
    public function regList($extendId,$pagenum=10,$page=1){
        $info = D('Admin/ExtendChannel')->findInfoByChannelId($extendId);
        $extendInfo = $this->memberInfoform($info);
        $user_list = array_slice($extendInfo['user_list'],$pagenum*($page-1),$pagenum);
        $data = array('list'=>$user_list,'pagenum'=>$pagenum,'page'=>$page);
        return $data;
    }
        /**
     * 计算注册人员(外部接口)
     * @param int $extendId       推广Id
     * @param int $timer          倒推天数
     * @return int $count        注册用户数量
     */
    public function countReg($extendId,$timer=0){
        $info = D('Admin/ExtendChannel')->findInfoByChannelId($extendId);
        $extendInfo = $this->memberInfoform($info);
        $list = array();
        $count = 0;
        $countData['date'] = date('Y-m-d',strtotime('-'.$timer.' day'));
        $condition['start_time'] = strtotime($countData['date']);
        foreach($extendInfo['user_list'] as $key => $value){
            //筛选今日注册人员
            if($value['reg_time']>$condition['start_time']){
                $count+=1;
            }
        }
        return $count;
    }
    /**
     * 给出当前推广人的渠道数据(外部接口)
     * @param int $userId       userId
     * @return int $count        注册用户数量
     */
    public function getChannelInfo($userId){
        $ExtendChannel = D('Admin/ExtendChannel');
        $extendInfo = $ExtendChannel->findInfoByUserId($userId);
        if($extendInfo){
           $channelInfo=$ExtendChannel->findInfoByChannelId($extendInfo['parent_id']);
        }else{
            $channelInfo=false;
        }
        return $channelInfo;
    }
}