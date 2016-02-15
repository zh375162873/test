<?php
namespace Admin\Model;
use Think\Model;
class GoodsstorehouselogModel extends Model 
{ 
  protected $tableName = 'goods_storehouse_log'; 
  protected $fields = array(
  'storehouse_logid', //记录id
  'storehouse_id', //商品库存id
  // 'goods_type',//商品类型（0：公共商品，1：现金商品，2：积分商品，3：活动商品）
  //'goods_id',//商品id
  'type', //进货加减库存（0：减库存，1：加库存）
  'price',// 进出库价格（如果是出库，默认是平均价，不用手动操作，商品上架时自动操作出库，如果是进库,前期按照仓库表的成本自动生成，后期可以自行填写实际进货价）
  'num',//进出库数量
  'ctime',//操作时间
  'shop_id',// 商城id（代理的用户id）
  'description',//备注
  '_pk'=>'storehouse_logid'//主键
  );
   //定义自动填充
 protected $_auto = array ( 
   array('ctime','time',1,'function'),    
 );

//通过商品id查询单个商品入库信息
public function  getinputstorebyid($goods_commonid){

}

//通商品id查询单个商品出库信息
public function  getoutstorebyid($goods_commonid){


}



 
 
 
}
?>