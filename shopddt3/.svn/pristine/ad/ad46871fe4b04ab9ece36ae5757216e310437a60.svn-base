<?php
namespace Admin\Model;
use Think\Model;
use Think\Upload;
class TempitemModel extends Model 
{ 
  protected $tableName = 'temp_item'; 
  protected $fields = array(
  'item_id', //	int(10) 	否		子模板设置信息id
  'item_title', // 	int(10) 	否		标题
  'temp_id', 	//int(10) 	否		模板id	
  'style_id', // 	int(10)	模板样式编号	
  'item_data', // 	text	设置信息
  'item_is_used', // 	int(3)	记录是否使用	
  'item_order', // 	int(10)	记录上移下移位置
  'shop_id', // 	int(3)			商城id	
  '_pk'=>'id'//主键
  );

  
  
   //获取列表
 public function getlist($param=true,$order){
   return   $this->where($param)->order($order)->select();
 }
  
/**
   *删除信息
   * @param  param array()  
   * @return  int   默认1
   * @author zhanghui
  */
  public function  itemdel($param=true){
	     $this->where($param)->delete();
  }
  
  
  /**
 * 获取详细信息
 * @param  milit   $id 分类ID或标识
 * @param  boolean $field 查询字段
 * @return array     产品信息
 * @author zhanghui
 */
 public function info($param=true,$field = true){
	return $this->field($field)->where($param)->find();
 }
 
  /**
 * 获取详细信息
 * @param  milit   $id 分类ID或标识
 * @param  boolean $field 查询字段
 * @return array     产品信息
 * @author zhanghui
 */
 public function findbyid($id=0,$field = true){
	return $this->field($field)->where("item_id=".$id)->find();
 }
 
 /**
   *更新信息
   * @param  $data  商品公共信息数组
   * @param  $id  商品公共id
   * @return  int   默认1
   * @author zhanghui  
  */
  public function  itemedit($data,$id){
	    $flag=$this->where("item_id=".$id)->data($data)->save();
		return  $flag;
  }
  
/**
 * 获取分类导航模板信息
 * @param  int   $shop_id 商城id
 * @return array     分类导航信息
 * @author zhanghui
 */
 public function finditem2_byshop_id($shop_id=0){
	$data=$this->where("shop_id=$shop_id and style_id=2")->select();
	return $data;
 }

/**
 *更新单个模板分类导航信息
 */
  public function editoneclass($item_id){
	//调取代理信息
	$shop_info = get_shop_proxy();
	//调取此代理的分类导航模板
	
	//数据调取
	$item=D('tempitem')->findbyid($item_id);
	$info=json_decode($item['item_data'],true);
	//检查分类是否被删除，如果被删除就删除掉
	foreach($info['info_data'] as $key=>$val2){
		if($val2['info_gcid']>0){
		 $class= D('goodsclass')->where('gc_id='.$val2['info_gcid'])->find();
		 if($class){
			
		 }else{
		   unset($info['info_data'][$key]);
		 }  
		}
	} 
	//调取分类信息
	$class_data=D('goodsclass')->getTree(0,true,$shop_info['shop_id']);
	//保存分类信息为导航
	foreach($class_data as $i=>$val){
	  $flag=0;
	  foreach($info['info_data'] as $key=>$val2){
		if($val2['info_gcid']==$val['gc_id']){
		$flag=1;
		$i=$key;
	   } 
	  }
	  if($flag==0){
		 //将此分类添加到导航列表中
		 $k=$key+1;
		 $info_title=$val['gc_name'];
		 $info_url=U("/home/goods/index/gc_id/".$val['gc_id']);
		 $info_img=$val['gc_images'];
		 $info_gcid=$val['gc_id'];
		 $info_order=225;
		 $info['info_data'][$k]['info_title']=$info_title;
		 $info['info_data'][$k]['info_type']=3;
		 $info['info_data'][$k]['url']=$info_url;
		 $info['info_data'][$k]['info_img']=$info_img;
		 $info['info_data'][$k]['info_gcid']=$info_gcid;
		 $info['info_data'][$k]['info_order']=$info_order;
		 $info['info_data'][$k]['info_isused']=0;
	   }else{
		 $info_title=$val['gc_name'];
		 $info_url=U("/home/goods/index/gc_id/".$val['gc_id']);
		 $info_img=$val['gc_images'];
		 $info['info_data'][$i]['info_title']=$info_title;
		 $info['info_data'][$i]['info_type']=3;
		 $info['info_data'][$i]['url']=$info_url;
		 $info['info_data'][$i]['info_img']=$info_img;
	   }  
	}
	$json_data=json_encode($info);
	//保存
	D("TempItem")->itemedit(array('item_data'=>$json_data),$item_id);
  }
 
  /**
 * 更新所有模板分类
 * @param  int   $shop_id 商城id
 * @return array     分类导航信息
 * @author zhanghui
 */
 public function editclassall($shop_id=0){
	$data=$this->finditem2_byshop_id($shop_id);
	foreach($data as $val ){
	  $this->editoneclass($val['item_id']);
	}
	return true;
 }

  
}
?>