<?php
namespace Admin\Model;
use Think\Model;
use Think\Upload;
class TempstyleModel extends Model 
{ 
  protected $tableName = 'temp_style'; 
  protected $fields = array(
  'style_id', //	int(10) 	否		模板样式id
  'style_type', // 	int(10) 	否		样式类型  布局：1  产品：2   列表：3	
  'style_title', 	//varchar(50) 	否		样式名称
  'style_num', // 	int(10)	否		数据数量（0：不限）	
  'style_is_used', // 	int(3)	否		是否启用		
  'style_img', // 	varchar(255)	否		样式图片效果
  'style_order', // 	int(10)	否		样式排序
  '_pk'=>'id'//主键
  );
  
  
  
   //获取列表
 public function getlist($param=true){
   return   $this->where($param)->order("style_id asc")->select();
 }

  /**
 * 获取详细信息
 * @param  milit   $id ID或标识
 * @param  boolean $field 查询字段
 * @return array     信息
 * @author zhanghui
 */
 public function info($id, $field = true){
	/* 获取分类信息 */
	$map = array();
	if(is_numeric($id)){ //通过ID查询
		$map['style_id'] = $id;
	}
	return $this->field($field)->where($map)->find();
 }
  
  

  
}
?>