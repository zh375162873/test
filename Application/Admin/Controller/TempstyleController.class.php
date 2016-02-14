<?php
namespace Admin\Controller;
use BizService\UserService;
use Think\Controller;
use Think\Model;
use Think\Upload; 
use BizService\PeriodService;

class TempstyleController extends Controller {
	

	/**
	 * 模板列表
	 * @author zhanghui
	 */
    public function stylelist(){
	    $data=D("tempstyle")->getlist();
		$this->assign('data',$data);
		$this->display("style_list");
    }
	
	/**
	 * 模板添加
	 * @author zhanghui
	 */
    public function styleadd(){
		$this->display("style_add");
    }
    
	/**
	 * 模板修改
	 * @author zhanghui
	 */
    public function styleedit(){
	    //获取商城信息
		$shop_proxy=get_shop_proxy();
		if (IS_POST) { // 提交表单
		    $id=I('style_id');
		    $data['style_type'] = I('style_type');
			$data['style_title'] = I('style_title');
			$data['style_num'] = I('style_num');
			$data['style_is_used'] = I('style_is_used');
		    $data['style_order'] = I('style_order');
			//保存数据
			if (false !== D('tempstyle')->where('style_id='.$id)->data($data)->save()) {
				$this->success ( '编辑成功！', U ( '/admin/tempstyle/stylelist' ) );
			} else {
				$error = D('temp')->getError ();
				$this->error ( empty ( $error ) ? '未知错误！' : $error );
			}
		} else {
		    $style_id = I ( 'style_id' );
			/* 获取分类信息 */
			$info = $style_id ?D('tempstyle')->info ( $style_id ) : '';
			$this->assign ( 'info', $info );
			$this->assign ( 'style_id', $style_id );
			$this->display("style_edit");
		}
		
    }
	
	/**
	 * 模板删除
	 * @author zhanghui
	 */
    public function styledel(){
	   
    }
	
	
    //模板七，内容生成
    public function Style7()
    {
	
	     
    	$info=$_POST['info'];
		$shop_id = $_REQUEST['shop_id'] ? $_REQUEST['shop_id'] : 1;
		$lat = $info['lat'] ? $info['lat'] : "34.26567";
		$lng = $info['lng'] ? $info['lng'] : "108.953435";
		if(!$shop_id){
			//调取代理信息
			$shop_info = get_shop_proxy();
			$shop_id=$shop_info['shop_id']?$shop_info['shop_id']:1;
		}
		//获取传来的信息
		$info=$_POST['info'];
		$page         = isset($_REQUEST['counting'])   && intval($_REQUEST['counting'])  > 0 ? intval($_REQUEST['counting'])  : 1;
	    $size         = isset($info['info_num'])   && intval($info['info_num'])  > 0 ? intval($info['info_num'])  : 5;
	    $str="";
	   /* if(!empty($info['info_data']['1'])){
		  foreach($info['info_data']['1'] as $key=>$val){
		    $str.="  and   $key like  '%$val%' ";
		  }
	    }
	  
	    if(!empty($info['info_data']['0'])){
		  foreach($info['info_data']['0'] as $key=>$val){
		    $str.=" and   $key = $val ";
		  }
	    }*/

       // $info['info_data']['0']['gc_id']=19;
	    
	    /*if($str!=""||$limit!=""){
		$p_data=   M()->query("select a.* from ddt_goods as a ,ddt_goods_class_relation as b where a.goods_commonid=b.goods_commonid and b.class_id=".$info['info_data']['0']['gc_id']);
		   
	    }*/
	
	    $gc_id=$info['info_data']['0']['gc_id']?$info['info_data']['0']['gc_id']:1;
	    if($gc_id > 1){
		$p_data=   M()->query("select a.* from ddt_goods as a ,ddt_goods_class_relation as b,ddt_goods_common as c where a.goods_commonid=b.goods_commonid and a.goods_state>0 and a.shop_id= $shop_id   and b.class_id=".$gc_id);
	    }else{
		 $p_data=   M()->query("select * from ddt_goods   where shop_id= $shop_id  and goods_state>0 "); 
		}
		
		 
		
		
		
	    $count = count($p_data);
	    $max_page = ($count> 0) ? ceil($count / $size) : 1;

		if ($page > $max_page)
		{ 
		   $array=array();
		   echo json_encode($array);
		   exit;
		}

		$limit="";
		if($info['info_page']==1&&$info['info_num']>0){
		  $num=$info['info_num'];
		  $statnum=($page-1)*$num;
		  $limit=" limit $statnum,$num ";
		}
		
		//一次性请求全部数据，从第二页算起,
		if(I('loadLength')==1){
		    $num = $size;
            $statnum = $page * $num;
            $limit = " limit $num, $statnum";
		}
		
		
		
		
		
		 //调取配置信息
		$setting=M('setting')->where('shop_id='.$shop_id)->select();
		foreach($setting as $val){
		  switch($val['code']){
		    case 'SHOW_EXPIRED':
			  if($val['value']==1){
			  
			  }else{  
			     $sql_where=" and  c.end_date >  unix_timestamp(now())   "; 
			  }
			break;
			case 'SHOW_SOLD_OUT':
			  if($val['value']==1){
			  
			  }else{  
			     $sql_where=" and  a.goods_storage > 0   "; 
			  }
			break;
			
		  }
		}
		
		
		
		
		
		if($gc_id > 1){
		  $goodslist=   M()->query("select a.* from ddt_goods as a ,ddt_goods_class_relation as b  , ddt_goods_common as c  where a.goods_commonid=b.goods_commonid   and a.shop_id= $shop_id   and a.goods_state>=0    and  a.goods_commonid=c.goods_commonid  and b.class_id=".$gc_id.$sql_where."  order by a.goods_commend desc, a.goods_commonid  desc  ".$limit);
		}else{
		   $goodslist=   M()->query("select a.* from ddt_goods as a , ddt_goods_common as c  where a.shop_id= $shop_id   and a.goods_state>0   and  a.goods_commonid=c.goods_commonid ".$sql_where."  order by a.goods_commend desc,(a.goods_salenum+c.false_salenum) desc,  a.goods_commonid  desc  ".$limit);
		}
		   
		foreach($goodslist as $key=>$val){
		    //判断是否有限时抢购和定时段抢购
		    $PeriodService = new PeriodService();
            $Period = $PeriodService->get_period_sales($val['goods_id']);
			if($Period){
			$goodslist[$key]['shut']=$Period['shut'];
			}else{
			$goodslist[$key]['shut']=0;
			}
			$goodcommon_info=D('goodscommon')->where('goods_commonid='.$val['goods_commonid'])->find();
			$goodslist[$key]['position_tags']=$val['is_virtual']?$goodcommon_info['position_tags']:$goodcommon_info['post_tags'];
			$goodslist[$key]['subtitle']=$goodcommon_info['subtitle'];
			
		    $n_latitude = $lat;
            $n_longitude = $lng;
			$distance = getDistance_admin($n_latitude, $n_longitude, $val['latitude'], $val['longitude']);
		    $goodslist[$key]['distance'] =number_format($distance/1000, 2, '.', '');

			$goodslist[$key]['goods_storage'] = $val['goods_storage']?$val['goods_storage']:0;
			//判断是否为虚假库存
			if($val['storage_type']){
			  //判断是否小于等于最小调拨值，如果小于就重新调配库存
			  if($val['storage_num']<=$val['storage_type_num']){
			    //调取现有的库存是否够最大库存的数量,就将显示的库存显示为最大库存值，如果不是，就显示现有库存
				if($val['goods_storage']>=$val['storage_type_max']){
			      $val['storage_num']=$val['storage_type_max'];
				}else{
				  $val['storage_num']=$val['goods_storage'];
				}
				M('goods')->where('goods_id='.$val['goods_id'])->data('storage_num='.$good_info['storage_num'])->save();
			  }
			  $goodslist[$key]['goods_storage']=$val['storage_num']; 
			}
			if($Period){
		      if($Period['goods_limit']>0){
		        $goodslist[$key] = $Period['goods_num'];
		      }
		    }
			
		}
		
		
		if($max_page < $page){
		  $array=array();
		  echo json_encode($array);
		}else{
		  echo json_encode($goodslist);
		}
    }
	
	
	 //模板七，内容生成
    public function Style7_bylbs() 
    {

		$shop_id = I('shop_id',1,'intval');
		if(!$shop_id){
			//调取代理信息
			$shop_info = get_shop_proxy();
			$shop_id=$shop_info['shop_id']?$shop_info['shop_id']:1;
		}
		
		//获取传来的信息
		$info=$_POST['info'];
		$page         = isset($_REQUEST['counting'])   && intval($_REQUEST['counting'])  > 0 ? intval($_REQUEST['counting'])  : 1;
	    $size         = isset($info['info_num'])   && intval($info['info_num'])  > 0 ? intval($info['info_num'])  : 6;
	 
		//定义url
		$url="http://api.map.baidu.com/geosearch/v3/nearby";
		//定义参数数组
		$params=array();
		//获取分类信息
		$gc_id=$info['info_data']['0']['gc_id']?$info['info_data']['0']['gc_id']:1;
		//设置分页
		if($page){
		    $params['page_size']=$size;
			$params['page_index']=$page-1;
		}
		
		//设置排序
		$params['sortby']="distance:1|price:1";
		//用户信息
		$params['geotable_id']=C('MAP_TABLEID');
		$params['ak']=C('MAP_KEY');
		//位置信息
		$params['location']=$info['lng'].",".$info['lat'];
		//半径
		$params['radius']="5000000000";
		//筛选条件
		 //1、分类筛选
		$gc_id=$info['info_data']['0']['gc_id']?$info['info_data']['0']['gc_id']:1;
		if($gc_id > 1){
		    //获取分类名称
			$gc=M("goods_class")->where('gc_id='.$gc_id)->find();
		    $params['q']=$gc['gc_name'];
		}
		//2、商城筛选
		
		
		$f=curl_http($url, $params, $method = 'GET');
		$d=json_decode($f,true);
		
		$goodslist=$d['contents'];
		$count=$d['total'];

	    $max_page = ($count> 0) ? ceil($count / $size) : 1;
		
		
		foreach($goodslist as $key=>$val){
		    $good_info=D('Admin/goods')->where('goods_commonid='.$val['goods_common_id'])->find();
			$goodslist[$key]=$good_info;
			$goodcommon_info=D('goodscommon')->where('goods_commonid='.$val['goods_common_id'])->find();
			$goodslist[$key]['position_tags']=$goodcommon_info['position_tags'];
			$goodslist[$key]['subtitle']=$goodcommon_info['subtitle'];
			
		    $n_latitude = session('lat')?session('lat'):"34.26567";
            $n_longitude = session('lng')?session('lng'):"108.953435";
			$distance = $val['distance'];
		    $goodslist[$key]['distance'] =number_format($distance/1000, 2, '.', '');
			if($good_info['goods_state']==0){
			  unset($goodslist[$key]);
			}
			
			
		}
		
		if($max_page < $page){
		  $array=array();
		  echo json_encode($array);
		}else{
		  echo json_encode($goodslist);
		}
    }
	



	
	
}