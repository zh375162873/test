<?php
/*
 * 现金流水支付记录模型
 * 对用户账户的现金统计
 */
namespace Home\model;
use Think\Model;
class LsPriceModel extends Model {
    protected $_auto=array(
    		array("addtime","time",1,"function"),
    );
    
    //现金账户流水列表
    public function list_price_ls(){
    	
    }
    
   
    /*
     * 现金账户流水记录
     * 记录现金账户的进账记录（现金红包），
     * 出账记录（消费产品）
     * $type:0：默认普通商品购买的消费 ac_id为订单id
     *       1：兑奖活动商品购买的消费  ac_id为订单id
     *       2：兑奖活动现金红包获取的充值 ac_id为活动id
     *       注，仅仅是记录商户现金账户变动的表
     */
    public function del_price_ls($dateinfo,$type){
      
        	//初始化数据
        	$info=array(
        	    "shop_id"=>$dateinfo['shop_id'],
        	    "buyer_id"=>$dateinfo['buyer_id'],
        	    "money"=>$dateinfo['ye_paymoney'],
        	    "content"=>"购买商品：".$dateinfo['goods']['goods_name']." 账户余额花费：".$dateinfo['ye_paymoney'],
        	    "ac_id"=>$dateinfo['order_id'],
        	    "type"=>$type,
        	    "addtime"=>time(),
        	);

    	
        if($this->data($info)->add()){
            return true;
        }else{
            return false;
        }
    }
    
    //减少金额账户流水
    public function add_price_ls($dateinfo,$type){
        $info=array(
            "shop_id"=>$dateinfo['shop_id'],
            "buyer_id"=>$dateinfo['userid'],
            "money"=>$dateinfo['money'],
            "content"=>"参加活动".$dateinfo['active_name']."获取现金".$dateinfo['money'],
            "ac_id"=>$dateinfo['ac_id'],
            "type"=>$type,
            "addtime"=>time(),
        );
        
        if($this->data($info)->add()){
            return true;
        }else{
            return false;
        }
    }
    
    //增加账户流水记录
    public function ls_price_jl($data,$type,$admin_name){
        
       $info=array(
            "shop_id"=>$data['shop_id'],
            "buyer_id"=>$data['userid'],
            "money"=>$data['money'],
            "content"=>$data['content'],
            "ac_id"=>empty($data['ac_id'])?0:$data['ac_id'],
            "type"=>$type,
            "addtime"=>time(),
            "admin_name" =>$admin_name,
        );

        if($this->data($info)->add()){
            return true;
        }else{
            return false;
        }
        
    }
    /*
    用户账户流水数据记录表
    @param $userid    用户ID
    @param $type      0,所有记录;1,消费流水;2,更改流水
    */
    public function getUserAccountLog($userId,$type){
        if($type==1){
            $data = $this->where('buyer_id='.$userId.' and shop_id!=0')->order('id desc')->select();
        }else if($type==2){
            $data = $this->where('buyer_id='.$userId.' and shop_id=0')->order('id desc')->select();
        }else{
            $data = $this->where('buyer_id='.$userId)->order('id desc')->select();
        }

        return $data;
    }
    
}