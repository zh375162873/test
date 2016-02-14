<?php
/*
 * 订单商品模型
 */
namespace Admin\model;
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
      	$userLoginData['last_ip']=$_SERVER['SERVER_ADDR'];
      	$userLoginData['visit_count']=$visitCount+1;
      	$data = $this->where('user_name='.$userName)->save($userLoginData);
      	return $data ? $data : false;
	}
	/**

	 * 查询用户列表
	 * @param $fliter		过滤数组
 	 * @return array

	 */
	public function userList($fliter=null){
		$data = $this->select();
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

	 * 查询用户信息
	 * @param $parent_Id		推广人id
 	 * @return array

	 */
	// public function extendUserCount($parent_Id){
	// 	$data = $this->where('parent_Id='.$parent_Id.' and reg_from='.session('proxyId'))->count();
	// 	return $data ? $data : 0;
	// }

	/**

	 * 查询推广人员推广用户列表
	 * @param $extenduserId		推广人员userId	
 	 * @return array

	 */
	public function extendUserList($extenduserId){
		$proxyId = session('proxyId');
		if(!isset($extenduserId)||!isset($proxyId)) 
			return false;
		$data = $this->where('reg_from='.$proxyId.' and parent_id='.$extenduserId)->field('user_id,user_name,nick_name,reg_time')->select();
		return $data ? $data : false;
	}

}