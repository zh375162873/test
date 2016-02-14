<?php

namespace BizService;

use BizService\BaseService;
/**
 * lbsService  lbs处理
 *
 * @author 张辉
 */
class LbsService extends BaseService {
	private $shop_id;
	private $url;
	private $ak;
	private $geotable_id;
	private $column;
	private $ak2;
	private $geotable_id2;
	public function _initialize(){
		$shop = get_shop_proxy();
		$this->shop_id = $shop['shop_id'];
		$this->ak ="cVYACkMpnKdlnR0IG6g7SN3X";
		$this->geotable_id ='120330';
		$this->column=array(
				'183767'=>'结束时间',
				'183766'=>'库存',
				'183175'=>'分类4',
				'183174'=>'分类3',
				'183173'=>'分类2',
				'183049'=>'分类1',
				'183031'=>'商城id',
				'182464'=>'产品id',
				'182446'=>'好评星级',
				'182445'=>'评价数',
				'169799'=>'产品价格',
				'169797'=>'产品公共id',
				'169794'=>'商户id'
		);
		$this->ak2 ="biTK0mmy5ZoLxW1cYYIY8GVx";
		$this->geotable_id2 ='126155';
	
	}
	/**
	 * 表字段信息
	 */
	public function column_list()
	{
		header("Content-Type: text/html;charset=utf-8");
		$url="http://api.map.baidu.com/geodata/v3/column/list";
		$params=array();
		$params['geotable_id']=$this->geotable_id;
		$params['ak']=$this->ak;
		$f=curl_http($url, $params, $method = 'GET');
		$d=json_decode($f);
		print_r($d);
	}
	
	
	/**
	 * 表字段信息
	 */
	public function column_list2()
	{
		header("Content-Type: text/html;charset=utf-8");
		$url="http://api.map.baidu.com/geodata/v3/column/list";
		$params=array();
		$params['geotable_id']="126155";
		$params['ak']="biTK0mmy5ZoLxW1cYYIY8GVx";
		$f=curl_http($url, $params, $method = 'GET');
		$d=json_decode($f);
		print_r($d);
	}
	
	
	/**
	 * 查询指定id列（detail column）详情接口
	 */
	public function column_detail()
	{
		header("Content-Type: text/html;charset=utf-8");
		$id = I('id', 0, 'intval');
		$url="http://api.map.baidu.com/geodata/v3/column/detail";
		$params=array();
		$params['geotable_id']=$this->geotable_id;
		$params['ak']=$this->ak;
		$params['id']=$id;
		$f=curl_http($url, $params, $method = 'GET');
		$d=json_decode($f);
		print_r($d);
	}
	
	/**
	 * 修改指定条件列（column）接口
	 type:
	 1:is_sortfilter_field,是否检索引擎的数值排序字段 1代表是，0代表否，可能会引起批量操作
	 2:is_search_field ,是否检索引擎的文本检索字段 ,1代表是，0代表否，会引起批量操作
	
	 1代表是，0代表否，会引起批量操作
	 */
	public function column_update()
	{
		header("Content-Type: text/html;charset=utf-8");
		$id = I('id', 0, 'intval');
		$type=I('type', 0, 'intval');
		$url="http://api.map.baidu.com/geodata/v3/column/update";
		$params=array();
		if($type==1){
			$params['is_sortfilter_field']=1;
		}elseif($type==2){
			$params['is_search_field']=1;
		}
		$params['geotable_id']=$this->geotable_id;
		$params['ak']=$this->ak;
		$params['id']=$id;
		$f=curl_http($url, $params, $method = 'GET');
		$d=json_decode($f);
		print_r($d);
	}
	
	//通过产品编号，查询产品
	public function findgoods_bytags($tags=0){
		$url="http://api.map.baidu.com/geodata/v3/poi/list";
		$params=array();
		$params['geotable_id']='126155';
		$params['ak']="biTK0mmy5ZoLxW1cYYIY8GVx";
		$params['tags']=$tags;
		$f=curl_http($url, $params, $method = 'GET');
		$d=json_decode($f,true);
		return $d;
	}
	
	
	//将产品信息添加到lbs上
	public function addgoods($params=array(),$tags=0){
		$data=$this->findgoods_bytags($tags);
		if($data['status']==0&&$data['pois']){
			
		}else{	
			//添加产品信息到lbs上面
			$url="http://api.map.baidu.com/geodata/v3/poi/create";
			$params['geotable_id']='126155';
		    $params['ak']="biTK0mmy5ZoLxW1cYYIY8GVx";
			$f=curl_http($url, $params, $method = 'POST');
		} 
	}
	
	//将产品信息修改到lbs上
	public function editgoods($params=array(),$tags=0){
		$data=$this->findgoods_bytags($tags);
		//如果有，就修改
		if($data['status']==0&&$data['pois']){
			$url="http://api.map.baidu.com/geodata/v3/poi/update";
			$params['geotable_id']='126155';
		    $params['ak']="biTK0mmy5ZoLxW1cYYIY8GVx";
		    $params['id']=$data['pois'][0][id];
			$f=curl_http($url, $params, $method = 'POST');
		}else{
		    //如果没有，就添加	
			$addparams=array();
			$addparams['title']=$params['title'];
			$addparams['address']=$params['address'];
			$addparams['longitude']=$params['longitude'];
			$addparams['latitude']=$params['latitude'];
			$addparams['coord_type']=$params['coord_type'];
			$addparams['store_id']=$params['store_id'];
			$addparams['shop_id']=$params['shop_id'];
			$addparams['goods_common_id']=$params['goods_common_id'];
			$addparams['price']=$params['price'];
			$addparams['goods_id']=$params['goods_id'];
			$addparams['evaluation_good_star']=$params['evaluation_good_star'];
			$addparams['evaluation_count']=$params['evaluation_count'];
			$addparams['tags']=$tags;
			$addparams['gc1']=$params['gc1'];
			$this->addgoods($addparams,$tags);
		}
	}

}