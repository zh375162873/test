<?php
namespace BizService;
use BizService\GoodsstoreService;

/**
 * 商品添加，修改，删除,上架 
 *
 * @author 张辉
 */ 
class GoodsService extends BaseService { 

	/**  添加商品基本信息
	 * @param $data 商品信息
	 * @return int
	 */ 
	public function addGoods($data){
	   //获取商城信息
	   $shop_id=$data['shop_id']?$data['shop_id']:1; 
	   //增加goods_common表信息
	   $goods_commonid=D('goodscommon')->addGoods($data);
	   $data['goods_commonid']=$goods_commonid;
	   //添加图片到图片管理表中
	   foreach ($data['goods_lunbo'] as $c) {
	      D('goodsimages')->data(array('goods_commonid'=>$goods_commonid,'shop_id'=>$shop_id, 'goods_image'=>$c))->add();
	   }
	   //添加主图到管理表中
	   D('goodsimages')->data(array('goods_commonid'=>$goods_commonid,'shop_id'=>$shop_id, 'goods_image'=>$data['goods_image'],'is_default'=>1))->add();
	   //分类关联
	   if($data['gc_id']){
	     $class_data=$data['gc_id'];
	   }else{
	     //查询默认分类
		 $class_moren=M('goods_class')->where("(is_all=1 or gc_name='随便看看') and shop_id=".$shop_id)->find();
	     $class_data[0]=$class_moren['gc_id'];
	   }
	   D('goodsclassrelation')->addclass($class_data,$goods_commonid,1);
       //录库
	   $goods_name=$data['goods_name'];
	   //$shop_id=1;
	   $store_type=$data['store_type'];
	   $store_id=$data['store_id'];
	   $gc_id=$data['gc_id'];
	   $a_price=$data['goods_price'];
	   $num=$data['goods_storage'];
	   D('goodsstorehouse')->Addstoreinfo($goods_commonid,$goods_name,$shop_id,$store_type,$store_id,$gc_id,$a_price,$num);
       //添加goods
	    $flag=D('goods')->addGoods($data);
       //添加integral_goods
		$flag=D('integralgoods')->addGoods($data);
       //添加prize_goods
	    $flag=D('prizegoods')->addGoods($data);
	  //添加快递配置信息
	    $data['shop_id']=$shop_id;
	    $flag=D('goodssending')->addData($data);	
		
	    return $goods_commonid;
	}
	
	/**  修改商品基本信息
	 * @param $data 商品信息
	 * @return int
	 */
	public function editGoods($data,$goods_commonid){
		$goods_commonid=$data['goods_commonid'];
		$shop_info=get_shop_proxy();
		$shop_id=$shop_info['shop_id']?$shop_info['shop_id']:0;
		//修改库存
		    //判断是否符合要求，减少后，不少于分配出去的库存量
			   $goodsstore = new GoodsstoreService();
			   $storage_num=$goodsstore->outstoreinfo($goods_commonid);
			   if($storage_num<=$data['goods_storage']){
			       $flag=$goodsstore->setstorage($goods_commonid,$data['goods_storage']); 
			   }else{
			      return -1;
			   } 
		//分类关联
		$class_data=$data['gc_id'];
		//分类关联
	   if($class_data){
	     $class_data=$data['gc_id'];
	   }else{
	     //查询默认分类
		 $class_moren=M('goods_class')->where("(is_all=1 or gc_name='随便看看') and shop_id=".$shop_id)->find();
	     $class_data[0]=$class_moren['gc_id'];
	   }
	   
		D('goodsclassrelation')->addclass($class_data,$goods_commonid,1);
		
		//修改goods_common
		D('goodscommon')->editGoods($data,$goods_commonid);
		
		//添加图片到图片管理表中
		//删除原来的数据,添加轮播图到数据表中
		D('goodsimages')->where(array('goods_commonid'=>$goods_commonid))->delete();
		foreach ($data['goods_lunbo'] as $c) {
		   D('goodsimages')->data(array('goods_commonid'=>$goods_commonid,'shop_id'=>$shop_id, 'goods_image'=>$c))->add();
		}
		//添加主图到管理表中
		D('goodsimages')->data(array('goods_commonid'=>$goods_commonid,'shop_id'=>$shop_id, 'goods_image'=>$data['goods_image'],'is_default'=>1))->add();
		//修改goods
		$flag=D('goods')->editGoods($data,$goods_commonid);
		//修改integral_goods
		$flag=D('integralgoods')->editGoods($data,$goods_commonid);
		//修改prize_goods
		$flag=D('prizegoods')->editGoods($data,$goods_commonid); 
		//修改发货信息
		$gooodssending=D('goodssending')->getinfobycommonid($goods_commonid);
		if($gooodssending){
		D('goodssending')->editDataBycommonid($data,$goods_commonid);
		}else{
		D('goodssending')->addData($data);
		}
		
		
		return  1;
	}
	
	
	 //删除商品信息
   public function  delGoods($id){
     //先隐藏
	   //判断是否有卖出的商品和剩余库存
	    $salenum=D('goodscommon')->where('goods_commonid='.$id)->getField('goods_salenum'); 
		if($salenum>0){
		  return -1;
		}
	 
	   //删除common表商品
	     D('goodscommon')->delGoods($id);
	   //删除goods表商品
	     D('goods')->delGoods($id);
	   //删除integral_goods表商品
	     D('integralgoods')->delGoods($id);
	   //删除prize_goods表商品
	     D('prizegoods')->delGoods($id);
         return 1;
   }
	
	
	/**  修改商品上架数量
	 * @param $data 商品信息
	 * @return int
	 */
	public function stateGoods($data,$goods_commonid){
		   //现金商品参数
		   $goods_id1=$data['goods_id1'];
		   $storage1=$data['storage1'];
		   $goods_commend1=$data['goods_commend1'];
		   $state1=$data['state1'];
		   $begin_time1=strtotime($data['begin_time1']);
		   //积分商品参数
		   $goods_id2=$data['goods_id2'];
		   $storage2=$data['storage2']; 
		   $goods_commend2=$data['goods_commend2'];
		   $state2=$data['state2'];
		   $begin_time2=strtotime($data['begin_time2']);
		   $goods_integral=$data['goods_integral'];
		   //活动商品参数
		   $goods_id3=$data['goods_id3'];
		   $storage3=$data['storage3']; 
		   $goods_commend3=$data['goods_commend3'];
		   $state3=$data['state3'];
		   $begin_time3=strtotime($data['begin_time3']);
		   $prize_goods_price=$data['prize_goods_price'];
		   if($prize_goods_price==1){
		    $prize_goods_price=$data['prize_price_value'];
		   } 
		   
		   
		   //判断分配的库存是否大于总库存
		   $storages_temp=$storage1+$storage2+$storage3;
		   $storages=M('goods_common')->where('goods_commonid='.$goods_commonid)->field('goods_storage')->find();
		   if($storages_temp> $storages['goods_storage']){
		     return -2;
			 exit;  
		   }else{
		     $goodsstore = new GoodsstoreService();
		     //修改剩余库存
			 $flag=$goodsstore->setstorage($goods_commonid,$storages_temp,0,4); 
		   }
		   
		   
		//修改现金库存信息 
		   
		   //修改库存
	       /*$flag=$goodsstore->setstorage($goods_commonid,$storage1,0,1); 
		   if($flag!=1){
		      return -1;
			 exit;
		   }*/
		   //修改商品信息
		   $goods_data1=array();
		   $goods_data1['goods_commend']=$goods_commend1;
		   $goods_data1['goods_state']=$state1;
		   $goods_data1['goods_storage']=$storage1;
		   //修改现金商品显示库存设置
		   $goods_data1['storage_type']=$data['storage_type'];
		   if($goods_data1['storage_type']){
		     $goods_data1['storage_type_max']=$data['storage_type_max'];
		     $goods_data1['storage_type_num']=$data['storage_type_num'];
		   }else{
		     $goods_data1['storage_type_max']=0;
		     $goods_data1['storage_type_num']=0;
		   }
		   
		   $flag=D('goods')->editGoodsbycommonid($goods_commonid,$goods_data1);
		   //如果是定时上架
		   /*if($state1==0){
		     D('cron')->addcron($goods_commonid,1,$begin_time1);
		   }else{
		     D('cron')->delcron($goods_commonid,1);
		   }*/
		//修改积分库存信息
		  //修改库存
	       /*$flag=$goodsstore->setstorage($goods_commonid,$storage2,0,2); 
		   if($flag!=1){
		     return -1;
			 exit;  
		   }*/
		   //修改商品信息
		   $goods_data2=array();
		   $goods_data2['goods_commend']=$goods_commend2;
		   $goods_data2['goods_state']=$state2;
		   $goods_data2['goods_storage']=$storage2;
		   $goods_data2['goods_integral']=$goods_integral;
		   $flag=D('integralgoods')->editGoodsbycommonid($goods_commonid,$goods_data2);
		   //如果是定时上架
		   /*if($state2==0){
		     D('cron')->addcron($goods_commonid,2,$begin_time2);
		   }else{
		     D('cron')->delcron($goods_commonid,2);
		   }*/
		
		//修改活动库存信息
		   //修改库存
	      /* $flag=$goodsstore->setstorage($goods_commonid,$storage2,0,3); 
		   if($flag!=1){
		     return -1;
			 exit;
		   }*/
		   //修改商品信息
		   $goods_data3=array();
		   $goods_data3['goods_commend']=$goods_commend3;
		   $goods_data3['goods_state']=$state3;
		   $goods_data3['goods_storage']=$storage3;
		   $goods_data3['goods_price']=$prize_goods_price;
		   $flag=D('prizegoods')->editGoodsbycommonid($goods_commonid,$goods_data3);
		   /*if($state3==0){
		     D('cron')->addcron($goods_commonid,3,$begin_time3);
		   }else{
		     D('cron')->delcron($goods_commonid,3);
		   } */
		   return 1;
	}
	
/**  商品统一下架
	 * @param $id 商品公共id
	 * @return int
	 */
	public function statedownGoods($id){
		   //修改商品信息
		   $goods_data1=array();
		   $goods_data1['goods_state']=0;
		   $flag=D('goods')->editGoodsbycommonid($id,$goods_data1);
		
		   //修改商品信息
		   $goods_data2=array();
		   $goods_data2['goods_state']=0;
		   $flag=D('integralgoods')->editGoodsbycommonid($id,$goods_data2);		

		   //修改商品信息
		   $goods_data3=array();
		   $goods_data3['goods_state']=0;
		   $flag=D('prizegoods')->editGoodsbycommonid($id,$goods_data3);
		   return 1;
	}
	
/**  商品设置上架下架
	 * @param $id 商品公共id
	 * @param $goods_type 商品类型  1：现金商品  2:积分商品  3：活动商品
	 * @param $type 操作类型  1：上架， 0：下架
	 * @return int
	 */
	public function setstateGoods($id,$goods_type,$type){
	
	      switch($goods_type){
		   case 1:
			   //修改商品信息
			   $goods_data1=array();
			   $goods_data1['goods_state']=$type;
			   $flag=D('goods')->editGoodsbycommonid($id,$goods_data1);
			   break;
		   case 2:
			   //修改商品信息
			   $goods_data2=array();
			   $goods_data2['goods_state']=$type;
			   $flag=D('integralgoods')->editGoodsbycommonid($id,$goods_data2);	
			   break;	
           case 3:
			   //修改商品信息
			   $goods_data3=array();
			   $goods_data3['goods_state']=$type;
			   $flag=D('prizegoods')->editGoodsbycommonid($id,$goods_data3);
			   break;
		  } 
		   return 1;
	}
		
	
	
	
	
	
	
	
	/**
	 * 获取商品列表（后台）
	 * @todo 未进行条件查询
	 * @param array $param
	 * @param int $ispagenation
	 * @return array
	 */
	public function goods_common_list($param=array(),$ispagenation=true){
	    $query = $this->	getgoodssql($param);
		if($ispagenation){
		    $count = count(M()->query($query));
			$data=mypage($count,$query);
			foreach($data['list'] as $key=>$val){
			  $data['list'][$key]=D("goodscommon")->where('goods_commonid='.$val['goods_commonid'])->find();
			}
			return $data;
		}else{
	        $data=	M()->query($query);
		    foreach($data as $key=>$val){
			  $data[$key]=D("goodscommon")->where('goods_commonid='.$val['goods_commonid'])->find();
			}
			return $data;
		}
	}
	

	
	
	
	
	/**
	 * 获取商品列表（后台）
	 * @todo 未进行条件查询
	 * @param array $param
	 * @param int $ispagenation
	 * @return array
	 */
	public function goods_list($param=array(),$ispagenation=true){
		$query = $this->	getmgoodssql($param);
		if($ispagenation){
			$count = count(M()->query($query));
			$data=mypage($count,$query);
			foreach($data['list'] as $key=>$val){
			  $data['list'][$key]=D("goods")->where('goods_commonid='.$val['goods_commonid'])->find();
			  $data['list'][$key]['goods_serial']=D("goodscommon")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_serial');
			}
			return $data;
		}else{
			return M()->query($query);
		}
	}
	
	
	/**
	 * 获取积分商品列表（后台）
	 * @todo 未进行条件查询
	 * @param array $param
	 * @param int $ispagenation
	 * @return array
	 */
	public function igoods_list($param=array(),$ispagenation=true){
		$query = $this->	getigoodssql($param);
		if($ispagenation){
			$count = count(M()->query($query));
			$data=mypage($count,$query);
			foreach($data['list'] as $key=>$val){
			  $data['list'][$key]=D("integral_goods")->where('goods_commonid='.$val['goods_commonid'])->find();
			  $data['list'][$key]['goods_serial']=D("goodscommon")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_serial');
			}
			return $data;
		}else{
			return M()->query($query);
		}
	}
	
	
	/**
	 * 获取活动商品列表（后台）
	 * @todo 未进行条件查询
	 * @param array $param
	 * @param int $ispagenation
	 * @return array
	 */
	public function pgoods_list($param=array(),$ispagenation=true){
	
		$query = $this->	getpgoodssql($param);
		if($ispagenation){
			$count = count(M()->query($query));
			$data=mypage($count,$query);
			foreach($data['list'] as $key=>$val){
			  $data['list'][$key]=D("prize_goods")->where('goods_commonid='.$val['goods_commonid'])->find();
			  $data['list'][$key]['goods_serial']=D("goodscommon")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_serial');
			}
			return $data;
		}else{
			return M()->query($query);
		}
	}
	
	/**
	 * 获取公共商品列表的sql语句（后台）
	 * @param array $data
	 * @return array
	 */
	public  function getgoodssql($data){
		//搜索字段
		$array=$data;
		//排序
		$order =isset($data['order'])?$data['order']:""; 
		//分页数据
		$limit =isset($data['limit']) ?'  limit '.$data['limit']  : '';
		//分组
        $group = " GROUP BY goods_commonid";
		$where="";
		//构造sql子查询语句
		/*$sql_select = "  a.goods_commonid   ";
		$sql_table = " ddt_goods_class_relation a  "; 
	    $sql_where = " 1=1";
		$sql_group = "  a.goods_commonid   ";
		$sql_order = "  a.goods_commonid  desc ";  */
		
		$sql_select = "  a.goods_commonid   ";
		$sql_table = " ddt_goods_common a  "; 
	    $sql_where = "  a.goods_state>=0  and a.shop_id=".$data['shop_id'];
		$sql_group = "  a.goods_commonid   ";
		$sql_order = "  a.goods_commonid  desc ";  
		
		
		switch($order){
		  case 1:
		    //产品编号降序
			$sql_order= "a.goods_serial asc ,".$sql_order;
			break;
		  case 2:
		    //产品编号升序
			$sql_order= "a.goods_serial desc ,".$sql_order;
			/*$sql_table.=",mdd_yp_product b ";
			$sql_select.=",b.price"; 
			$sql_where.=" and a.infoid=b.id";
			$sql_order = " b.price desc,".$sql_order; */
			break;
		  case 3:
		    //产品名称降序
			$sql_order= "a.goods_name asc ,".$sql_order;
			break;
		  case 4:
		    //产品名称降序
			$sql_order= "a.goods_name desc ,".$sql_order;
			break;	
		   case 5:
		    //产品总数降序
			$sql_select.=",a.goods_storage+a.goods_salenum as ddd  "; 
			$sql_order= " ddd asc ,".$sql_order;
			break;
		   case 6:
		    //产品总数降序
			$sql_select.=",a.goods_storage+a.goods_salenum as ddd  "; 
			$sql_order= " ddd  desc ,".$sql_order;
			break;	
		   case 7:
		    //产品库存降序
			$sql_order= "a.goods_storage asc ,".$sql_order;
			break;
		   case 8:
		    //产品库存降序
			$sql_order= " a.goods_storage  desc ,".$sql_order;
			break;		
		   case 9:
		    //产品未上架降序
			$sql_table.=",ddt_goods_storehouse s ";
			$sql_select.=",s.num"; 
			$sql_where.=" and a.goods_commonid=s.goods_commonid";
			$sql_order = " s.num asc,".$sql_order;
			break;
		   case 10:
		    //产品未上架降序
			$sql_table.=",ddt_goods_storehouse s ";
			$sql_select.=",s.num"; 
			$sql_where.=" and a.goods_commonid=s.goods_commonid";
			$sql_order = " s.num desc,".$sql_order;
			break;	
			case 11:
		    //产品销售降序
			$sql_order= "a.goods_salenum asc ,".$sql_order;
			break;
		   case 12:
		    //产品销售降序
			$sql_order= " a.goods_salenum  desc ,".$sql_order;
			break;
		   case 13:
		    //产品销售降序
			$sql_order= "a.goods_addtime asc ,".$sql_order;
			break;
		   case 14:
		    //产品销售降序
			$sql_order= " a.goods_addtime  desc ,".$sql_order;
			break;				
		}
		
		
		
		
		//关键词搜索
		$keyword=$data['key'];
		//如果传来keyword
		if($keyword){
			  $sql_where.="  and ( a.goods_name like '%$keyword%'    or   a.position_tags like '%$keyword%'  or    a.store_name like '%$keyword%'  or    a.subtitle like '%$keyword%'   or  a.goods_serial like '%$keyword%' ) "; 
		}
		//分类搜索
		if($array['goodsclass']){
		    $sql_table .= " ,ddt_goods_class_relation b  "; 
			$sql_where.=" and a.goods_commonid=b.goods_commonid  and b.class_id=".$array['goodsclass'];
		}
				
		//上架状态
		if($array['state']){
		  switch($array['state']){
		    case 1:
			//销售中
		    $sql_where.=" and  a.goods_storage >0 ";
			break;
			case 2:
			//已售完
		    $sql_where.="  and a.goods_storage = 0 ";
			break;
			case 3:
			//未上架
			$sql_table.=",ddt_goods_storehouse c ";
		    $sql_where.="  and c.num = a.goods_storage  and a.goods_commonid=c.goods_commonid";
			break;
		  }
		}
		//渠道
		if($array['channel_type']){
		  $sql_where.=" and a.channel_type =".$array['channel_type'];
		}
		if($array['channel_id']){
		  $sql_where.=" and a.channel_id =".$array['channel_id'];
		}
		
		//商品类型
		if($array['is_virtual']>0){
		  $sql_table.=",ddt_goods z ";
		  if($array['is_virtual']==1){
		    $sql_where.=" and  a.goods_commonid=z.goods_commonid and z.is_virtual=0 ";
		  }else{
		    $sql_where.="  and  a.goods_commonid=z.goods_commonid and z.is_virtual=1 ";
		  }	
		}
		
		
		//开始时间
		if($array['begin_time']){
		    $begin_time=strtotime($array['begin_time']);
			$sql_where.=" and a.start_date >=".$begin_time;
		}
		
		//开始时间
		if($array['end_time']){
		    $end_time=strtotime($array['end_time']);
			$sql_where.=" and a.end_date <=".$end_time;
		}
	   $sql=" SELECT ".$sql_select." FROM ".$sql_table." where ".$sql_where.$group." order by ".$sql_order.$limit;
	   return $sql ; 
	}
	
	/**
	 * 获取积分商品列表的sql语句（后台）
	 * @param array $data
	 * @return array
	 */
	public  function getmgoodssql($data){
		//搜索字段
		$array=$data;
		//排序
		$order =isset($data['order'])?$data['order']:""; 
		//分页数据
		$limit =isset($data['limit']) ? ' LIMIT '.$data['limit'] :'' ;
		//分组
        $group = " GROUP BY goods_commonid";
		$where="";
		//构造sql子查询语句
		/*$sql_select = "  a.goods_commonid   ";
		$sql_table = " ddt_goods_class_relation a  "; 
	    $sql_where = " 1=1";
		$sql_group = "  a.goods_commonid   ";
		$sql_order = "  a.goods_commonid  desc ";  */
		$sql_select = "  a.goods_commonid   ";
		$sql_table = " ddt_goods a  "; 
	    $sql_where = " a.goods_state >=0  and a.shop_id=".$data['shop_id'];
		$sql_group = "  a.goods_commonid   ";
		$sql_order = " a.goods_commend desc, (a.goods_salenum+c.false_salenum) desc, a.goods_commonid  desc "; 
		//添加表
		$sql_table.=",ddt_goods_common c ";
		$sql_where.=" and c.goods_commonid=a.goods_commonid ";
		
		
		
		switch($order){
		  case 1:
		    //商品编号降序 
			$sql_order= "c.goods_serial asc ,".$sql_order;
			break;
		  case 2:
		    //商品编号升序
			$sql_order = " c.goods_serial desc,".$sql_order; 
			break;
		  case 3:
		    //商品编号升序
			$sql_order = " c.goods_name asc,".$sql_order; 
			break;	
		  case 4:
		    //商品编号升序
			$sql_order = " c.goods_name desc,".$sql_order; 
			break;	
		  case 5:
		    //商品原价升序
			$sql_order = " c.goods_marketprice asc,".$sql_order; 
			break;	
		  case 6:
		    //商品原价升序
			$sql_order = " c.goods_marketprice desc,".$sql_order; 
			break;	
		  case 7:
		    //商品售价升序
			$sql_order = " a.goods_price asc,".$sql_order; 
			break;	
		  case 8:
		    //商品售价升序
			$sql_order = " a.goods_price desc,".$sql_order; 
			break;	
		  case 9:
		    //商品库存升序
			$sql_order = " a.goods_storage asc,".$sql_order; 
			break;	
		  case 10:
		    //商品库存升序
			$sql_order = " a.goods_storage desc,".$sql_order; 
			break;	
		  case 11:
		    //商品销售升序
			$sql_order = " a.goods_salenum asc,".$sql_order; 
			break;	
		  case 12:
		    //商品销售升序
			$sql_order = " a.goods_salenum desc,".$sql_order; 
			break;	
		  case 13:
		    //商品上架时间升序
			$sql_order = " a.goods_addtime asc,".$sql_order; 
			break;	
		  case 14:
		    //商品上架时间升序
			$sql_order = " a.goods_addtime desc,".$sql_order; 
			break;		
		  case 15:
		    //商品上架时间升序
			$sql_order = " a.goods_state asc,".$sql_order; 
			break;	
		  case 16:
		    //商品上架时间升序
			$sql_order = " a.goods_state desc,".$sql_order; 
			break;			
		 
		}
		//关键词搜索
		$keyword=isset($data['key'])?$data['key']:"";
		//如果传来keyword
		if($keyword){
			  $sql_where.="and (   a.goods_name like '%$keyword%'    or   c.position_tags like '%$keyword%'  or    c.store_name like '%$keyword%'  or    c.subtitle like '%$keyword%'   or  c.goods_serial like '%$keyword%' ) "; 
		}
		//分类搜索
		if(isset($array['goodsclass'])&&$array['goodsclass']){
		    $sql_table.=",ddt_goods_class_relation b ";
			$sql_where.=" and b.goods_commonid=a.goods_commonid and b.class_id=".$array['goodsclass'];
		}
				
		//上架状态
		if(isset($array['state'])&&$array['state']){
		  switch($array['state']){
		    case 1:
			//销售中
		    $sql_where.=" and   a.goods_storage >0  and   a.goods_state=1 ";
			break;
			case 2:
			//已售完
		    $sql_where.="  and a.goods_storage = 0 and   a.goods_state=1 ";
			break;
			case 3:
			//未上架
		    $sql_where.="  and   a.goods_state=0";
			break;
		  }
		}
		 
		//渠道
		if(isset($array['channel_type'])&&$array['channel_type']){
		  $sql_where.="  and c.channel_type =".$array['channel_type'];
		}
		if(isset($array['channel_id'])&&$array['channel_id']){
		  $sql_where.="  and c.channel_id =".$array['channel_id'];
		}
		
		
		//开始时间
		if(isset($array['begin_time'])&&$array['begin_time']){
		    $begin_time=strtotime($array['begin_time']);
			$sql_where.="  and c.start_date >=".$begin_time;
		}
		   
		//开始时间
		if(isset($array['end_time'])&&$array['end_time']){
		    $end_time=strtotime($array['end_time']);
			$sql_where.="  and c.end_date <=".$end_time;
		}
		
		
		//商品类型
		if($array['is_virtual']>0){
		  if($array['is_virtual']==1){
		    $sql_where.="  and a.is_virtual=0 ";
		  }else{
		    $sql_where.="  and  a.is_virtual=1 ";
		  }	
		}
		
		

	   $sql=" SELECT ".$sql_select." FROM ".$sql_table." where ".$sql_where.$group." order by ".$sql_order.$limit;
	   return $sql ; 
	}
	
	
	/**
	 * 获取积分商品列表的sql语句（后台）
	 * @param array $data
	 * @return array
	 */
	public  function getigoodssql($data){
		//搜索字段
		$array=$data;
        //排序
		$order =isset($data['order'])?$data['order']:""; 
		//分页数据
		$limit =isset($data['limit'])?' LIMIT '.$data['limit']:'';
		//分组
        $group = " GROUP BY goods_commonid";
		$where="";
		//构造sql子查询语句
		/*$sql_select = "  a.goods_commonid   ";
		$sql_table = " ddt_goods_class_relation a  "; 
	    $sql_where = " 1=1";
		$sql_group = "  a.goods_commonid   ";
		$sql_order = "  a.goods_commonid  desc ";  */
		$sql_select = "  a.goods_commonid   ";
		$sql_table = " ddt_integral_goods a  "; 
	    $sql_where = " a.goods_state>=0 and a.shop_id=".$data['shop_id'];
		$sql_group = "  a.goods_commonid   ";
		$sql_order = "  a.goods_commonid  desc ";  
		//添加表
		$sql_table.=",ddt_goods_common c ";
		$sql_where.=" and c.goods_commonid=a.goods_commonid ";
		
		
		switch($order){
		 case 1:
		    //商品编号降序 
			$sql_order= "c.goods_serial asc ,".$sql_order;
			break;
		  case 2:
		    //商品编号升序
			$sql_order = " c.goods_serial desc,".$sql_order; 
			break;
		  case 3:
		    //商品编号升序
			$sql_order = " c.goods_name asc,".$sql_order; 
			break;	
		  case 4:
		    //商品编号升序
			$sql_order = " c.goods_name desc,".$sql_order; 
			break;	
		  case 5:
		    //商品原价升序
			$sql_order = " c.goods_marketprice asc,".$sql_order; 
			break;	
		  case 6:
		    //商品原价升序
			$sql_order = " c.goods_marketprice desc,".$sql_order; 
			break;	
		  case 7:
		    //商品售价升序
			$sql_order = " a.goods_integral asc,".$sql_order; 
			break;	
		  case 8:
		    //商品售价升序
			$sql_order = " a.goods_integral desc,".$sql_order; 
			break;	
		  case 9:
		    //商品库存升序
			$sql_order = " a.goods_storage asc,".$sql_order; 
			break;	
		  case 10:
		    //商品库存升序
			$sql_order = " a.goods_storage desc,".$sql_order; 
			break;	
		  case 11:
		    //商品销售升序
			$sql_order = " a.goods_salenum asc,".$sql_order; 
			break;	
		  case 12:
		    //商品销售升序
			$sql_order = " a.goods_salenum desc,".$sql_order; 
			break;	
		  case 13:
		    //商品上架时间升序
			$sql_order = " a.goods_addtime asc,".$sql_order; 
			break;	
		  case 14:
		    //商品上架时间升序
			$sql_order = " a.goods_addtime desc,".$sql_order; 
			break;		
		  case 15:
		    //商品上架时间升序
			$sql_order = " a.goods_state asc,".$sql_order; 
			break;	
		  case 16:
		    //商品上架时间升序
			$sql_order = " a.goods_state desc,".$sql_order; 
			break;			
		}
		
		//关键词搜索
		$keyword=isset($data['key'])?$data['key']:"";
		//如果传来keyword
		if($keyword){
			  $sql_where.="and (  a.goods_name like '%$keyword%'    or   c.position_tags like '%$keyword%'  or    c.store_name like '%$keyword%'  or    c.subtitle like '%$keyword%'   or  c.goods_serial like '%$keyword%' ) "; 
		}
		//分类搜索
		if(isset($array['goodsclass'])&&$array['goodsclass']){
		    $sql_table.=",ddt_goods_class_relation b ";
			$sql_where.=" and b.goods_commonid=a.goods_commonid   and b.class_id=".$array['goodsclass'];
		}
				
		//上架状态
		if(isset($array['state'])&&$array['state']){
		  switch($array['state']){
		    case 1:
			//销售中
		    $sql_where.=" and   a.goods_storage >0  and   a.goods_state=1 ";
			break;
			case 2:
			//已售完
		    $sql_where.="  and a.goods_storage = 0 and   a.goods_state=1 ";
			break;
			case 3:
			//未上架
		    $sql_where.="  and   a.goods_state=0";
			break;
		  }
		}
		
		//渠道
		if(isset($array['channel_type'])&&$array['channel_type']){
		  $sql_where.="  and c.channel_type =".$array['channel_type'];
		}
		if(isset($array['channel_id'])&&$array['channel_id']){
		  $sql_where.="  and c.channel_id =".$array['channel_id'];
		}
		
		//开始时间
		if(isset($array['begin_time'])&&$array['begin_time']){
		    $begin_time=strtotime($array['begin_time']);
			$sql_where.=" and c.start_date >=".$begin_time;
		}
		
		//开始时间
		if(isset($array['end_time'])&&$array['end_time']){
		    $end_time=strtotime($array['end_time']);
			$sql_where.=" and c.end_date <=".$end_time;
		}
		
		
		//商品类型
		if($array['is_virtual']>0){
		  $sql_table.=",ddt_goods z ";
		  if($array['is_virtual']==1){
		    $sql_where.=" and  a.goods_commonid=z.goods_commonid and z.is_virtual=0 ";
		  }else{
		    $sql_where.="  and  a.goods_commonid=z.goods_commonid and z.is_virtual=1 ";
		  }	
		}
		
		

	   $sql=" SELECT ".$sql_select." FROM ".$sql_table." where ".$sql_where.$group." order by ".$sql_order.$limit;
	   return $sql ; 
	}


   
	
	/**
	 * 获取活动商品列表的sql语句（后台）
	 * @param array $data
	 * @return array
	 */
	public  function getpgoodssql($data){
		//搜索字段
		$array=$data;
		//排序
		$order =isset($data['order'])?$data['order']:""; 
		//分页数据
		$limit =isset($data['limit'])? ' LIMIT '.$data['limit']:"";
		//分组
        $group = " GROUP BY goods_commonid";
		$where="";
		//构造sql子查询语句
		/*$sql_select = "  a.goods_commonid   ";
		$sql_table = " ddt_goods_class_relation a  "; 
	    $sql_where = " 1=1";
		$sql_group = "  a.goods_commonid   ";
		$sql_order = "  a.goods_commonid  desc ";  */
		
		$sql_select = "  a.goods_commonid   ";
		$sql_table = " ddt_prize_goods a  "; 
	    $sql_where = " a.goods_state>=0 and a.shop_id=".$data['shop_id'];
		$sql_group = "  a.goods_commonid   ";
		$sql_order = "  a.goods_commonid  desc ";  
		//添加表
		$sql_table.=",ddt_goods_common c ";
		$sql_where.=" and c.goods_commonid=a.goods_commonid ";
		switch($order){
		  case 1:
		    //商品编号降序 
			$sql_order= "c.goods_serial asc ,".$sql_order;
			break;
		  case 2:
		    //商品编号升序
			$sql_order = " c.goods_serial desc,".$sql_order; 
			break;
		  case 3:
		    //商品编号升序
			$sql_order = " c.goods_name asc,".$sql_order; 
			break;	
		  case 4:
		    //商品编号升序
			$sql_order = " c.goods_name desc,".$sql_order; 
			break;	
		  case 5:
		    //商品原价升序
			$sql_order = " c.goods_marketprice asc,".$sql_order; 
			break;	
		  case 6:
		    //商品原价升序
			$sql_order = " c.goods_marketprice desc,".$sql_order; 
			break;	
		  case 7:
		    //商品售价升序
			$sql_order = " a.goods_price asc,".$sql_order; 
			break;	
		  case 8:
		    //商品售价升序
			$sql_order = " a.goods_price desc,".$sql_order; 
			break;	
		  case 9:
		    //商品库存升序
			$sql_order = " a.goods_storage asc,".$sql_order; 
			break;	
		  case 10:
		    //商品库存升序
			$sql_order = " a.goods_storage desc,".$sql_order; 
			break;	
		  case 11:
		    //商品销售升序
			$sql_order = " a.goods_salenum asc,".$sql_order; 
			break;	
		  case 12:
		    //商品销售升序
			$sql_order = " a.goods_salenum desc,".$sql_order; 
			break;	
		  case 13:
		    //商品上架时间升序
			$sql_order = " a.goods_addtime asc,".$sql_order; 
			break;	
		  case 14:
		    //商品上架时间升序
			$sql_order = " a.goods_addtime desc,".$sql_order; 
			break;		
		  case 15:
		    //商品上架时间升序
			$sql_order = " a.goods_state asc,".$sql_order; 
			break;	
		  case 16:
		    //商品上架时间升序
			$sql_order = " a.goods_state desc,".$sql_order; 
			break;			
		}
		//关键词搜索
		$keyword=isset($data['key'])?$data['key']:"";
		//如果传来keyword
		if($keyword){
			  $sql_where.="and (  a.goods_name like '%$keyword%'    or   c.position_tags like '%$keyword%'  or    c.store_name like '%$keyword%'  or    c.subtitle like '%$keyword%'   or  c.goods_serial like '%$keyword%') "; 
		}
		//分类搜索
		if(isset($array['goodsclass'])){
		    $sql_table.=",ddt_goods_class_relation b ";
			$sql_where.=" and b.goods_commonid=a.goods_commonid   and b.class_id=".$array['goodsclass'];
		}
				
		//上架状态
		if(isset($array['state'])){
		  switch($array['state']){
		    case 1:
			//销售中
		    $sql_where.=" and   a.goods_storage >0  and   a.goods_state=1 ";
			break;
			case 2:
			//已售完
		    $sql_where.="  and a.goods_storage = 0 and   a.goods_state=1 ";
			break;
			case 3:
			//未上架
		    $sql_where.="  and   a.goods_state=0";
			break;
		  }
		}
		
		//渠道
		if(isset($array['channel_type'])&&$array['channel_type']){
		  $sql_where.="  and c.channel_type =".$array['channel_type'];
		}
		if(isset($array['channel_id'])&&$array['channel_id']){
		  $sql_where.="  and c.channel_id =".$array['channel_id'];
		}
		
		//开始时间
		if(isset($array['begin_time'])&&$array['begin_time']){
		    $begin_time=strtotime($array['begin_time']);
			$sql_where.=" and c.start_date >=".$begin_time;
		}
		
		//开始时间
		if(isset($array['end_time'])&&$array['end_time']){
		    $end_time=strtotime($array['end_time']);
			$sql_where.=" and c.end_date <=".$end_time;
		}
		
		
		//商品类型
		if($array['is_virtual']>0){
		  $sql_table.=",ddt_goods z ";
		  if($array['is_virtual']==1){
		    $sql_where.=" and  a.goods_commonid=z.goods_commonid and z.is_virtual=0 ";
		  }else{
		    $sql_where.="  and  a.goods_commonid=z.goods_commonid and z.is_virtual=1 ";
		  }	
		}
	   $sql=" SELECT ".$sql_select." FROM ".$sql_table." where ".$sql_where.$group." order by ".$sql_order.$limit;
	   return $sql ; 
	}


     //通过产品编号获取商品
	 /**
	 *  param  serial   商品编号
	 *  param  type  商品类型  1:现金商品  2：积分商品   3：抽奖商品
	 *  return  array  商品信息
	 */
	 
	 
	 function getGoodsBySerial($serial,$type=1){ 
	    $goodscommon=D("goodscommon")->where("goods_serial = '$serial'")->find(); 
		$goods_commonid= $goodscommon['goods_commonid'];
		$goodsinfo=array();
		if($goodscommon){
		  switch($type){
		    case 1:
			$goodsinfo=D("goods")->where("goods_commonid = $goods_commonid")->field('goods_id,goods_commonid,goods_name,shop_id,goods_price,goods_marketprice,goods_storage,goods_state')->find();  
			break;
			case 2:
			$goodsinfo=D("integralgoods")->where("goods_commonid = $goods_commonid")->field('goods_id,goods_commonid,goods_name,shop_id,goods_price,goods_marketprice,goods_storage,goods_state')->find();  
			break;
			case 3:
			$goodsinfo=D("prizegoods")->where("goods_commonid = $goods_commonid")->field('goods_id,goods_commonid,goods_name,shop_id,goods_price,goods_marketprice,goods_storage,goods_state')->find();  
			break;
		  }	
		}else{
		  return 0;
		}
		return  $goodsinfo;
	 }
	 
	 
	 //获取产品列表页面信息(产品列表页面使用)
	 function  getgooodlist($data,$type=1){
	    $info=$data;
		//调取代理信息
        $shop_info = get_shop_proxy();
        $shop_id=$shop_info['shop_id']?$shop_info['shop_id']:1;
		$gc_id=$data['gc_id'];

		if($gc_id > 1){
			$goodslist=   M()->query("select a.* from ddt_goods as a ,ddt_goods_class_relation as b where a.goods_commonid=b.goods_commonid   and a.shop_id= $shop_id  and a.goods_state>0   and b.class_id=".$gc_id);
		}else{
		  $goodslist=   M()->query("select * from ddt_goods where shop_id= $shop_id  and goods_state > 0 ");
		}
		foreach($goodslist as $key=>$val){
			$goodcommon_info=D('Admin/goodscommon')->where('goods_commonid='.$val['goods_commonid'])->find();
			$goodslist[$key]['position_tags']=$goodcommon_info['position_tags'];
			$goodslist[$key]['subtitle']=$goodcommon_info['subtitle'];
		}
		return $goodslist;
	 }
	 
	 
	 //商品统计
	public function getGoodsState($gettype=0,$goodstype=1){
	   $goods_num=0;
	  switch($goodstype){
	    case 1://上架中的商品      
		  $goods_num=D('Admin/goods')->getGoodsState($gettype);
		break;
		case 2://已售罄的商品
		  $goods_num=D('Admin/integralgoods')->getGoodsState($gettype);
		break;
		case 3://未上架的商品
		  $goods_num=D('Admin/prizegoods')->getGoodsState($gettype);
		break;
	  }
	  return $goods_num;
	}

    
	
	
	
	
	
}