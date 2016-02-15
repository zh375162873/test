<?php
namespace Home\Model;
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
 


  
}
?>