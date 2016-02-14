<?php
/*
 * 用户评论管理模型
 * 记录用户评论，统计用户相关评论
 */
namespace Home\model;
use Think\Model;
use BizService\UserService;
use Org\Util\Date;
//use BizService\GoodshomeService;
class PinglunManageModel extends Model {
    protected $fields=array("id","goods_id","order_id","goods_type","shop_id","store_id","buyer_id","pl_content","pl_parentid","pl_addtime","pl_updatetime","pl_points","pl_status","_pk"=>"id");
    protected $_auto=array(
    	array("pl_addtime","time",1,"function"),
    	array("pl_updatetime","time",2,"function"),	
    );
    
    public $orderdb,$_userid,$users;
    public function __construct(){
        parent::__construct();
        $this->orderdb=D("Orders");
        $this->users=new UserService();
       // $this->GoodshomeService=new GoodshomeService();
    }

	/**
	 * 根据条件获取评论信息
	 * @param $condition
	 * @return mixed
	 */
	public function get_commet_info($condition){
		$row = $this->where($condition)->find();
		return $row;
	}
    
    //评论添加(订单id,商品id)
	public function addplun($data){
		 $orderinfo = $this->orderdb->getinfo($data['order_id']);
	     $pl_info = array(
	         "goods_id" => $orderinfo['goods']['goods_id'],
	         "order_id" => $orderinfo['order_id'],
	         "goods_type" => $data['goodstype'],
	         "shop_id" => $orderinfo['shop_id'],
	         "store_id" => $orderinfo['store_id'],
	         "buyer_id" => $orderinfo['buyer_id'],
	         "pl_content" => $data['desc'],
	         "pl_parentid" => $data['parentid'],
	         "pl_addtime" => time(),
	         "pl_points" => $data['mc_score'],
	         "pl_status" => 0,
	     );
	     
	     if($this->data($pl_info)->add()){
	         $this->orderdb->where(array("order_id"=>$data['order_id']))->data(array('evaluation_state'=>1))->save();//更新订单评论
	         //统计该商品评论数
	        /*  $arr=$this->where(array("goods_id"=>$pl_info['goods_id']))->select();
	         $count_num = count($arr);
	         $total ='';
	         for($i=0;$i<$count_num;$i++){
	             $total = $total+$arr[$i]['pl_points'];
	         }
	         $star = $total/$count_num;
	         $this->GoodshomeService->setevaluation($pl_info['goods_id'],$star,$count_num); */
	        
	         return true;
	     }else{
	         return false;
	     }
	}
	
	//评论列表
	public function getlist($goods_id){
	   if(empty($goods_id)){
	       return false;
	   }
	   $arr = $this->where(array("goods_id"=>$goods_id,"pl_status"=>1))->select();
	   $count = count($arr);
	   $info['one']=0;
	   $info['two']=0;
	   $info['three']=0;
	   $info['four']=0;
	   $info['five']=0;
	   $info['count']=$count;
	   $total = '';
	   for($i=0;$i<$count;$i++){
	       $userinfo=$this->users->userInfo($arr[$i]['buyer_id']);
	       $arr[$i]['nickname']=$userinfo['nick_name'];
	       $arr[$i]['user_name']=$userinfo['user_name'];
	       $arr[$i]['pl_addtime']=Date("Y-m-d H:i:s",$arr[$i]['pl_addtime']);
	       $arr[$i]['user_name']=substr_replace($arr[$i]['user_name'],"****",3,4);
	       $total = $total+$arr[$i]['pl_points'];
	       if($arr[$i]['pl_points']==1){
	           $info['one']=$info['one']+1;
	       }
	       elseif($arr[$i]['pl_points']==2){
	           $info['two']=$info['two']+1;
	       }
	       elseif($arr[$i]['pl_points']==3){
	           $info['three']=$info['three']+1;
	       }
	       elseif($arr[$i]['pl_points']==4){
	           $info['four']=$info['four']+1;
	       }
	       elseif($arr[$i]['pl_points']==5){
	           $info['five']=$info['five']+1;
	       }
	   }
	   $info['one_bfb'] = ($info['one']*100)/$count; 
	   $info['two_bfb'] = ($info['two']*100)/$count;
	   $info['three_bfb'] = ($info['three']*100)/$count;
	   $info['four_bfb'] = ($info['four']*100)/$count;
	   $info['five_bfb'] = ($info['five']*100)/$count;
	   $info['count_bfb'] = $total/$count;
	   $info['count_xsd'] = round($info['count_bfb'], 1); 
	   $info['count_bfb'] = $info['count_xsd']*20;
	   
	   
	   $data = array();
	   $data['list']=$arr;
	   $data['info']=$info;
	   
	   return $data;
	}
	//统计信息
	
	
	
	//评论搜索和统计(每个商品的用户评论，每个商户的用户平路数，每个商城的用户评论)
	public function searchplun($id,$type){
		
	}
}