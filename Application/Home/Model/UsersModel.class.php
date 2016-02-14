<?php
/*
 * 订单商品模型
 */
namespace Home\model;
use Think\Model;
class UsersModel extends Model {
	/**

	 * 返回用户id
	 * @param $userName		用户名
	 * @return mixed

	 */
	public function findUserIdByName($userName){
		if(!$userName) 
			return false;
		$data = $this->where('user_name='.$userName)->getField('user_id');//返回字符串
		return $data ? $data : false;
	}
	/**

	 * 返回用户id
	 * @param $userId		用户id
	 * @return mixed

	 */
	public function findUserNameById($userId){
		if(!$userId) 
			return false;
		$data = $this->where('user_id='.$userId)->getField('user_name');//返回字符串
		return $data ? $data : false;
	}
	/**

	 * 查询用户id,密码,密码附加码
	 * @param $userName		用户名
	 * @return mixed

	 */
	public function findUserInfoByName($userName){
		if(!$userName) 
			return false;
		$data = $this->where('user_name='.$userName)->Field('user_id, password, ec_salt')->find();//返回一维数组
		return $data ? $data : false;
	}
	/**

	 * 数据表中写入附加码,更新用户密码
	 * @param $userName		用户名
	 * @param $password     密码
	 * @return mixed

	 */
	public function updateUserPassword($userName,$password){
		if(!(isset($userName)&&isset($password))) 
			return false;
		$userData = array();
		$userData['ec_salt']=rand(1,9999);
      	$userData['password']=md5(md5($password).$userData['ec_salt']);
      	$data = $this->where('user_name='.$userName)->save($userData);
      	return $data ? $data : false;
	}
	/**

	 * 数据表中增加用户
	 * @param $userName		用户名
	 * @param $password     密码
 	 * @return mixed

	 */
	public function addUser($userData){
		if(!$userData) 
			return false;
		$data = $this->data($userData)->add();//返回字符串
		return $data ? $data : false;
	}
	/**

	 * 登录更新相关信息，最后登录时间，最后登录ip,登录次数
	 * @param $userName		用户名
	 * @return mixed

	 */
	public function updateUserLogin($userName){
		if(!isset($userName)) 
			return false;
		$visitCount = $this->where('user_name='.$userName)->getField('visit_count');
		$userLoginData = array();
		$userLoginData['last_login']=time();
      	$userLoginData['last_ip']=getenv("REMOTE_ADDR");
      	$userLoginData['visit_count']=$visitCount+1;
      	$data = $this->where('user_name='.$userName)->save($userLoginData);
      	return $data ? $data : false;
	}
	/**

	 * 查询用户列表
	 * @param $condition		过滤数组
 	 * @return array

	 */
	public function userList($condition=null){
		$where = '1=1';
		$order = 'reg_time desc';
		if($condition['username']){
			$where.= ' and (user_name like "%'.$condition['username'].'%" or nick_name like "%'.$condition['username'].'%" ) ';
		}
		if($condition['region']){
			$where.= ' and mobile_province like "%'.$condition['region'].'%" or mobile_city like "%'.$condition['region'].'%"';
		}
		if($condition['status']!=''){
			$where.= ' and is_validated ='.$condition['status'];
		}
		if($condition['come_from']!=''){
			if($condition['come_from']=='extend'){
				$where.= ' and reg_from !=0 and parent_id !=0';
			}else{
				$where.= ' and reg_from=0 and parent_id=0';
			}
		}
		
		if($condition['begin_time']&&!$condition['end_time']){
			$where.= ' and reg_time >='.strtotime($condition['begin_time']);
		}
		if($condition['end_time']&&!$condition['begin_time']){
			$where.= ' and reg_time <='.(strtotime($condition['end_time'])+86399);
		}
		if($condition['end_time']&&$condition['begin_time']){

			$where.= ' and reg_time between '.strtotime($condition['begin_time']).' and '.(strtotime($condition['end_time'])+86399);
		}
		if($condition['ordername']&&$condition['ordertype']){
			$order=$condition['ordername'].' '.$condition['ordertype'];
		}
		$count = $this->where($where)->count();
		$sql = "SELECT `user_id`,`user_name`,`mobile_province`,`mobile_city`,`nick_name`,`birthday`,`user_money`,`frozen_money`,`pay_points`,`rank_points`,`address_id`,`reg_time`,`reg_from`,`extend`,`last_login`,`last_time`,`last_ip`,`visit_count`,`parent_id`,`is_validated` from `ddt_users` WHERE $where ORDER BY $order";
		$data =mypage($count,$sql,$condition);

		// var_dump($data);exit;
		
		// $data = $this->where($where)->order($order)->select();
		return $data ? $data : false;
	}
	/**

	 * 修改用户数据
	 * @param $userId		用户id
	 * @param $tag		    字段名
	 * @param $tagValue		字段值
 	 * @return boolean

	 */
	public function changeUserData($userId,$tag,$tagValue){
		$data = $this->where('user_id='.$userId)->setField($tag,$tagValue);
		return $data ? $data : false;
	}
	/**

	 * 修改用户资金账户
	 * @param $userId		用户id
	 * @param $change		修改值
 	 * @return boolean

	 */
	public function changeUserMoney($userId,$change){
		$money = $this->where('user_id='.$userId)->getField('user_money');
		//若修改后资金小于0,则返回false
		if($money+$change<0){
			$data = false;
		}else{
			$data = $this->where('user_id='.$userId)->setInc('user_money',$change);
		}
		return $data ? $data : false;
	}

	/**

	 * 修改用户积分账户
	 * @param $userId		用户id
	 * @param $change		修改值
 	 * @return boolean

	 */
	public function changeUserPoints($userId,$change){
		$pay_pointst = $this->where('user_id='.$userId)->getField('pay_points');
		//若修改后资金小于0,则返回false
		if($pay_pointst+$change<0){
			$data = false;
		}else{
			$data = $this->where('user_id='.$userId)->setInc('pay_points',$change);
		}
		return $data ? $data : false;
	}
	/**

	 * 查询用户信息
	 * @param $userId		用户id
 	 * @return array

	 */
	public function userInfo($userId,$field=true){
		$data = $this->where('user_id='.$userId)->field($field)->find();
		return $data ? $data : false;
	}

	public function addWXUser($userId,$openid){
		if(!$userId||(!$openid)){
			return false;
		}
		$data = $this->where('user_id='.$userId)->setField('wx_openid',$openid);
		return $data ? $data : false;
	}
	public function removeWXUser($userId){
		if(!$userId){
			return false;
		}
		$data = $this->where('user_id='.$userId)->setField('wx_openid','');
		return $data ? $data : false;
	}
	public function findUserInfoByOpenid($openid){
		if(!$openid){
			return false;
		}
		$data = $this->where("wx_openid='".$openid."'")->field('user_id,user_name')->find();
		return $data ? $data : false;
	}

}


// 登录用户Id  session('userId');
// $UsersModel->userInfo($userId) 成功返回一维数组，否则false 可用余额字段为user_money、冻结余额字段为frozen_money和积分字段为pay_points

// $UsersModel->changeUserMoney($userId,$change) float $change 为修改值，增加为正，减少为负  成功返回1，否则false

// $UsersModel->changeUserPoints($userId,$change) float $change 为修改值，增加为正，减少为负  成功返回1，否则false
