<?php
/**
 * 摇一摇
 */
namespace Home\Controller;
use Think\Controller;
use BizService\UserService;
use BizService\ShakeService;

class ShakeController extends Controller
{
    public function _initialize(){
        parent::_initialize();
        $shop_info = shop_info(session('city'));
        $this->assign("shop_base_info",$shop_info);
    }
    /**
     * 摇一摇页面
     */
    public function index()
    {
        $userId = session('userId');
        if (isset($userId)) {
            $this->assign('user_id', $userId);
            $this->assign('username', session('userName'));
        } else {
            $this->assign('user_id', 0);
        }
        $this->display();
    }


    /**
     * 摇一摇结果
     */
    public function generate()
    {
        $userId = session('userId');
        if($userId==""){
            redirect('index');
        }
        $shake = new ShakeService();
        //S(md5(session("userId")."last_yiy_time"),null);
        //echo S(md5(session("userId")."last_yiy_time"));
        if($shake->get_last_yiy_time()){
            $this->assign("res", "您已摇奖,请".intval(($shake->get_yiy_wait_time())/60)."分钟后再试~!");
            $this->display();
            exit;
        }
        //记录本次摇奖时间,有效期为摇奖设置的间隔时间
        //todo  已中奖用户是否需要继续抽奖，还是有限制，具体按照产品经理需求
        S(md5(session("userId")."last_yiy_time"),time(),$shake->get_yiy_wait_time());

        $data = $shake->get_prize_goods();
        foreach ($data as $key=>$val)
        {
            $probability[$key] =  $val["prob"];
        }
        $n = $shake->get_rand($probability);
        $type = $data[$n]['type'];
        if($type==2){
            //todo 查找积分奖励设置,既最大值赠送，最小赠送
            $goodsdata = array('num_min'=>1,'num_max'=>20);
            //从区间找随机数
            $number = mt_rand($goodsdata['num_min'], $goodsdata['num_max']);
            //todo 调用增加用户积分函数

            $this->assign("res", "恭喜您获得" . $number . "积分");
            $res['yes'] = $number."积分";
        }else{
            $res['yes'] = $data[$n]["prize"];
            $this->assign("res",$res['yes']);
        }
        //todo 奖品数量需要减少
        unset($data[$n]); // 将中奖项从数组中剔除，剩下未中奖项
        $this->display();
    }

    //用户登录
    public function userActLogin(){
        $user = new UserService();
        $userName = I('post.username','');
        $password = I('post.password','');
        //过滤条件
        $data = $user->userLogin($userName, $password);
        if($data)
            $this->error('登录失败', 'index');
        else
            $this->success('登录成功', 'index');
    }
}