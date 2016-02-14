<?php
/*
 * 订单商品模型
 */
namespace Admin\Model;
use Think\Model;
class ExtendChannelModel extends Model {
	 protected $_validate = array(   
									array('name','','渠道名称已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一     
							);

	/**

	 * 检查是否有渠道重名,返回推广渠道id
	 * @param $channelName		渠道名称
	 * @return mixed

	 */
	public function findChannelIdByName($channelName,$channelId=null){
		if(!$channelName) 
			return false;
		$where = $channelId?' and id!='.$channelId:'';
		$data = $this->where('name="'.$channelName.'" and proxy_id='.session('proxyId').' and is_channel=0'.$where)->getField('id');//返回字符串
		return $data;
	}
	/**

	 * 根据渠道Id返回渠道名称
	 * @param $channelName		渠道名称
	 * @return mixed

	 */
	public function findChannelNameById($channelId){
		if($channelId == 0) 
			return '未分组';
		$data = $this->where('id="'.$channelId.'" and proxy_id='.session('proxyId').' and is_channel=0')->getField('name');//返回字符串
		return $data;
	}
	/**

	 * 返回推广id
	 * @param $userId		用户id
	 * @param $proxy		本商城内筛选开启（0为关闭，1为开启）（前台接口）
	 * @return mixed

	 */
	public function findExtendIdByUserId($userId,$proxy=0){
		if(!$userId)
			return false;
		$where = array();
        $where['user_id'] = $userId;
        if($proxy){
            $where['proxy_id'] = session('proxyId');
        }
		$data = $this->where($where)->getField('id');//返回字符串
		return $data ? $data : false;
		//.' and proxy_id='.session('proxyId')
	}

	/**

	 * 返回推广id
	 * @param $goods_code		优惠标签
	 * @return mixed

	 */
	public function findExtendIdByGoodsCode($goodsCode){
		if(!$goodsCode) 
			return false;
		$data = $this->where("goods_code='".$goodsCode."' and proxy_id=".session('proxyId'))->getField('id');//返回字符串
		return $data ? $data : false;
	}

		/**

	 * 判断是否是商城渠道负责人,返回负责人Id
	 * @param $userId		用户id
	 * @param $channelId	不包含的channel_id
	 * @param $proxy		本商城内筛选开启（0为关闭，1为开启）（前台接口）
	 * @return mixed

	 */
	public function findChannelIdByUserId($userId,$channelId=0,$proxy=0){
		if(!$userId) 
			return false;
        $where = array();
        $where['user_id'] = $userId;
        $where['is_channel'] = 0;
        if($channelId){
            //$where = $channelId?' and id!='.$channelId:'';
            $where['id'] = array('neq',$channelId);
        }
		if($proxy){
			//$where.=' and proxy_id='.session('proxyId');
            $where['proxy_id'] = session('proxyId');
		}
		$data = $this->where($where)->getField('id');//返回字符串
		return $data ? $data : false;
		//and proxy_id='.session('proxyId').'
	}
	/**

	 * 返回用户Id
	 * @param $userName		渠道用户名
	 * @return mixed

	 */
	// public function findUserIdByName($userName){
	// 	if(!$userName) 
	// 		return false;
	// 	$data = $this->where('user_name="'.$userName.'" and proxy_id='.session('proxyId'))->getField('user_id');//返回字符串
	// 	return $data ? $data : false;
	// }	
	/**

	 * 返回关联用户id
	 * @param $channelId		渠道Id
	 * @return mixed

	 */
	public function findUserIdByChannelId($channelId){
		if(!$channelId) 
			return false;
		$data = $this->where('id='.$channelId.' and proxy_id='.session('proxyId').' and is_channel=0')->getField('user_id');//返回字符串
		return $data ? $data : false;
	}	
	/**

	 * 返回关联渠道或人员信息
	 * @param $channelId		渠道Id
	 * @return mixed

	 */
	public function findInfoByChannelId($channelId){
		if(!$channelId) 
			return false;
		$data = $this->where('id='.$channelId.' and proxy_id='.session('proxyId'))->find();//返回一维数组
		return $data ? $data : false;
	}	/**

	 * 返回关联渠道人员信息
	 * @param $userId		用户Id
	 * @return mixed

	 */
	public function findInfoByUserId($userId){
		if(!$userId) 
			return false; 
		$proxyId=session('proxyId');
		$proxyId=	$proxyId?$proxyId:0;
		$data = $this->where('user_id='.$userId.' and proxy_id='.$proxyId)->find();//返回一维数组
		return $data ? $data : false;
	}	
	/**

	 * 返回渠道负责人id
	 * @param $userName		用户名
	 * @return mixed

	 */
	public function findChannelUserIdByName($userName,$channelId=null){
		if(!$userName) 
			return false;
		$where = $channelId?' and id!='.$channelId:'';
		$data = $this->where('user_name="'.$userName.'" and proxy_id='.session('proxyId').' and is_channel=0'.$where)->getField('user_id');//返回字符串
		return $data ? $data : false;
	}

	/**

	 * 检查是否有推广标签重名,返回推广渠道id
	 * @param $channelName		渠道名称
	 * @return mixed

	 */
	public function findExtendIdByLabel($label,$channelId=null){
		if(!$label) 
			return false;
		$where = $channelId?' and id!='.$channelId:'';
		$data = $this->where('goods_code="'.$label.'" and proxy_id='.session('proxyId').$where)->getField('id');//返回字符串
		return $data;
	}
	/**

	 * 添加渠道
	 * @param $channelData		渠道信息
	 * @return mixed

	 */
	public function addChannel($channelData){
		if(!$channelData) 
			return false;
		$channelId = $this->data($channelData)->add();//返回新增推广渠道Id
		if($channelId){//将渠道Id返回赋给parent_id
			$data = $this->where('id='.$channelId)->setField('parent_id',$channelId);
		}else{
			return false;
		}
		return $data ? $channelId : false;
	}

	/**

	 * 添加推广人员
	 * @param $extendMemberData		推广人员信息
	 * @return mixed

	 */
	public function addExtendMember($extendMemberData){
		if(!$extendMemberData) 
			return false;
		$data = $this->data($extendMemberData)->add();//返回字符串
		return $data ? $data : false;
	}
	/**

	 * 渠道列表
	 * @param $channelData		渠道信息
	 * @return mixed

	 */
	public function channelList($condition=null){
		$where = '';
		$order = 'add_time desc';
		// var_dump($condition);exit;
		if($condition['username']){
			$where.=' and (name like "%'.$condition['username'].'%" or user_name like "%'.$condition['username'].'%")';
		}
	
		if($condition['ordername']&&$condition['ordertype']){
			if($condition['ordername'] == 'name'||$condition['ordername'] == 'user_name'||$condition['ordername'] == 'add_time'){  
        		$order=$condition['ordername'].' '.$condition['ordertype'];
        	}
		}
			$data = $this->alias('e')
						->where('is_channel=0 and proxy_id='.session('proxyId').$where)
						->order($order)
						// ->field('*,count(user_id) as membercount,sum(extend) as extendcount')
						->group('parent_id')
						->select();
						 // dump($data);exit;
		return $data ? $data : false;
	}
	/**

	 * 推广人员列表
	 * @param $channelId		渠道Id
	 * @return mixed

	 */
	public function memberList($channelId,$condition=null){
		$where = '';
		// if(session('proxyId')){
		// 	$proxyId = session('proxyId');
		// }else{
		// 	$shopData = get_shop_proxy();
		// 	$proxyId = $shopData['proxy_id'];
		// }
		if($channelId<0){
			$order = 'add_time desc';
		}else{
			$where.=' and e.parent_id='.$channelId;
			$order= ' e.is_channel asc, e.add_time desc';
		}
		if($condition['username']){
			$where.= ' and (e.user_name like "%'.$condition['username'].'%" or u.user_name like "%'.$condition['username'].'%")';
		}
		if($condition['ordername']&&$condition['ordertype']){
			if($condition['ordername'] == 'user_name'||$condition['ordername'] == 'username'||$condition['ordername'] == 'identity'||$condition['ordername'] == 'extend'||$condition['ordername'] == 'add_time'){
				$order=$condition['ordername'].' '.$condition['ordertype'];
			}
			
		}
			$data = $this->alias('e')
						->join('ddt_users u on e.user_id=u.user_id')
						->where('e.proxy_id='.session('proxyId').$where)
						->order($order)->field('e.*,u.user_name as username')
						->select();
		return $data ? $data : false;
	}
		/**

	 * 推广人员列表
	 * @param $channelId		渠道Id
	 * @return mixed

	 */
	public function channeldataCount($channelId){
		$where = $channelId<0?'':' and parent_id='.$channelId;
		$data = $this->where('proxy_id='.session('proxyId').$where)->field('id,user_id,extend')->select();
		return $data ? $data : false;
	}		
	/**

	 * 推广人员列表
	 * @param $channelId		渠道Id
	 * @return mixed

	 */
	// public function extendCount($channelId){
	// 	$where = $channelId<0?'':' and parent_id='.$channelId;
	// 	$data = $this->where('proxy_id='.session('proxyId').$where)->sum('extend');
	// 	return $data;
	// }
	/**

	 * 更新渠道信息
	 * @param $id		推广Id
	 * @param $channelData		用户名
	 * @return mixed

	 */
	public function updateExtend($id,$channelData){
		if(!isset($id)) 
			return false;
      	$data = $this->where('id='.$id)->save($channelData);
      	return $data ? $data : false;
	}
	/**

	 * 更新渠道内parent_id
	 * @param $channelNewId		新parent_id
	 * @param $channelId		旧parent_id
	 * @return mixed

	 */
	public function updateMemberParentId($channelNewId,$channelId){
		if(!isset($channelNewId)||!isset($channelId)) 
			return false;
      	$data = $this->where('parent_id='.$channelId)->setField('parent_id',$channelNewId);
      	return $data ? $data : false;
	}

	/**

	 * 将原渠道负责人调至未分组
	 * @param $channelId		原渠道Id
	 * @return mixed

	 */
	public function changeChannelToMember($channelId){
		if(!isset($channelId)) 
			return false;
		$memberData = array();
	    $memberData['name'] = '';
	    $memberData['parent_id'] = 0;
	    $memberData['is_channel'] = 1;
      	$data = $this->where('id='.$channelId)->save($memberData);
      	return $data ? $data : false;
	}
		/**

	 * 更新推广注册用户数
	 * @param $regFrom		推广标识码
	 * @return mixed

	 */
	public function updateMemberExtendCount($regFrom){
		if(!isset($regFrom)) 
			return false;
		$regFrom=base64_decode($regFrom);
      	$data = $this->where('identity="'.$regFrom.'"')->setInc('extend'); 
      	return $data ? $data : false;
	}
}