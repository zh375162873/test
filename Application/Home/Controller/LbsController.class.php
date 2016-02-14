<?php

namespace Home\Controller;
use Think\Controller;
use BizService\GoodshomeService;

class LbsController extends Controller
{
    private $shop_id;
	private $url;
	private $ak;
	private $geotable_id;
	private $column;
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
    
    
    public function ajax_goodslist()
    {
    	
    	//http://api.map.baidu.com/geosearch/v3/nearby?ak=cVYACkMpnKdlnR0IG6g7SN3X&geotable_id=120330&location=108.953435,34.26567&radius=1000000&sortby=distance:1|price:1&page_size=7&page_index=4&q=%E5%A5%BD%E5%90%83%E5%93%92
    	$type = I("type") ? I("type") : 0;
    	$gc_id = I("gc_id") ? I("gc_id") : 0;
    	$counting = I("counting") ? I("counting") : 1;
    	$oncenum = I("oncenum") ? I("oncenum") : 6;
    	$lat = I("lat") ? I("lat") : 0;
    	$lng = I("lng") ? I("lng") : 0;
    	//定位坐标
    	if ($lat && $lng) {
    		$data['lat'] = $lat;
    		$data['lng'] = $lng;
    	} elseif (session('lat') && session('lng')) {
    		$data['lat'] = session('lat');
    		$data['lng'] = session('lng');
    	} else {
    		$data['lat'] = "34.26567";
    		$data['lng'] = "108.953435"; /*else {
    		//百度定位
    		$mapService = new \BizService\MapService();
    		$map = $mapService->locationByIP();
    		$data['lat'] = $map['lat'];
    		$data['lng'] = $map['lng'];*/
    	}
    	if ($gc_id) {
    		$data['gc_id'] = $gc_id;
    	}
    	if ($type) {
    		$data['order'] = $type;
    	}
    	
    	$GoodshomeService = new GoodshomeService();
    	//调取代理信息
    	//$shop_info = get_shop_proxy();
    	$shop_id =  $this->shop_id ?  $this->shop_id : 1;
    	//获取传来的信息
    	$info = $_POST['info'];
    	$page = $counting;
    	$size = $oncenum;
    	$str = "";
    	$p_data = $this->mgoods_list_bylbs($data, 0);
    	
    	$count = count($p_data);
    	$max_page = ($count > 0) ? ceil($count / $size) : 1;
    	if ($page > $max_page) {
    		$array = array();
    		//echo json_encode($array);
    		//exit;
    	}
    	
    	$limit = "";
    	if ($page >= 1 && $size > 0) {
    		$num = $size;
    		$statnum = ($page - 1) * $num;
    		$limit = " $statnum,$num ";
    	}
    	
    	//一次性请求全部数据，从第二页算起,
    	if (I('loadLength') == 1) {
    		$num = $size;
    		$statnum = $page * $num;
    		$limit = " $num, $statnum";
    	}
    	
    	
    	$data['limit'] = $limit;
    	$data['page'] = $page;
    	$data['size'] = $size;
    	print_r($data);
    	$goodslist = $this->mgoods_list_bylbs($data, 0);
    	
    	if ($max_page < $page) {
    		$array = array();
    		echo json_encode($array);
    	} else {
    		echo json_encode($goodslist);
    	}
    
    }
    
    
    /**
     * 获取积分商品列表（前台）通过lbs
     * @todo 未进行条件查询
     * @param array $param
     * @param int $ispagenation
     * @return array
     */
    public function mgoods_list_bylbs($param=array(),$ispagenation=true){
    	$shop_info=get_shop_proxy();
    	$shop_id=$shop_info['shop_id']?$shop_info['shop_id']:1;
    	//定义url
    	$url="http://api.map.baidu.com/geosearch/v3/nearby";
    	//定义参数数组
    	$params=array();
    	//设置分页
    	if($param['page']>0){
    		$params['page_size']=$param['size'];
    		$params['page_index']=$param['page']-1;
    	}
    	//设置排序
    	$params['sortby']="distance:1|price:1";
    	//用户信息
    	$params['geotable_id']="120330";
    	$params['ak']="cVYACkMpnKdlnR0IG6g7SN3X";
    	//位置信息
    	$params['location']=$param['lng'].",".$param['lat'];
    	//半径
    	$params['radius']="5000000000";
    	//筛选条件
    	//1、分类筛选
    	$gc_id=$param['gc_id']?$param['gc_id']:1;
    	if($gc_id > 1){
    		//获取分类名称
    		$gc=M("goods_class")->where('gc_id='.$gc_id)->find();
    		$params['q']=$gc['gc_name'];
    	}
    	//2、商城筛选
    	// $params['filter']="shop_id:".$shop_id;
    	echo "****************************************";
    	$params['q']="好吃哒";
    	print_r($params);
    	echo "****************************************";
    	$f=curl_http($url, $params, $method = 'GET');
    	$d=json_decode($f,true);
    	$goodslist=$d['contents'];
    	$count=$d['total'];
    		
    	foreach($goodslist as $key=>$val){
    		$goodslist[$key]['goods_commonid']=$val['goods_common_id'];
    		$goodslist[$key]['longitude']=$val['location'][0];
    		$goodslist[$key]['latitude']=$val['location'][1];
    	}
    	
    	print_r($goodslist);
    	return $goodslist;
    }
    
    //将产品信息添加到lbs上
    public function addgoods($params=array()){
    	//添加产品信息到lbs上面
    	$url="http://api.map.baidu.com/geodata/v3/poi/create";
    	$params['geotable_id']="126155";
    	$params['ak']="biTK0mmy5ZoLxW1cYYIY8GVx";
    	$f=curl_http($url, $params, $method = 'POST');
    } 
    
    //将产品信息添加到lbs上
    public function editgoods($params=array()){
    	$url="http://api.map.baidu.com/geodata/v3/poi/update";
    	$params['geotable_id']="126155";
    	$params['ak']="biTK0mmy5ZoLxW1cYYIY8GVx";
		$f=curl_http($url, $params, $method = 'POST');
    }
    
    
    
    
    
    
    
    
    
    
    
    //查询产品
    /*		$url="http://api.map.baidu.com/geodata/v3/poi/list";
     $params=array();
     $params['geotable_id']='120330';
     $params['ak']="cVYACkMpnKdlnR0IG6g7SN3X";
     $params['goods_id']=$goods['goods_id'].",".$goods['goods_id'];
     $f=curl_http($url, $params, $method = 'GET');
     $d=json_decode($f,true);
     $c=$d['pois'];
     //修改
     if($c[0]){
     $url="http://api.map.baidu.com/geodata/v3/poi/update";
     $params=array();
     $params['geotable_id']='120330';
     $params['ak']="cVYACkMpnKdlnR0IG6g7SN3X";
     $params['id']=$c[0][id];
     $params['title']=$goods['goods_name'];
     $params['address']=$store['address'];
     $params['coord_type']=3;
     $params['longitude']=$store['longitude'];
     $params['latitude']=$store['latitude'];
     $params['store_id']=$goods['store_id'];
     $params['shop_id']=$goods['shop_id'];
     $params['goods_common_id']=$goods['goods_commonid'];
     $params['price']=$goods['goods_price'];
     $params['goods_id']=$goods['goods_id'];
     $params['evaluation_good_star']=$goods['evaluation_good_star'];
     $params['evaluation_count']=$goods['evaluation_count'];
     $f=curl_http($url, $params, $method = 'POST');
     }else{
     //添加产品
     $url="http://api.map.baidu.com/geodata/v3/poi/create";
     $params=array();
     $params['title']=$goods['goods_name'];
     $params['address']=$store['address'];
     $params['longitude']=$store['longitude'];
     $params['latitude']=$store['latitude'];
     $params['coord_type']=3;
     $params['geotable_id']='120330';
     $params['ak']="cVYACkMpnKdlnR0IG6g7SN3X";
     $params['store_id']=$goods['store_id'];
     $params['shop_id']=$goods['shop_id'];
     $params['goods_common_id']=$goods['goods_commonid'];
     $params['goods_name']=$goods['goods_name'];
     $params['price']=$goods['goods_price'];
     $params['goods_id']=$goods['goods_id'];
     $params['evaluation_good_star']=$goods['evaluation_good_star'];
     $params['evaluation_count']=$goods['evaluation_count'];
     $f=curl_http($url, $params, $method = 'POST');
     }*/
    
    //修改字段
    
    /*     $url="http://api.map.baidu.com/geodata/v3/column/update";
     $params=array();
     $params['id']="184646";
     $params['is_index_field']=1;
     $params['geotable_id']='126155';
     $params['ak']="biTK0mmy5ZoLxW1cYYIY8GVx";
     $f=curl_http($url, $params, $method = 'POST');
     echo $f;
    
    
     $url="http://api.map.baidu.com/geodata/v3/column/update";
     $params=array();
     $params['id']="182447";
     $params['is_index_field']=1;
     $params['geotable_id']='120330';
     $params['ak']="cVYACkMpnKdlnR0IG6g7SN3X";
     $f=curl_http($url, $params, $method = 'POST');
    
     $params=array();
     $params['id']="182447";
     $params['is_index_field']=1;
     $params['geotable_id']='120330';
     $params['ak']="cVYACkMpnKdlnR0IG6g7SN3X";
     $f=curl_http($url, $params, $method = 'POST');
     $params=array();
     $params['id']="182446";
     $params['is_index_field']=1;
     $params['geotable_id']='120330';
     $params['ak']="cVYACkMpnKdlnR0IG6g7SN3X";
     $f=curl_http($url, $params, $method = 'POST');
     $params=array();
     $params['id']="182445";
     $params['is_index_field']=1;
     $params['geotable_id']='120330';
     $params['ak']="cVYACkMpnKdlnR0IG6g7SN3X";
     $f=curl_http($url, $params, $method = 'POST');
     $params=array();
     $params['id']="169799";
     $params['is_index_field']=1;
     $params['geotable_id']='120330';
     $params['ak']="cVYACkMpnKdlnR0IG6g7SN3X";
     $f=curl_http($url, $params, $method = 'POST');
     $params=array();
     $params['id']="169797";
     $params['is_index_field']=1;
     $params['geotable_id']='120330';
     $params['ak']="cVYACkMpnKdlnR0IG6g7SN3X";
     $f=curl_http($url, $params, $method = 'POST');
     $params=array();
     $params['id']="169795";
     $params['is_index_field']=1;
     $params['geotable_id']='120330';
     $params['ak']="cVYACkMpnKdlnR0IG6g7SN3X";
     $f=curl_http($url, $params, $method = 'POST');*/
    

}