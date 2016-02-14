<?php
namespace Admin\Model;
use Think\Model;
use Think\Upload;
class TempModel extends Model 
{ 
  protected $tableName = 'Temp'; 
  protected $fields = array(
  'temp_id', //	int(10) 			模板id
  'temp_title', // 	varchar	50			模板名称
  'create_time', 	//int(10) 			
  'content', // 	text			说明
  'shop_id', // 	int(10)			商城id
  'temp_type', // 	int(3)			模板类型（1：首页）
  'is_used', // 	int(3)			是否启用	
  '_pk'=>'id'//主键
  );
  

 //添加
 public function addtemp($param=true,$type=1){
   $param['create_time']=time();
   $param['is_used']=1;
   $param['temp_type']=$type;
   return   $this->data($param)->add();
 }
 //修改
 
 //删除
 
 //获取列表
 public function getlist($param=true){
   return   $this->where($param)->select();
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
		$map['temp_id'] = $id;
	}
	return $this->field($field)->where($map)->find();
 }
 
 /**
 * 获取详细信息
 * @param  milit   $id ID或标识
 * @param  boolean $field 查询字段
 * @return array     信息
 * @author zhanghui
 */
 public function getinfo($param=true,$field = true){
	return $this->field($field)->where($param)->find();
 }
 
  
}
?>