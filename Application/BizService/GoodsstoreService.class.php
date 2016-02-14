<?php
/**

 * 库存管理，修改库存
 *
 * @author 张辉
 
 */
namespace BizService;


class GoodsstoreService extends BaseService { 

/**
 *  设置库存，在分配库存时用和修改总库存时使用
 *  @param  $goods_commonid   商品公共id 
 *  @param  $storage  设置的库存 
 *  @param  $goods_price  设置的进库价格 
 *  @param  $type  库存类型，修改总库存信息：0    修改现金库存：1  修改积分库存：2  修改活动库存：3
 *  @author  张辉
 */ 
  public function  setstorage($goods_commonid=0,$storage=0,$goods_price=0,$type=0){
        //调取仓库信息
	    $goods_storehouse_data=D("goodsstorehouse")->where("goods_commonid=".$goods_commonid)->find();
		$storehouse_id=$goods_storehouse_data['storehouse_id'];
        //已分配库存
		$storages=$this->outstoreinfo($goods_commonid);
        //调取商品公共信息表信息
		$goodscommon_data=D('goodscommon')->where("goods_commonid=".$goods_commonid)->find();
		//总库存
		$goods_store_num=$goodscommon_data['goods_storage'];
		//如果价格没有填写或者为零，就填写原进货价。
		if($goods_price==0){
		   $goods_price=$goodscommon_data['goods_price'];
		}
		$shop_id=$goodscommon_data['shop_id'];
		//调取要修改的商品信息表
		$goods_data=array();
		switch($type){
		  case 0:
		    $goods_data=$goodscommon_data;
			if($goods_data){
			  //修改数量
			  $num1=$storage-$storages;
			  if($storehouse_id){
		        $flag=D('goodsstorehouse')->editstorage($storehouse_id,2,$num1);
			  }
			}else{
			  return -1;
			}  
		    break;
		  case 1:
		    $goods_data=D('goods')->where("goods_commonid=".$goods_commonid)->find();
		    if($goods_data){
			  //修改数量
		      $storage_num=$goods_data['goods_storage']-$storage;
			  $num1=$goods_store_num-$storages+$storage_num;
			  if($num1<0){
			   return -1;
			   exit;
			  }
		      $flag=D('goodsstorehouse')->editstorage($storehouse_id,2,$num1);
			}else{
			  return -1;
			}  
		    break;
		  case 2: 
		    $goods_data=D('integralgoods')->where("goods_commonid=".$goods_commonid)->find();
			if($goods_data){
			  //修改数量
		      $storage_num=$goods_data['goods_storage']-$storage;
			  $num1=$goods_store_num-$storages+$storage_num;
			  if($num1<0){
			   return -1;
			   exit;
			  }
			  if($storehouse_id){
		      $flag=D('goodsstorehouse')->editstorage($storehouse_id,2,$num1);
			  }
			}else{
			  return -1;
			}  
		    break;
		  case 3: 
		    $goods_data=D('prizegoods')->where("goods_commonid=".$goods_commonid)->find();
			if($goods_data){
			  //修改数量
		      $storage_num=$goods_data['goods_storage']-$storage;
			  $num1=$goods_store_num-$storages+$storage_num;
			  if($num1<0){
			   return -1;
			   exit;
			  }
			  if($storehouse_id){
		      $flag=D('goodsstorehouse')->editstorage($storehouse_id,2,$num1);
			  }
			}else{
			  return -1;
			}  
		    break; 
		  case 4: 
		    $goods_data=$goodscommon_data;
			if($goods_data){
			  //修改数量
			  $num1=$goods_store_num-$storage;
			  if($storehouse_id){
		      $flag=D('goodsstorehouse')->editstorage($storehouse_id,2,$num1);
			  }
			}else{
			  return -1;
			}  
		    break; 	
		 }
		 return 1;
  } 
   
   
/**
 *  获取商品综合库存信息
 *  @param  $goods_commonid   商品公共id 
 *  @param  $type  获取字段 类型  0：商品总量 已售 总库存 未分配  
 *  @author  张辉
 */
 public function  storeinfo($goods_commonid=0,$type=0){
    /* 获取分类信息 */
	$map = array();
	if(is_numeric($goods_commonid)){ //通过ID查询
		$map['goods_commonid'] = $goods_commonid;
	} 
	$data=array();
	//商品销售信息
	$goods_info=D('goodscommon')->info($goods_commonid,"goods_salenum,goods_storage");
	//获取未分配库存
	$store_info=D('goodsstorehouse')->info($goods_commonid,"num");
	//整理字段
	$data['goods_num']=$goods_info['goods_salenum']+$goods_info['goods_storage'];
    $data['goods_storage']=$goods_info['goods_storage'];
	$data['goods_salenum']=$goods_info['goods_salenum'];
	$data['num']=$store_info['num'];
	return  $data;
 }
 
/**
 *  获取商品已分配库存信息
 *  @param  $goods_commonid   商品公共id 
 *  @author  张辉
 */
 public function  outstoreinfo($goods_commonid=0){

      //调取现金商品
	   $goods_storage=D('goods')->field('goods_storage')->where("goods_commonid=".$goods_commonid)->find();
	  //调取积分商品
	   $lgoods_storage=D('integralgoods')->field('goods_storage')->where("goods_commonid=".$goods_commonid)->find();
	  //调取活动商品
	   $pgoods_storage=D('prizegoods')->field('goods_storage')->where("goods_commonid=".$goods_commonid)->find();
	   $storage_num=$goods_storage['goods_storage']+$lgoods_storage['goods_storage']+$pgoods_storage['goods_storage'];
	   return  $storage_num;
 }
 
 /**
 *  商品卖出后，减少商品库存或者退货后增加库存
 *  @param  $id   商品id 
 *  @param  $num  操作数量，正数减少库存 ，负数增加库存
 *  @param  $type  商品类型  1：现金商品  2：积分商品  3：活动商品
 *  @return  int   1:整除  -1：当前库存不足  
 *  @author  张辉
 */
 public function  changestoragebyid($id=0,$num=0,$type=1){
		switch($type){
		  case 1:
		   $flag=D('Admin/Goods')->changestorage($id,$num);   
		  break;
		  case 2:
		   $flag=D('Admin/Integralgoods')->changestorage($id,$num);  
		  break;
		  case 3:
		   $flag=D('Admin/Prizegoods')->changestorage($id,$num);  
		  break;
		}
		if($flag<0){
		  return $flag;
		}
	 return $flag;
 }
/**
 * 获取商品详情
 * @param number $id
 * @param unknown $field
 * @param number $type
 * @return boolean
 */
 
public function getinfo($id=0,$field=array(),$type=1){
    //
    if($type==1){
       return D("Admin/Goods")->getinfobyid($id,$field);
    }
    elseif ($type==2){
       return D("Admin/Integralgoods")->getinfobyid($id,$field);
    }
    elseif ($type==3){
       return D("Admin/Prizegoods")->getinfobyid($id,$field);
    }
    elseif ($type==4){
       return D("Admin/goodscommon")->getGoodsbymid($id,$field);
    }else{
        return false;
    }
} 
 
 
 
 
 



}
?>