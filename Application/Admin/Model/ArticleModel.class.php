<?php
namespace Admin\model;
use Think\Model;

class ArticleModel extends Model{
	protected $tableName = 'article';
	protected $fields = array(
		'id', // id
		'author',
		'subject',
		'createtime',
		'lastmodifytime',
		'message',
		'_pk'=>'id'//主键
	);
	//标题自动验证
	protected $_validate=array(
		array('subject','require','文章标题必须非空'),
		array('subject','callback_checklen','标题内容过长',0,'callback'),
		array('message','require','文章内容必须非空'),
	);
	
	//字段长度验证回调函数(ThinkPHP会自动帮我们传递参数)
	function callback_checklen($data){
		if(strlen($data)>200){
			return false;
		}
		return true;
	}
	
	//自动完成，在create时自动执行
	//array('填充字段','填充内容','填充条件','附加规则');
	//填充字段 
	protected $_auto=array(
		array('createtime','time',1,'function'),
		array('lastmodifytime','time',2,'function'),
	);
}

?>