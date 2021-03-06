<?php
namespace Admin\Model;
use Think\Model;
class IntegralgoodsModel extends Model 
{ 
  protected $tableName = 'integral_goods'; 
  protected $fields = array(
   'goods_id',//商品id
   'goods_commonid', //  int(10)  商品公用id  
   'goods_name',// 商品名称
   'shop_id', //商城id
   'store_type', //供货商类型（1：代理下商家，2：渠道，3：合同）
   'store_id', // 供货商id(默认是代理下面的商家id，渠道id，或者合同id)
   'store_name', //店铺名称
   'gc_id', //商品分类id
   'gc_id_1', //一级分类id
   'gc_id_2', //二级分类id
   'gc_id_3', //三级分类id
   'goods_price', //商品成本价格
   'goods_marketprice', //市场价
   'goods_storage_alarm', //库存报警值
   'goods_click', //商品点击数量
   'goods_salenum', //已兑换数量
   'goods_collect', //	收藏数量
   'goods_storage', //商品库存
   'goods_image', //商品主图
   'goods_state', //商品状态 0下架，1正常，10违规（禁售）
   'goods_addtime', //商品添加时间
   'goods_edittime', //商品编辑时间
   'areaid_1', //一级地区id
   'areaid_2', //二级地区id
   'areaid_3', //三级地区id（区）
   'goods_commend', //商品推荐 1是，0否 默认0
   'goods_integral',//所需积分
   'limit_num', //单个用户限制购买数量
   'time_start',//兑换开始时间
   'time_end',//兑换结束时间
   'limit_user_rank',//限制会员级别
   '_pk'=>'goods_id'//主键
   );
 //定义自动填充
 protected $_auto = array ( 
   array('goods_addtime','time',1,'function'),    
   array('goods_edittime','time',2,'function'), 
 );
 
 
   //新增商品
  public  function  addGoods($data){
        $data_integralgoods=array();
		$data_integralgoods['goods_name']=$data['goods_name'];
		$data_integralgoods['goods_commonid']=$data['goods_commonid'];
		$data_integralgoods['shop_id']=$data['shop_id'];
		$data_integralgoods['store_type']=1;
		$data_integralgoods['store_id']=$data['store_id'];
		$data_integralgoods['store_name']=$data['store_name'];
		$data_integralgoods['store_id']=$data['store_id'];
		$data_integralgoods['store_name']=$data['store_name'];
		$data_integralgoods['areaid_1']=$data['province'];
		$data_integralgoods['areaid_2']=$data['city'];
		$data_integralgoods['areaid_3']=$data['district'];
		$data_integralgoods['goods_price']=$data['goods_price'];
		$data_integralgoods['goods_marketprice']=$data['goods_marketprice'];
		$data_integralgoods['goods_image']=$data['goods_image'];
		$data_integralgoods['goods_state']=0;
		$data_integralgoods['goods_integral']=0;
		$data_integralgoods['limit_num']=$data['goods_limit'];
		$data_integralgoods['goods_addtime']=time();
		$data_integralgoods['goods_edittime']=time();
        $goods_id=$this->add($data_integralgoods);
		return  $goods_id;
  }
  
  //删除商品
  public  function  delGoods($id){
         $data=array();
	     $data['goods_state']=-1;
	     $this->where('goods_commonid='.$id)->data($data)->save();
  }
  
  //更新商品
  public  function  editGoods($data, $goods_commonid){
        $data_integralgoods=array();
		$data_integralgoods['goods_name']=$data['goods_name'];
		$data_integralgoods['goods_price']=$data['goods_price'];
		//$data_integralgoods['goods_integral']=$data['goods_integral'];
		$data_integralgoods['goods_marketprice']=$data['goods_marketprice'];
		$data_integralgoods['store_name']=$data['store_name'];
		$data_integralgoods['store_id']=$data['store_id'];
		$data_integralgoods['limit_num']=$data['goods_limit'];
		$data_integralgoods['areaid_1']=$data['province'];
		$data_integralgoods['areaid_2']=$data['city'];
		$data_integralgoods['areaid_3']=$data['district'];
		$data_integralgoods['goods_edittime']=time();
        $flag=$this->where("goods_commonid=".$goods_commonid)->data($data_integralgoods)->save();
  }
  
    //通过商品公用id更新产品信息
  public function   editGoodsbycommonid($goods_commonid,$data){
		 $flag=$this->where("goods_commonid=".$goods_commonid)->data($data)->save();
	     return $flag;
  }
  
  
     /**
 * 获取商品详细信息
 * @param  milit   $id 分类ID或标识
 * @param  boolean $field 查询字段
 * @return array     产品信息
 * @author zhanghui
 */
 public function info($goods_commonid=0,$id=0,$field = true){
	/* 获取产品信息 */
	$map = array();
	if(is_numeric($goods_commonid)){ //通过ID查询
		$map['goods_commonid'] = $goods_commonid;
	} 
	if(is_numeric($id)&&$id>0){ //通过ID查询
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
	   if($goods_salenum>=$num){
	    $this->where("goods_commonid = $goods_commonid")->setDec('goods_salenum',$num); // 减少销售数量
	    D('Admin/goodsstorehouse')->changestorage($goods_commonid,abs($num)); 
	    D('Admin/goodscommon')->changestorage($goods_commonid,$num); 
	   }else{
	    return -1;
	   } 
	 }
	 
	 return  1;
 } 



}
?>