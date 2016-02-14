<?php
/**
现金商品信息模型
*/
namespace Admin\Model;
use Think\Model;
use BizService\Geohash;
use BizService\MapService;
class GoodssendingModel extends Model 
{ 
  protected $tableName = 'goods_sending'; 
  protected $fields = array(
	  'id', // 商品id
	  'shop_id', //商城id
	  'goods_commonid', //商品公共id
	  'goods_id', //	商品id
	  'type',//  运费类型(1:包邮，2：收费)
	  'first_price',// 首件价格
	  'add_price',//续件价格
	  'is_free',//是否启用多件包邮
	  'free_num',//	多少件包邮
	  '_pk'=>'id'//主键
  );
   


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
 * 获取详细信息
 * @param  milit   $goods_commonid 产品公共id
 * @param  boolean $field 查询字段
 * @return array     信息
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
 * 新增商品
 * @param  array   $data 商品信息数组
 * @return int     商品id
 * @author zhanghui
 */ 
  public function  addData($data){
        $data_goods=array();
		$data_goods['shop_id']=$data['shop_id'];
		$data_goods['goods_commonid']=$data['goods_commonid'];
		$data_goods['type']=$data['sendtype'];
		$data_goods['first_price']=$data['first_price'];
		$data_goods['add_price']=$data['add_price'];
		$data_goods['is_free']=$data['is_free'];
		$data_goods['free_num']=$data['free_num'];
		$id=$this->add($data_goods);
		return  $id;
  }
  

  
  /**
   *更新信息
   * @param  $data  商品公共信息数组
   * @param  $id  商品公共id
   * @return  int   默认1
   * @author zhanghui  
  */
  public function  editData($data,$id){
        $data_goods=array();
		$data_goods['type']=$data['sendtype'];
		$data_goods['first_price']=$data['first_price'];
		$data_goods['add_price']=$data['add_price'];
		$data_goods['is_free']=$data['is_free'];
		$data_goods['free_num']=$data['free_num'];
	    $flag=$this->where("id=".$id)->data($data_goods)->save();
		return  $flag;
  }
  
   /**
   *更新信息
   * @param  $data  商品公共信息数组
   * @param  $id  商品公共id
   * @return  int   默认1
   * @author zhanghui  
  */
  public function  editDataBycommonid($data,$goods_commonid){
        $data_goods=array();
		$data_goods['type']=$data['sendtype'];
		if($data_goods['type']==2){
			$data_goods['first_price']=$data['first_price'];
			$data_goods['add_price']=$data['add_price'];
			$data_goods['is_free']=$data['is_free'];
			$data_goods['free_num']=$data['free_num'];
		}else{
			$data_goods['first_price']=0;
			$data_goods['add_price']=0;
			$data_goods['is_free']=0;
			$data_goods['free_num']=0;
		}
	    $flag=$this->where("goods_commonid=".$goods_commonid)->data($data_goods)->save();
		return  $flag;
  }

  
  
  

}

?>