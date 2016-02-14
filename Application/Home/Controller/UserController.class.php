<?php
namespace Home\Controller;
use BizService\ShopService;
use BizService\UserService;
use BizService\ExtendService;
use Admin\ExtendChannelModel;

header("Content-type:text/html;charset=utf-8");

class UserController extends BaseController
{
    private $shop_id;
    public function _initialize(){
        parent::_initialize();
        $shop = get_shop_proxy();
        $this->shop_id = $shop['shop_id'];
    }
    public function mine()
    {
        $user = new UserService();
        $extend = new ExtendService();
        $userId = session('userId');
        $queryData = array('user_name', 'nick_name', 'user_money', 'pay_points');
        $userData = $user->userInfo($userId, $queryData);       
        $userData['checknum'] = sizeof($user->checkNumber());

        if(D('Admin/ExtendChannel')->findChannelIdByUserId($userId,0,1)){
            $userData['channel'] = 1;
            $userData['extend'] = 1;
        }elseif(D('Admin/ExtendChannel')->findExtendIdByUserId($userId,1)){
            $userData['extend'] = 1;
        }    
        
        // var_dump($userData);
        $this->assign('userdata', $userData);
        $this->display();
        
    }

    public function changeNickname()
    {
        $user = new UserService();
        $userId = session('userId');
        $queryData = array('nick_name');
        $userData = $user->userInfo($userId, $queryData);
        $this->assign('userdata', $userData);
        $this->display();
    }

    /**
     * 关于我们
     */
    public function about(){
        $shop = new ShopService();
        $shop_info = $shop->get_shop_info($this->shop_id);
        $this->assign("info",$shop_info[0]);
        $this->display();
    }
    public function changePassword()
    {
        $this->display();
    }

    public function actChangeNickname()
    {
        $user = new UserService();
        $userId = session('userId');
        $nickName = I('get.nickname', '', 'strval');
        $data = $user->changeUserData($userId, 'nick_name', $nickName);
        redirect('mine');
    }

    public function actChangePassword()
    {
        $user = new UserService();
        $oldPassword = I('post.old_password', '', 'strval');
        $newPassword = I('post.new_password', '', 'strval');
        $newPassword2 = I('post.new_password2', '', 'strval');
        
        if(!($oldPassword&&$newPassword&&$newPassword2)){
            echo "<script type='text/javascript'>alert('原密码或新密码输入为空');history.back();</script>";exit();
        }else{
            if($newPassword!==$newPassword2){
                echo "<script type='text/javascript'>alert('新密码与重复密码输入不一致');history.back();</script>";exit();
            }
        }

        if ($user->checkUser(session('userName'), $oldPassword) > 0) {
            $data = $user->resetPasswd(session('userId'), $newPassword);
            if ($data) {
                echo "<script type='text/javascript'>alert('修改密码成功');window.location.href='" . U('user/mine') . "';</script>";
            } else {
                echo "<script type='text/javascript'>alert('修改密码失败');history.back();</script>";
            }
        } else {
            echo "<script type='text/javascript'>alert('原密码输入错误');history.back();</script>";
        }

    }

    public function checkNumber(){
        $user = new UserService();
        $num_list = $user->checkNumber();

        // var_dump($num_list);exit;
        $this->assign('num_list',$num_list);
        $this->display('checkNumber');
    }

    public function location()
    {
        $this->display('location');
    }

}