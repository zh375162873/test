<?php

namespace BizService;

use BizService\BaseService;
/**
 * 用户Service
 *
 * @author 赵星
 */
class UserService extends BaseService {

	/**

	 * 用户中心登录状态判断

	 */
	public function checkUsercenter(){
		if(session('adminKeyStatus') !='on' ){
                $url='location:'.C('WIFI_URL').'/admin/default/index';
                header($url);
            exit;
        }
	}
	/**

	 * 用户登录
	 * @param $userName	用户名
	 * @param $password     密码
	 * @param $type 	    登录类型
  	 * @param $remember	    记住账号
  	 * @return int

	 */
	public function userLogin($userName,$password,$type=0,$remember=0){
		$UsersModel = D('Users');
		if (empty($userName) || empty($password))
		{
			$loginFailed = 1;
		}else{
			if (self::checkUser($userName, $password) > 0 && $UsersModel->updateUserLogin($userName)){
				$userId = $UsersModel->findUserIdByName($userName);
				session('userId',$userId);
				session('userName',$userName);
				cookie('userName',$userName);
				$wx_openid = session('wx_openid');
				if($wx_openid){
					D('Home/Users')->addWXUser($userId,$wx_openid);
				}
				$loginFailed = 0;
			}else{
				$loginFailed = 2;
			}
		}
		return $loginFailed;
	}
	/**

	 * 账号密码检查
	 * @param $userName	用户名
	 * @param $password     密码
	 * @return int

	 */
	public function checkUser($userName, $password = null){
		$UsersModel = D('Users');
	  	if (empty($password)){	
	  		$userId = $UsersModel->findUserIdByName($userName);
	      	return $userId;
	  	}else{	
	  		$userInfo = $UsersModel->findUserInfoByName($userName);
	      	if (empty($userInfo)){
	      		//用户不存在
	          	return 0;
	      	}else{
	      		$ecSalt=$userInfo['ec_salt'];
	      		if ($userInfo['password'] != self::compilePassword(array('password'=>$password,'ecSalt'=>$ecSalt))){
	      			//密码错误
		          	return -1;
		      	}
		      	else
		      	{	//首次登录赋值ec_salt
		          	if(empty($ecSalt)){
		          		if($UsersModel->updateUserPassword($userName,$password)){
		          			return $userInfo['user_id'];
		          		}else{
		          			return -2;
		          		}
		          	}else{
		          		return $userInfo['user_id'];
		          	}
		      	}
	      	}
	  	}
	}
	/**

	 * 密码合成
	 * @param $arr['password']	   密码
	 * @param $arr['ecSalt']      密码附加码

	 */
	public function compilePassword($arr){
		if (isset($arr['password'])){
	        $arr['md5password'] = md5($arr['password']);
	   	}
	  	if(!empty($arr['ecSalt'])){
	       	$comPass = md5($arr['md5password'].$arr['ecSalt']);
	   	}else{
	        $comPass = $arr['md5password'];
	   	} 
	   	return $comPass;
	}

	/**

	 * 用户注册
	 * @param $userName	用户名
	 * @param $password     密码
	 * @return boolean

	 */
	public function userRegister($userName, $password)
	{	$UsersModel = D('Users');
		/* 账号输入过滤,长度限定11位 */
		if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $userName)||strlen($userName)!=11)
		{
			return false;
		}
		/* 密码输入过滤,长度限定6-12位 */
		if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $password)||strlen($password)<6||strlen($password)>12)
		{
			return false;
		}
		/* 检查是否用户名重名 */
		if (self::checkUser($userName)){
			return false;
		}
		if (!self::addUser($userName, $password, $regFrom)){
			//注册失败
			return false;
		}else{
			//注册成功
			/* 设置成登录状态 */
			$userId = $UsersModel->findUserIdByName($userName);
			session('userId',$userId);
			session('userName',$userName);
			cookie('userName',$userName);
			$wx_openid = session('wx_openid');
			if($wx_openid){
				D('Home/Users')->addWXUser($userId,$wx_openid);
			}
		 	$UsersModel->updateUserLogin($userName);
		}
		return true;
	}

	/**

	 * 增加用户
	 * @param $userName		用户名
	 * @param $password     密码
 	 * @return boolean

	 */
	public function addUser($userName, $password)
	{	
	    //检查是否重名
	    if (self::checkUser($userName) > 0){
	        return false;
	    }
	    $UsersModel = D('Users');
	    $ChannelModel = D('Admin/ExtendChannel');
	    $post_username = $userName;
	    $post_password = self::compilePassword(array('password'=>$password));
	    $userData = array();
	    $userData['user_name'] = $post_username;
	    $userData['password'] = $post_password;
	    $userData['reg_time'] = time();
	    $mobile_info = $this->get_mobile_area($userName);
	    $userData['mobile_province'] = $mobile_info['province'];
	    $userData['mobile_city'] = $mobile_info['cityname'];

	    $regFrom=session('regFrom');
	    if($regFrom){
        	$userFrom=explode('from',base64_decode($regFrom));
        	$userData['parent_id'] = intval($userFrom[0]);
	    	$userData['reg_from'] =  session('proxyId');
	    	session('regFrom',null);
	    }
	    //写入数据库
	    //创建用户数据和推广数据
      	if($UsersModel->addUser($userData)){
      		if($regFrom){
      			return $ChannelModel->updateMemberExtendCount($regFrom);exit();
      		}
	    		return true;
	    }else{
	    	return false;
	    }
	}

		/**

	 * 用户列表
 	 * @return array

	 */
	public function userList($condition=null){
		$UsersModel = D('Home/Users');
        if($condition['ordername'] == 'order_num'||$condition['ordername'] == 'order_money'){
            $condition['ordername']='';
        }
        $usersData = $UsersModel->userList($condition);
		return $usersData;
	}

	/**

	 * 修改数据
	 * @param $userId		用户id
	 * @param $tag     		修改字段名
	 * @param $tagValue     修改字段值
 	 * @return mixed

	 */

	public function changeUserData($userId,$tag,$tagValue){
		$UsersModel = D('Home/Users');
		$data = $UsersModel->changeUserData($userId,$tag,$tagValue);
		return $data;
	}
	/**

	 * 重置密码
	 * @param $userId		用户id
	 * @param $rePassword   重置密码
 	 * @return mixed

	 */
	public function resetPasswd($userId,$rePassword){
		$UsersModel = D('Home/Users');
		$userName = $UsersModel->findUserNameById($userId);
		if($userName){
			$data = $UsersModel->updateUserPassword($userName,$rePassword);
		}else{
			$data = false;
		}
		return $data;
	}
	/**

	 * 修改账户资金
	 * @param $userId		用户id
	 * @param $userId		用户id
 	 * @return mixed

	 */
	public function changeUserMoney($userId,$change){
		$UsersModel = D('Admin/Users');
		$data = $UsersModel->changeUserMoney($userId,$change);
		return $data;
	}	/**

	 * 修改用户积分账户
	 * @param $userId		用户id
	 * @param $change		修改值
 	 * @return boolean

	 */
	public function changeUserPoints($userId,$change){
		$UsersModel = D('Home/Users');
		$data = $UsersModel->changeUserPoints($userId,$change);
		return $data;
	}	/**

	 * 查询用户信息
	 * @param $userId		用户id
 	 * @return array

	 */
	public function userInfo($userId,$queryData=null){
		$UsersModel = D('Home/Users');
        $ChannelModel = D('Admin/ExtendChannel');
		if($queryData){
			$data = $UsersModel->field($queryData)->userInfo($userId);
		}else{
			$data = $UsersModel->userInfo($userId);
		}
		if($data['parent_id']){
			$extend_data = $ChannelModel->findInfoByUserId($data['parent_id']);
			if($extend_data['parent_id']){
				$data['channel_id'] = $extend_data['parent_id'];
				$data['distribute'] = $extend_data['distribute'];
			}else{
				$data['channel_id'] = 0;
				$data['distribute'] = 0;
			}
		}	
		return $data;
	}
	/**

	 * 删除用户
	 * @param $userId		用户id
 	 * @return mixed

	 */
	public function deleteUser($userId){
		$UsersModel = D('Home/Users');
		$data = $UsersModel->delete($userId); 
		return $data;
	}

	/**

	 * 用户短信通知
	 * @param $phoneNum		用户手机
	 * @param $msgData		信息内容
 	 * @return mixed

	 */
	public function sendMsgForUser($phoneNum,$msgData){
		$post_data = array();
        $post_data['account'] = iconv('GB2312', 'GB2312',"LKJX888");
        $post_data['pswd'] = iconv('GB2312', 'GB2312',"lkjx2015MSG");
        $post_data['mobile'] =$phoneNum;
        $post_data['msg']=mb_convert_encoding("$msgData",'UTF-8', 'UTF-8');
        $post_data['needstatus']='true';
        $url='http://222.73.117.158/msg/HttpBatchSendSM?'; 
        $parse = parse_url($url);
        // var_dump($parse);die();
        // for($i=0;$i<10;$i++)
        // echo "<br />";
        $o="";
        foreach ($post_data as $k=>$v)
        {
           $o.= "$k=".urlencode($v)."&";
        }
        $post_data=substr($o,0,-1);
         
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch) ;
        $pos = strpos($result,',');
        // echo $result;
        //用于截取判断状态码
        $co=substr($result,15,1);
		return $co;
	}

	/**

	 * 用户短信验证码验证
	 * @param $phoneNum		用户手机
	 * @param $check_code	验证码
 	 * @return mixed

	 */

    public function checkCode($phone_num,$check_code){
        if(preg_match('/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/', $phone_num)){
            $validate_code = $check_code . "_" . $phone_num;
            $code = session('code');
            if($code == $validate_code){
               return json_encode(array("code"=>1,"msg"=>""));exit();
            }else{
               return json_encode(array("code"=>0,"msg"=>"验证码输入错误~!"));exit();
            }
        }else{
            return json_encode(array("code"=>2,"msg"=>"请输入合法手机号~!"));exit();
        }     
    }
    	/**

	 * 查询推广人员推广用户列表
	 * @param $extenduserId		推广人员userId	
 	 * @return array

	 */
    public function extendUserList($extenduserId){
		$UsersModel = D('Admin/Users');
		$data = $UsersModel->extendUserList($extenduserId); 
		
		return $data ? $data : false;
	}
	    /**
     * 用户订单的统计
     * $condition 数组 参数为：
     * @param unknown $userid array
     * @param $proxy_id渠道id
     * @param unknown $start_time
     * @param unknown $end_time
     * @return Ambigous <\Think\mixed, boolean, unknown, mixed, object>
     */
    public function userCount($condition){
        $data=array();
        if(!empty($condition['start_time'])&&!empty($condition['end_time'])){
            $data['reg_time'] = array(array("gt",$condition['start_time']),array("lt",$condition['end_time']));
        }
        elseif(!empty($condition['start_time'])&&empty($condition['end_time'])){
            $data['reg_time'] = array("gt",$condition['start_time']);
        }
        elseif(empty($condition['start_time'])&&!empty($condition['end_time'])){
            $data['reg_time'] = array("lt",$condition['end_time']);
        }
		$userCount = D('Admin/Users')->where($data)->count("user_id");

        return $userCount;
    }
    /**
     * 用户手机号归属地查询
     * @param $mobile 手机号
     * @return array 
     */
    public function get_mobile_area($mobile){
    //根据淘宝的数据库调用返回值
        // $url = "http://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=".$mobile."&t=".time();
    //根据拍拍的数据库调用返回值
        $url = "http://virtual.paipai.com/extinfo/GetMobileProductInfo?mobile=".$mobile."&amount=10000&callname=info";

        $content = file_get_contents($url);
        $content = iconv('GB2312', 'UTF-8', $content);
        $find = array('"',"'",'{','}','(',')','\r','\n','\r\n',';','info');
        $json = str_replace($find, '', $content);
        $sda=strstr($json,'<',true);
        $array = explode(',', $sda);
        $info = array();
        foreach($array as $val){
            $temp = explode(':', $val);
            $key = trim($temp[0]);
            $info[$key] = trim($temp[1]);
        }
        // var_dump($info);exit;
        return $info;
        // array (size=8)
        //   'mobile' => string '13333333333' (length=11)
        //   'province' => string '河北' (length=6)
        //   'isp' => string '中国电信' (length=12)
        //   'stock' => string '1' (length=1)
        //   'amount' => string '10000' (length=5)
        //   'maxprice' => string '0' (length=1)
        //   'minprice' => string '0' (length=1)
        //   'cityname' => string '秦皇岛' (length=9)
    }


    public function checkNumber(){
    	$userId = session('userId');
        $new_check_num = D('Home/DhmManage')->userChecknum($userId);
        $proxyId =  session('proxyId');

        if($proxyId==39){	
	        $old_check_num = D('Home/OldChecknum')->userChecknum($userId);
	        $check_num_list = array_merge($new_check_num,$old_check_num);
        }else{
        	$check_num_list = $new_check_num;
        }
        $num_list =array();
        $count = 0 ;
        foreach ($check_num_list as $key => $value) {
            if(isset($value['order_id'])){
                $goods_temp = D('Home/Goods')->getinfobyid($value['goods_id'],'goods_name,virtual_indate');
                // var_dump($goods_temp);
                
                $num_list[$count] = $check_num_list[$key];
                $num_list[$count]['goods_name'] = $goods_temp['goods_name'];
                $num_list[$count]['indate'] = date('Y年m月d日',$goods_temp['virtual_indate']);
                $num_list[$count]['href'] =  U('order/orderinfo?id='.$value['order_id']);
                $num_list[$count]['dhm_code'] = substr($value['dhm_code'],0,4).' '.substr($value['dhm_code'],4,4).' '.substr($value['dhm_code'],8,4);          
                if($goods_temp['virtual_indate']<=time()){
                	$num_list[$count]['overtime'] = 1;
             	}
                $count++;

            }else{
                $num_list[$count] = $check_num_list[$key];
                $num_list[$count]['href'] = 'http://shop.ddt123.cn/oldshop/mobile/goods.php?id='.$value['goods_id'];
                $num_list[$count]['dhm_code'] = substr($value['dhm_code'],0,4).' '.substr($value['dhm_code'],4,4).' '.substr($value['dhm_code'],8,4).' '.substr($value['dhm_code'],12,4);
                $count++;
            }
            
        }
        return $num_list;

    }
}