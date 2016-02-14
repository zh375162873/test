<?php
/**
现金商品信息模型
*/
namespace Home\Model;
use Think\Model;
class GoodsModel extends Model 
{ 
  protected $tableName = 'goods'; 

/**
 * 获取商品详细信息
 * @param  milit   $id 分类ID或标识
 * @param  boolean $field 查询字段
 * @return array     产品信息
 * @author zhanghui
 */
 public function getinfobyid($id=0,$field = true){
	/* 获取产品信息 */
	$map = array();
	if(is_numeric($id)){ //通过ID查询
		$map['goods_id'] = $id;
	} 
	return $this->field($field)->where($map)->find();
 } 

 
/**
 * 获取商品详细信息
 * @param  milit   $id 分类ID或标识
 * @param  boolean $field 查询字段
 * @return array     产品信息
 * @author zhanghui
 */
 public function getinfobycommonid($goods_commonid=0,$field = true){
	/* 获取产品信息 */
	$map = array();
	if(is_numeric($goods_commonid)){ //通过ID查询
		$map['goods_commonid'] = $goods_commonid;
	} 
	return $this->field($field)->where($map)->find();
 } 
 



/**
 *  卖货退货时修改总库存
 *  @param  $goods_commonid   商品公共id 
 *  @param  $num      数量  正数为销售  负数为退货
 *  @author  张辉
 */
 public function  changestorage($id,$num){
     $goods_commonid=$this->where('goods_id='.$id)->getField('goods_commonid');
     if($num>0){
	   //判断是否有此库存量的商品
	   $goods_storage=$this->where('goods_commonid='.$goods_commonid)->getField('goods_storage');
	   if($goods_storage>=$num){
	     $this->where("goods_commonid = $goods_commonid")->setDec('goods_storage',$num); // 减少库存数量
		 $this->where("goods_commonid = $goods_commonid")->setInc('goods_salenum',$num); // 增加销售数量
		 D('Admin/goodscommon')->changestorage($goods_commonid,$num); 
	   }else{
	    return -1;
	   }
	 }else{
	   $goods_salenum=$this->where('goods_commonid='.$goods_commonid)->getField('goods_salenum'); 
	   if($goods_salenum>=abs($num)){
	    $this->where("goods_commonid = $goods_commonid")->setDec('goods_salenum',abs($num)); // 减少销售数量
	    D('Admin/goodsstorehouse')->changestorage($goods_commonid,abs($num)); 
	    D('Admin/goodscommon')->changestorage($goods_commonid,$num); 
	   }else{
	    return -1;
	   } 
	 }
	 
	 return  1;
 } 
 
 
    //通过产品编号获取商品
	 /**
	 *  param  serial   商品编号
	 *  param  field  获取的字段
	 *  return  array  商品信息
	 */
	function getGoodsBySerial($serial,$field=true){ 
	    $goodscommon=D("goodscommon")->where("goods_serial = '$serial'")->find(); 
		$goods_commonid= $goodscommon['goods_commonid'];
		$goodsinfo=array();
		if($goodscommon){
		  $goodsinfo=D("goods")->where("goods_commonid = $goods_commonid")->field($field)->find();  
		}else{
		  return 0;
		}
		return  $goodsinfo;
	 }
}

?>