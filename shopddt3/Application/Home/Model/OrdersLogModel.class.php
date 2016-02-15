<?php
/*
 * 订单历史操作记录模型
 */
namespace Home\model;
use Think\Model;
class OrdersLogModel extends Model {
	  protected $fields=array("id","order_id","log_msg","log_time","log_role","log_user","log_orderstate","log_type","_pk"=>"id");
	  protected $_auto=array(
	  	    array("log_time","time",1,"function"),	
	  );
	  
	  /*
	   * 添加操作记录
	   * 
	   */
	  public function add_log($data){
	     
	  	//添加普通商品订单操作记录
	  	if($this->data($data)->add()){
	  	    return true;
	  	}else{
	  	    return false;
	  	}
	  }
	  //操作记录列表
	  public function list_log($data){
	  	
	  }
      
      
}