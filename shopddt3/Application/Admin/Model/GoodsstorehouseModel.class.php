<?php
namespace Admin\Model;
use Think\Model;
class GoodsstorehouseModel extends Model 
{ 
  protected $tableName = 'goods_storehouse'; 
  protected $fields = array(
	  'storehouse_id', //商品库存id
	  'goods_commonid',//商品公用id
	  'goods_name',//商品名称
	  'shop_id',//商城id
	  'store_type',// 供货商类型（1：代理下商家，2：渠道，3：合同）
	  'store_id',//供货商id(默认是代理下面的商家id，渠道id，或者合同id)
	  'gc_id',// 分类id
	  's_price',//价格最高（前期不需要）
	  'e_price',//价格最低（前期不需要）
	  'a_price',//成本价格
	  'num',//库存数(未分配库存数)
	  'description',// 备注
	  '_pk'=>'storehouse_id'//主键
  );
  
  

 
 //从库中删除 
 public function  delstorehouse($id){
 
 
 }
 //更新信息
 public function  editstorehouse($data = array(), $condition = array()){
 
 
 }

 /**录库
 *parm   $data 入库信息并记录入库日志
 *       $data{goods_commonid,goods_name,shop_id,store_type,store_id,gc_id,a_price,num,description}
 *       张辉
 */
 
 /**
  *  录库
  *  @param  $goods_commonid  商品公共id
  *  @param  $goods_name 商品名称
  *  @param  $shop_id   商城id
  *  @param  $store_type  来源类型
  *  @param  $store_id  商户id
  *  @param  $gc_id  分类
  *  @param  $a_price  价格
  *  @param  $num  库存
  *  @author  张辉
  */
 public function  Addstoreinfo($goods_commonid=0,$goods_name=0,$shop_id=0,$store_type=0,$store_id=0,$gc_id=0,$a_price=0,$num=0,$description=''){
    //将数写入库中
	$data=array();
	$data['goods_commonid']=$goods_commonid;
	$data['goods_name']=$goods_name;
	$data['shop_id']=$shop_id;
	$data['store_type']=$store_type;
	$data['store_id']=$store_id;
	$data['gc_id']=$gc_id;
	$data['a_price']=$a_price;
	$data['num']=$num;
    $storehouse_id=$this->add($data);
	//记录入库信息
    $this->inputstroe($storehouse_id,$a_price,$num,$shop_id);
 }
 
/**
  *  入库（公共商品信息库存修改）
  *  @param  $storehouse_id  主表库存id 
  *  @param  $price 出库价格
  *  @param  $num   出库数量
  *  @param  $shop_id  所属商城
  *  @param  $description  描述
  *  @param  Additional 预留的参数信息。
  *  @author  张辉
  */
 public function  inputstroe($storehouse_id=0,$price=0,$num=0,$shop_id=0,$description="",$additional=array()){
    //将信息加入入库记录表中
        $data=array();
		$data['storehouse_id']=$storehouse_id;
		$data['type']=1;
		$data['price']=$price;
		$data['num']=$num;
		$data['ctime']=time();
		$data['shop_id']=$shop_id;
		$data['description']=$description;
		$storehouselog_id=D('goodsstorehouselog')->add($data);
		
	    //修改库存主表的库存信息(暂时不需要)
	    /*$storehouse_id = $data['storehouse_id'];
   	    $type = 1;
	    $num = $data['num'];
	    $storehouselog_id=$this->editstorage($storehouse_id,$type,$num);*/
 }
 
 /**
  *  出库（公共商品信息库存修改）
  *  @param  $storehouse_id  主表库存id 
  *  @param  $price 出库价格
  *  @param  $num   出库数量
  *  @param  $shop_id  所属商城
  *  @param  $description  描述
  *  @author  张辉
  */
 public function  outstroe($storehouse_id=0,$price=0,$num=0,$shop_id=0,$description=""){
    //将信息加入出库记录表中
		$data=array();
		$data['storehouse_id']=$storehouse_id;
		$data['type']=0;
		$data['price']=$price;
		$data['num']=$num;
		$data['ctime']=time();
		$data['shop_id']=$shop_id;
		$data['description']=$description;
		$storehouselog_id=D('goodsstorehouselog')->add($data);
	//修改库存主表的库存信息（暂时不需要）
/*	    $type = 0;
	    $num = $data['num'];
  	    $storehouselog_id=$this->editstorage($storehouse_id,$type,$num);*/
 }
 
/**
  *  入库（分配商品库存修改）
  *  @param  $storehouse_id  主表库存id 
  *  @param  $price 出库价格
  *  @param  $num   出库数量
  *  @param  $shop_id  所属商城
  *  @param  $description  描述
  *  @param  Additional 预留的参数信息。
  *  @author  张辉
  */
 public function  assigninput($storehouse_id=0,$price=0,$num=0,$shop_id=0,$description="",$additional=array()){
        //将信息加入入库记录表中
        $data=array();
		$data['storehouse_id']=$storehouse_id;
		$data['type']=1;
		$data['price']=$price;
		$data['num']=$num;
		$data['ctime']=time();
		$data['shop_id']=$shop_id;
		$data['description']=$description;
		$storehouselog_id=D('goodsstorehouselog')->add($data);
	    //修改库存主表的库存信息
	    $storehouse_id = $data['storehouse_id'];
   	    $type = 1;
	    $num = $data['num'];
	    $storehouselog_id=$this->editstorage($storehouse_id,$type,$num);
		return 1;
 }
 
 /**
  *  出库（分配商品库存修改）
  *  @param  $storehouse_id  主表库存id 
  *  @param  $price 出库价格
  *  @param  $num   出库数量
  *  @param  $shop_id  所属商城
  *  @param  $description  描述
  *  @author  张辉
  */
 public function  assignout($storehouse_id=0,$price=0,$num=0,$shop_id=0,$description=""){
        //判断出库量是否有超出现有库存
		$flag=$this->check_data($storehouse_id,1,$num);
		if($flag!=1){
		  return $flag;
		  exit;
		}
        //将信息加入出库记录表中
		$data=array();
		$data['storehouse_id']=$storehouse_id;
		$data['type']=0;
		$data['price']=$price;
		$data['num']=$num;
		$data['ctime']=time();
		$data['shop_id']=$shop_id;
		$data['description']=$description;
		$storehouselog_id=D('goodsstorehouselog')->add($data);
	//修改库存主表的库存信息
        $type = 0;
	    $num = $data['num'];
  	    $storehouselog_id=$this->editstorage($storehouse_id,$type,$num);
		
		return 1;
 }
  
 
 
 
 
 
/**
 *  修改库存主表的库存信息,修改主表库存
 *  @param  $storehouse_id   主表库存id 
 *  @param  $type     增加库存：1  减少：0,  修正：2
 *  @param  $num      数量
 *  @author  张辉
 */
 public function  editstorage($storehouse_id,$type,$num){
	if($type==1){
	  $this->where("storehouse_id = $storehouse_id")->setInc('num',$num); // 增加库存数量
	}elseif($type==0){
	  $this->where("storehouse_id = $storehouse_id")->setDec('num',$num); // 增加库存数量
	}elseif($type==2){
	  $this->where("storehouse_id = $storehouse_id")->data(array('num'=>$num))->save(); // 增加库存数量
	}
 }
 
 /**
 *  判断分配库存时，出入库数据否正常
 *  @param  $storehouse_id   主表库存id 
 *  @param  $type     增加库存：1  减少：0
 *  @param  $num      数量
 *  @author  张辉
 */
 public function  check_data($storehouse_id,$type,$num){
    // 增加库存数量时，数据检查
	if($type==1){
	  //判断库存中是否还有剩余剩余库存用于增加相应产品库存，出库操作
	  $data=$this->where('storehouse_id='.$storehouse_id)->find(); 
	  //判断是否够出库的
	  $store_num=$data['num'];
	  if($store_num<$num){
	    return  -1;
	  }else{
	    return  1;
	  }
	//减少库存数据检查
	}elseif($type==0){
	   //判断要要入库操作的数据是否合法，总数量应该等于商品剩余库存总数，如果有偏差，表明数据有误(暂时不做)。
	   return  1;
	}
 }
 
/**
 *  获取库存主表信息
 *  @param  $goods_commonid   商品公共id 
 *  @param  $field  获取字段 
 *  @author  张辉
 */
 public function  info($goods_commonid=0, $field = true){
    /* 获取分类信息 */
	$map = array();
	if(is_numeric($goods_commonid)){ //通过ID查询
		$map['goods_commonid'] = $goods_commonid;
	} 
	return $this->field($field)->where($map)->find();
 
 }
 
/**
 *  退货时修改总库存
 *  @param  $goods_commonid   商品公共id 
 *  @param  $num      数量
 *  @author  张辉
 */
 public function  changestorage($goods_commonid,$num){
	 return  $this->where("goods_commonid = $goods_commonid")->setInc('num',$num); // 增加库存数量
 } 

 
}
?>