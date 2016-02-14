<?php
/**
 * Created by ddt.
 * User: 张辉
 * Description:后台产品管理类，提供后台产品管理所需的操作方法
 */
 
namespace Admin\Controller;
use BizService\UserService;
use Think\Controller;
use Think\Model;
use BizService\GoodsService;
use BizService\GoodsstoreService;
use BizService\ExtendService;
use BizService\Geohash;
use BizService\ShopService;
use BizService\ExeclService;
use BizService\PeriodService;
use BizService\OrderService;

class GoodsController extends BaseController {
	public function _initialize(){
		parent::_initialize();
	}
    public function index(){
		$this->goods_common_list();
    }
	
/**
 *产品公共列表
 */ 
	public function goods_common_list(){
	    $type=I('type');
		$gc_id=I('type');
	    //获取商城信息
		$shop_info=get_shop_proxy();
		$shop_id=$shop_info['shop_id']?$shop_info['shop_id']:0;
		if($shop_id==0){
		  exit('请先登录！');
		}
		$goodsstore = new GoodsstoreService();
		$goods = new GoodsService();
		$arr=array();
		$arr['shop_id']=$shop_id;	
		//$arr['limit']=" 0,20 ";
		//单独查询分类
		if(I('gc_id')){
		  $arr['goodsclass']=I('gc_id');
		}
		if(I('order')){
		$arr['order']=I('order');
		}
		if(IS_POST||IS_GET){
		  $arr['goodsclass']=I('goodsclass');
		  $arr['state']=I('goodsstatus');
		  $arr['end_time']=I('end_time');
		  $arr['begin_time']=I('begin_time');
		  $arr['key']=I('keywords');
		  //渠道
		  $arr['channel_type']=I('channel_type');
		  $arr['channel_id']=I('channel_id');
		  if(I('gc_id')){
		  $arr['goodsclass']=I('gc_id');
	  	  }
		  //商品类型
		  $arr['is_virtual']=I('is_virtual');
		  $this->assign('is_virtual',I('is_virtual'));	
		  $this->assign('class',I('goodsclass'));	
		  $this->assign('goodsstatus',I('goodsstatus'));	
		  $this->assign('keywords',I('keywords'));	
		  $this->assign('begin_time',I('begin_time'));	
		  $this->assign('end_time',I('end_time'));	
		  $goodslist=$goods->goods_common_list($arr);
		}else{
	  	  $goodslist=$goods->goods_common_list($arr);
		}

		//加工数据
		foreach($goodslist['list'] as $key=>$val){
		  //商品总量
		  $goodslist['list'][$key]['goods_num']=$val['goods_salenum']+$val['goods_storage'];
		  //现金上架库存
		 $goodslist['list'][$key]['goods_storage_m'] = D("goods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_storage');
		 $goodslist['list'][$key]['goods_salenum_m'] = D("goods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_salenum');
		  $goodslist['list'][$key]['goods_divided'] = D("goods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_divided');
		  //积分上架库存
		 $goodslist['list'][$key]['goods_storage_i'] =D("integralgoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_storage');
		 $goodslist['list'][$key]['goods_salenum_i'] =D("integralgoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_salenum');
		  //活动上架库存
		 $goodslist['list'][$key]['goods_storage_p'] =D("prizegoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_storage');
		  $goodslist['list'][$key]['goods_salenum_p'] =D("prizegoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_salenum');
		  //未上架库存
		  $goodslist['list'][$key]['goods_storagein'] = $val['goods_storage']-$goodslist['list'][$key]['goods_storage_m']-$goodslist['list'][$key]['goods_storage_i']- $goodslist['list'][$key]['goods_storage_p'] ;
		  $goodslist['list'][$key]['goods_addtime'] = date('Y-m-d  h:i:s',$val['goods_addtime']);
		  
		  $is_virtual= D("goods")->where('goods_commonid='.$val['goods_commonid'])->getField('is_virtual');
		  $goodslist['list'][$key]['position_tags']=$is_virtual?$val['position_tags']:$val['post_tags'];
		}
		//获取分类信息
		$goodsclass=D("goodsclass")->where('shop_id='.$shop_id)->select();
		//获取渠道列表
		$ExtendService = new ExtendService();
		$channelList=$ExtendService->channelList();
		if(I('channel_type')){
		   //获取渠道人员列表
		   $memberList=$ExtendService->memberList(I('channel_type'));
		   $this->assign('memberList',$memberList);	
		   $this->assign('channel_type',I('channel_type'));
		   $this->assign('channel_id',I('channel_id'));  
		}else{
		   $this->assign('memberList',array());	
		   $this->assign('channel_type',"");
		   $this->assign('channel_id',"");  
		}
		
		
		//
		
		
		$this->assign('channelList',$channelList);	
		$this->assign('goodslist',$goodslist);	
		$this->assign('goodsclass',$goodsclass);	
		$this->assign('type',$type);	
		$this->assign('gc_id',$gc_id);	
		$this->display("list");
    }
	
	
	
/**
 *现金产品列表
 */ 	
	public function goods_list(){
	    $shop_info=get_shop_proxy();
		$shop_id=$shop_info['shop_id']?$shop_info['shop_id']:0;
		$goodsstore = new GoodsstoreService();
		$goods = new GoodsService();
		$arr=array();
		$arr['shop_id']=$shop_id;	
		//排序
		if(I('order')){
		  $arr['order']=I('order');
		}
			
		if(IS_POST||IS_GET){
		  if(I('goodsclass')){
		    $arr['goodsclass']=I('goodsclass');
		  }	
		  if(I('goodsstatus')){
		    $arr['state']=I('goodsstatus');
		  }
		  if(I('end_time')){
		    $arr['end_time']=I('end_time');
		  }
		  if(I('begin_time')){
		    $arr['begin_time']=I('begin_time');
		  }
		  if(I('keywords')){
		    $arr['key']=I('keywords');
		  }
		  
		  if(I('gc_id')){
		  $arr['goodsclass']=I('gc_id');
		}
		  
		  //渠道
		  if(I('channel_type')){
		  $arr['channel_type']=I('channel_type');
		  }
		  if(I('channel_id')){
		    $arr['channel_id']=I('channel_id');
		  }
		  //商品类型
		  $arr['is_virtual']=I('is_virtual');
		  $this->assign('is_virtual',I('is_virtual'));	
		  
		  
		  $this->assign('class',I('goodsclass'));	
		  $this->assign('goodsstatus',I('goodsstatus'));	
		  $this->assign('keywords',I('keywords'));	
		  $this->assign('begin_time',I('begin_time'));	
		  $this->assign('end_time',I('end_time'));	
		  $goodslist=$goods->goods_list($arr);
		}else{
	  	  $goodslist=$goods->goods_list($arr);
		}
		//加工数据
		foreach($goodslist['list'] as $key=>$val){
		  //现金上架库存
		 $goodslist['list'][$key]['goods_storage_m'] = D("goods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_storage');
		 $goodslist['list'][$key]['goods_salenum_m'] = D("goods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_salenum');
		 $goodslist['list'][$key]['goods_edittime'] = date('Y-m-d  h:i:s',D("goods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_edittime'));
		 if($goodslist['list'][$key]['goods_storage_m']>0 && $goodslist['list'][$key]['goods_state']==1){
		    $goodslist['list'][$key]['goods_status'] ="出售中"; 
		 }else{
		    if($goodslist['list'][$key]['goods_state']==0){
		     $goodslist['list'][$key]['goods_status'] ="等待上架"; 
		    }else{
			 $goodslist['list'][$key]['goods_status'] ="已售完"; 
			}	 
		 }
		} 
		//分类
		$goodsclass=D("goodsclass")->where('shop_id='.$shop_id)->select();
		//获取渠道列表
		$ExtendService = new ExtendService();
		$channelList=$ExtendService->channelList();
		if(I('channel_type')){
		   //获取渠道人员列表
		   $memberList=$ExtendService->memberList(I('channel_type'));
		   $this->assign('memberList',$memberList);	
		   $this->assign('channel_type',I('channel_type'));
		   $this->assign('channel_id',I('channel_id'));  
		}else{
		   $this->assign('memberList',array());	
		   $this->assign('channel_type',"");
		   $this->assign('channel_id',"");  
		}
		
		
		
		
		
		$this->assign('channelList',$channelList);	
		$this->assign('goodslist',$goodslist);	
		$this->assign('goodsclass',$goodsclass);	
		$this->display("goods_list");
    }
	
/**
 *积分产品列表
 */	
	public function goods_integral_list(){
	    $shop_info=get_shop_proxy();
		$shop_id=$shop_info['shop_id']?$shop_info['shop_id']:0;
		$goodsstore = new GoodsstoreService();
		$goods = new GoodsService();
		$arr=array();
		$arr['shop_id']=$shop_id;	
		//排序
		if(isset($_GET['order'])&&$_GET['order']){
		$arr['order']=$_GET['order'];
		}
		if(IS_POST){
		  $arr['goodsclass']=$_POST['goodsclass'];
		  $arr['state']=$_POST['goodsstatus'];
		  $arr['end_time']=$_POST['end_time'];
		  $arr['begin_time']=$_POST['begin_time'];
		  $arr['key']=$_POST['keywords'];
		  //渠道  
		  $arr['channel_type']=$_POST['channel_type'];
		  $arr['channel_id']=$_POST['channel_id'];
		  $this->assign('class',$_POST['goodsclass']);	
		  $this->assign('goodsstatus',$_POST['goodsstatus']);	
		  $this->assign('keywords',$_POST['keywords']);	
		  $this->assign('begin_time',$_POST['begin_time']);	
		  $this->assign('end_time',$_POST['end_time']);	
		  //商品类型
		  $arr['is_virtual']=I('is_virtual');
		  $this->assign('is_virtual',I('is_virtual'));	
		  $goodslist=$goods->igoods_list($arr);
		}else{
		  $this->assign('goodsstatus',"");
		  $this->assign('begin_time',"");	
		  $this->assign('end_time',"");	
		  $this->assign('keywords',"");	
		  $this->assign('class',"");	
	  	  $goodslist=$goods->igoods_list($arr);
		}
		//加工数据
		foreach($goodslist['list'] as $key=>$val){
		  //现金上架库存
		 $goodslist['list'][$key]['goods_storage_i'] =D("integralgoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_storage');
		 $goodslist['list'][$key]['goods_salenum_i'] =D("integralgoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_salenum');
		 $goodslist['list'][$key]['goods_addtime'] = date('Y-m-d  h:i:s',D("integralgoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_edittime'));
		 if($goodslist['list'][$key]['goods_storage_i']>0 && $goodslist['list'][$key]['goods_state']==1){
		    $goodslist['list'][$key]['goods_status'] ="出售中"; 
		 }else{
		    if($goodslist['list'][$key]['goods_state']==0){
		     $goodslist['list'][$key]['goods_status'] ="等待上架"; 
		    }else{
			 $goodslist['list'][$key]['goods_status'] ="已售完"; 
			}	 
		 }
		} 
		//分类
		$goodsclass=D("goodsclass")->where('shop_id='.$shop_id)->select();
		//获取渠道列表
		$ExtendService = new ExtendService();
		$channelList=$ExtendService->channelList();
		if(I('channel_type')){
		   //获取渠道人员列表
		   $memberList=$ExtendService->memberList(I('channel_type'));
		   $this->assign('memberList',$memberList);	
		   $this->assign('channel_type',I('channel_type'));
		   $this->assign('channel_id',I('channel_id'));  
		}else{
		   $this->assign('memberList',array());	
		   $this->assign('channel_type',"");
		   $this->assign('channel_id',"");  
		}
		
		
		
		$this->assign('channelList',$channelList);	
		$this->assign('goodslist',$goodslist);	
		$this->assign('goodsclass',$goodsclass);	
		$this->display("goods_integral_list");
    }
	
/**
 *活动产品列表
 */		
	public function goods_prize_list(){
	    $shop_info=get_shop_proxy();
		$shop_id=$shop_info['shop_id']?$shop_info['shop_id']:0;
		$goodsstore = new GoodsstoreService();
		$goods = new GoodsService();
		$arr=array();
		$arr['shop_id']=$shop_id;	
		//排序
		if(isset($_GET['order'])&&$_GET['order']){
		$arr['order']=$_GET['order'];
		}
		if(IS_POST){
		  if($_POST['goodsclass']){
		  $arr['goodsclass']=$_POST['goodsclass'];
		  }
		  $arr['state']=$_POST['goodsstatus'];
		  $arr['end_time']=$_POST['end_time'];
		  $arr['begin_time']=$_POST['begin_time'];
		  $arr['key']=$_POST['keywords'];
		  //渠道  
		  $arr['channel_type']=$_POST['channel_type'];
		  $arr['channel_id']=$_POST['channel_id'];
		  $this->assign('class',$_POST['goodsclass']);	
		  $this->assign('goodsstatus',$_POST['goodsstatus']);	
		  $this->assign('keywords',$_POST['keywords']);	
		  $this->assign('begin_time',$_POST['begin_time']);	
		  $this->assign('end_time',$_POST['end_time']);	
		  //商品类型
		  $arr['is_virtual']=I('is_virtual');
		  $this->assign('is_virtual',I('is_virtual'));	
		  $goodslist=$goods->pgoods_list($arr);
		}else{
		  $this->assign('goodsstatus',"");
		  $this->assign('begin_time',"");	
		  $this->assign('end_time',"");	
		  $this->assign('keywords',"");	
		  $this->assign('class',"");	
	  	  $goodslist=$goods->pgoods_list($arr);
		}
		//加工数据
		foreach($goodslist['list'] as $key=>$val){
		  //现金上架库存
		 $goodslist['list'][$key]['goods_storage_p'] =D("prizegoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_storage');
		  $goodslist['list'][$key]['goods_salenum_p'] =D("prizegoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_salenum');
		 $goodslist['list'][$key]['goods_addtime'] = date('Y-m-d  h:i:s',D("prizegoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_edittime'));
		 if($val['goods_storage']>0 && $val['goods_state']==1){
		    $goodslist['list'][$key]['goods_status'] ="出售中"; 
		 }else{
		    if($val['goods_state']==0){
		     $goodslist['list'][$key]['goods_status'] ="等待上架"; 
		    }else{
			 $goodslist['list'][$key]['goods_status'] ="已售完"; 
			}	 
		 }
		} 
		//分类
		$goodsclass=D("goodsclass")->where('shop_id='.$shop_id)->select();
		//获取渠道列表
		$ExtendService = new ExtendService();
		$channelList=$ExtendService->channelList();
		if(I('channel_type')){
		   //获取渠道人员列表
		   $memberList=$ExtendService->memberList(I('channel_type'));
		   $this->assign('memberList',$memberList);	
		   $this->assign('channel_type',I('channel_type'));
		   $this->assign('channel_id',I('channel_id'));  
		}else{
		   $this->assign('memberList',array());	
		   $this->assign('channel_type',"");
		   $this->assign('channel_id',"");  
		}
		$this->assign('channelList',$channelList);	
		$this->assign('goodslist',$goodslist);	
		$this->assign('goodsclass',$goodsclass);
		$this->display("goods_prize_list");
    }
	
/**
 *添加产品
 */	
	public  function goods_common_add(){
	  //获取商城id
	   $shop_info=get_shop_proxy();
	   $shop_id=$shop_info['shop_id'];
	  if(IS_POST){
	   //获取参数
	   $data=array();
	   $data=I('post.');
	   $data['shop_id']=$shop_id;
	   //数据验证
	       if($data['shop_id']==""){
		     $this->error ("商城id不能为空！");
		   }
		   if($data['goods_serial']=="" || strlen($data['goods_serial'])>32){
		     $this->error ("商品编号不能为空！");
		   }else{
			   if(strlen($data['goods_serial'])>32){
				   $this->error ("商品编号长度不能超过32个字符！");
			   }
		     $g=M("goods_common")->where("goods_serial='".$data['goods_serial']."'")->find();
			 if($g){
			   $this->error ("产品编号重复，请修改！");
			 }
		   }
		   //商家信息是否正确
		   if($data['store_name']==""){
		     $this->error ("商家名称不能为空！");
		   }else{
		     $store_info=get_merchant_info($data['store_id']);
			 if($store_info['merchant_name']!=$data['store_name']){
				 $shop_info=get_shop_proxy();
			   $shoplist=get_merchant_by_proxy($shop_info['proxy_id'],array('name'=>$data['store_name']));
			   if($shoplist){
			     $data['store_id']=$shoplist[0]['merchant_id'];
				 $data['province']=$shoplist[0]['province'];
				 $data['city']=$shoplist[0]['city'];
				 $data['district']=$shoplist[0]['district'];
			   }else{
			     $this->error ("商家信息有误！");
			   }	 
			 }
		   }
		   if($data['store_id']==""){
		     $this->error ("商家id不能为空！");
		   }else{
		     $store_info=get_merchant_info($data['store_id']);
			 if($store_info=="null"){
			   $this->error ("商家信息有误！");
			 }
		   }
		   if($data['goods_name']==""){
		     $this->error ("主标题不能为空！");
		   }
		   if($data['goods_storage']==""||$data['goods_storage']<=0){
		     $this->error ("商品库存不能为空！");
		   }
		   
		   if($data['goods_marketprice']==""){
		     $this->error ("市场价不能为空！");
		   }
		   
		   if($data['goods_price']==""){
		     $this->error ("销售价不能为空！");
		   }
			/*if($data['goods_image']==""){
				 $this->error ("商品主图不能为空！");
			 }
            */ //判断有效期
		   if($data['start_date']==""){
		     $this->error ("有效期不能为空！");
		   }
		   if($data['end_date']==""){
		     $this->error ("有效期不能为空！");
		   }
		   if($data['start_date']>$data['end_date']){
		    $this->error ("有效期开始时间不得晚于结束时间！");
		   }
		   //轮播图
		   /*$n=count($data['goods_lunbo']);
		   if($n==0){
		     $this->error ("至少要上传一张轮播图！");
		   }*/
		   //判断渠道不为空
		   if($data['channel_id']==""){
		     $this->error ("渠道人员不能为空！");
		   }
	       $goods = new GoodsService();
	       $goods_id=$goods->addGoods($data);
		   if($goods_id){
			if($data['sub']==2){
			 $this->success ( '入库成功！', U ( 'goods_common_list' ) );
			}else{
			 $this->success ( '入库成功！下一步进行上架操作！', U ( 'admin/goods/goods_common_state/goods_id/'.$goods_id ) ); 
			} 
		   }else{
			 $this->error ("添加失败");
		   }
	  }else{
	    //商城id 
		$shop_info=get_shop_proxy();
		//查看是否为复制，如果是复制，就调取信息
		$goods_commonid=I('goods_commonid',0);
		//调取渠道类型
		$ExtendService = new ExtendService();
		$channelList=$ExtendService->channelList();
		$class_data=D('goodsclass')->getTree(0,true,$shop_id);
		$this->assign('channelList',$channelList);
        $this->assign('classdata',$class_data);	
		$this->assign('shop_info',$shop_info);	
		if($goods_commonid>0){
		  //调取此商品分类
		  $classrelation_data=D('goodsclassrelation')->getclass($goods_commonid);
		  //调取产品基本信息
		  $goods_data=D('goodscommon')->info($goods_commonid);  
		  if($goods_data){
		  	$goods_data['usetime']=string2array($goods_data['usetime']);
			$goods_data['rules']=string2array($goods_data['rules']);
			$goods_data['addcontent']=string2array($goods_data['addconent']);
			$goods_data['start_date']=$goods_data['start_date']?date('Y-m-d', $goods_data['start_date']):"";
			$goods_data['end_date']=$goods_data['start_date']?date('Y-m-d', $goods_data['end_date']):"";
		  //调取渠道类型
		    $ExtendService = new ExtendService();
		    $channelList=$ExtendService->channelList();
		  	$memberList=$ExtendService->memberList($goods_data['channel_type']);
		  }
		  
		  //调取运费设置信息
		  $Goodssending=D('Goodssending')->getinfobycommonid($goods_commonid);  
		   //调取现金商品
		   $goods=D('goods')->info($goods_commonid);  
		   if($goods['take_type']==2){
		   $goods['take_num1']=$goods['take_num'];
		   $goods['take_num']=0;
		   }else{
		   $goods['take_num1']=0;
		   }
		  //调取积分商品
		   $lgoods_data=D('integralgoods')->info($goods_commonid);  
		  //调取活动商品
		   $pgoods_data=D('prizegoods')->info($goods_commonid);  
		  //调取商品轮播图
		   $goods_lunbo=D('goodsimages')->info($goods_commonid);  
		  
		  //人员列表
		  // var_dump(($goods_data));exit;
		   $this->assign('memberList',$memberList);
		   $this->assign('channelList',$channelList);
		   $this->assign('shop_info',$shop_info);	
		   $this->assign('goods_id',$goods_id);	
		   $this->assign('classdata',$class_data);
		   $this->assign('classrelation_data',$classrelation_data);
		   $this->assign('goods_data',$goods_data);
		   $this->assign('goods',$goods);
		   $this->assign('goods_lunbo',$goods_lunbo);	
		   $this->assign('goods_limit',$goods['virtual_limit']);	
		   $this->assign('goods_integral',$lgoods_data['goods_integral']);
		   $this->assign('prize_goods_price',$pgoods_data['goods_price']);
		   $this->assign('Goodssending',$Goodssending);	
           $this->display("add_copy");  
		}else{
           $this->display("add");
		}
	  }  
	}
/**
 *修改产品基本信息
 */	
	public function goods_common_edit_base(){
	     $shop_proxy=get_shop_proxy();
	     $proxyid=$shop_proxy['proxy_id']; 
		if(IS_POST){
		   $data=array();
		   $data=I('post.');
		   //数据验证
		   if($data['goods_serial']==""){
		     $this->error ("商品编号不能为空！");
		   }else{
			   if(strlen($data['goods_serial'])>32){
				   $this->error ("商品编号长度不能超过32个字符！");
			   }
			   $g=M("goods_common")->where("goods_serial='".$data['goods_serial']."' and goods_commonid!=".intval($data['goods_commonid'])."")->find();
			   if($g){
				   $this->error ("产品编号重复，请修改！");
			   }
		   }
		   //商家信息是否正确
		   if($data['store_name']==""){
		     $this->error ("商家名称不能为空！");
		   }else{
		     $store_info=get_merchant_info($data['store_id']);
			 if($store_info['merchant_name']!=$data['store_name']){
			   $shoplist=get_merchant_by_proxy($proxyid,array('name'=>$data['store_name']));
			   if($shoplist){
			     $data['store_id']=$shoplist[0]['merchant_id'];
				 $data['province']=$shoplist[0]['province'];
				 $data['city']=$shoplist[0]['city'];
				 $data['district']=$shoplist[0]['district'];
			   }else{
			     $this->error ("商家信息有误！");
			   }	 
			 }
		   }
		   
		   if($data['store_id']==""){
		     $this->error ("商家id不能为空！");
		   }else{
		     $store_info=get_merchant_info($data['store_id']);
			 if($store_info=="null"){
			   $this->error ("商家信息有误！");
			 }
		   }
		   if($data['goods_name']==""){
		     $this->error ("主标题不能为空！");
		   }
		   if($data['goods_storage']==""||$data['goods_storage']<=0){
		     $this->error ("商品库存不能为空！");
		   }
		   
		   if($data['goods_marketprice']==""){
		     $this->error ("市场价不能为空！");
		   }
		   
		   if($data['goods_price']==""){
		     $this->error ("销售价不能为空！");
		   }
		   
		  /* if($data['goods_image']==""){
		     $this->error ("商品主图不能为空！");
		   }*/
		   
		   //判断有效期
		   /*if($data['start_date']==""){
		     $this->error ("有效期不能为空！");
		   }
		   if($data['end_date']==""){
		     $this->error ("有效期不能为空！");
		   }*/
		   if($data['start_date']>$data['end_date']){
		    $this->error ("有效期开始时间不得晚于结束时间！");
		   }
		   
		    //轮播图
		  /* $n=count($data['goods_lunbo']);
		   if($n==0){
		     $this->error ("至少要上传一张轮播图！");
		   }*/
		   
		   
		   
		   //判断渠道不为空
		   if($data['channel_id']==""){
		     $this->error ("渠道人员不能为空！");
		   }

		   //修改基本信息
		   if($data['sub']==1){
			   $goods = new GoodsService();
			   $flag=$goods->editGoods($data);
			   
			   if($flag==1){
				  $this->success ( '操作成功！下一步进行上架操作！', U ( 'admin/goods/goods_common_state/goods_id/'.$data['goods_commonid'] ) ); 
			   }else{
				 $this->error ("操作失败");
			   }
		   }elseif($data['sub']==2){
		   //商品下架
			   $goods = new GoodsService();
			   $flag=$goods->editGoods($data);
			   $flag=$goods->statedownGoods($data['goods_commonid']);
			   if($flag==1){
				  $this->success ( '产品下架成功！', U ( 'admin/goods/goods_common_list' ) ); 
			   }else{
				 $this->error ("操作失败");
			   }
		   }
	    }else{
		  //商城id 
		  $shop_info=get_shop_proxy();
		  //商品id 
		  $goods_id=I('goods_id'); 
		  //调取分类信息
		  $class_data=D('goodsclass')->getTree(0,true,$shop_info['shop_id']);
		  //调取此商品分类
		  $classrelation_data=D('goodsclassrelation')->getclass($goods_id);
		  //调取产品基本信息
		  $goods_data=D('goodscommon')->info($goods_id);  
		  if($goods_data){
		  	$goods_data['usetime']=string2array($goods_data['usetime']);
			$goods_data['rules']=string2array($goods_data['rules']);
			$goods_data['addcontent']=string2array($goods_data['addconent']);
			$goods_data['start_date']=$goods_data['start_date']?date('Y-m-d', $goods_data['start_date']):"";
			$goods_data['end_date']=$goods_data['start_date']?date('Y-m-d', $goods_data['end_date']):"";
		  //调取渠道类型
		    $ExtendService = new ExtendService();
		    $channelList=$ExtendService->channelList();
		  	$memberList=$ExtendService->memberList($goods_data['channel_type']);
		  }
		  
		  //调取运费设置信息
		  $Goodssending=D('Goodssending')->getinfobycommonid($goods_id);  
		   //调取现金商品
		   $goods=D('goods')->info($goods_id);  
		   if($goods['take_type']==2){
		   $goods['take_num1']=$goods['take_num'];
		   $goods['take_num']=0;
		   }else{
		   $goods['take_num1']=0;
		   }
		  //调取积分商品
		   $lgoods_data=D('integralgoods')->info($goods_id);  
		  //调取活动商品
		   $pgoods_data=D('prizegoods')->info($goods_id);  
		  //调取商品轮播图
		   $goods_lunbo=D('goodsimages')->info($goods_id);  
		  
		  //人员列表
		  // var_dump(($goods_data));exit;
		   $this->assign('memberList',$memberList);
		   $this->assign('channelList',$channelList);
		   $this->assign('shop_info',$shop_info);	
		   $this->assign('goods_id',$goods_id);	
		   $this->assign('classdata',$class_data);
		   $this->assign('classrelation_data',$classrelation_data);
		   $this->assign('goods_data',$goods_data);
		   $this->assign('goods',$goods);
		   $this->assign('goods_lunbo',$goods_lunbo);	
		   $this->assign('goods_limit',$goods['virtual_limit']);	
		   $this->assign('goods_integral',$lgoods_data['goods_integral']);
		   $this->assign('prize_goods_price',$pgoods_data['goods_price']);
		   $this->assign('Goodssending',$Goodssending);	
           $this->display("editbase");  
	    }
    }
/**
 *商品删除
 */	
	public function goods_common_del(){
		if(IS_GET){
		   $goods_id=I('goods_id');
		   $goods = new GoodsService();
	       $flag=$goods->delGoods($goods_id);
		   if($flag==1){
		   		$this->success ( '删除成功！', U ( 'goods_common_list' ) );
		   }else{
	     		$this->error ( '此商品有售卖记录，不可删除！', U ( 'goods_common_list' ) );
		   }
	    }
    }
	
/**
 *商品上架
 */
	public function goods_common_state(){
		if(IS_POST){
	       $goods_commonid=I('goods_commonid');
		   $data=array();
		   $data=I('post.');
		 //保存上架信息
		   $goods = new GoodsService();
	       $flag=$goods->stateGoods($data,$goods_commonid); 
		 //修改seo信息
		   /*$seodata=array();
		   $seodata['keywords']=$data['keywords'];
		   $seodata['description']=$data['description'];
		   D('goodscommon')->editGoodsbycommonid($goods_commonid,$seodata);*/
		   //M('goods_common')->where('goods_commonid='.$goods_commonid)->data($seodata)->save();
		   //M('goods_common')->where('goods_commonid='.$goods_commonid)->setField('keywords',$data['keywords']);
           $this->success ( '操作成功！');     
	    }else{
		  $goods_id=I('goods_id');
		  //调取产品基本信息
		  $goodscommon_data=D('goodscommon')->info($goods_id);  
		  //调取产品未分配库存
			$goods_store_service = new GoodsstoreService();
		  $goodsstore=$goods_store_service->storeinfo($goods_id);
		  //现金商品信息
		     $goods_data=D('goods')->info($goods_id); 
		     //是否有定时上架
			 /*if($goods_data['goods_state']==0){
			   $dotime= D('cron')->info($goods_commonid,1,'exetime');
			   $goods_data['dotime']=date("Y-m-d",$dotime['exetime']);
			 }*/
		  
		  //积分商品
		     $integralgoods_data=D('integralgoods')->info($goods_id); 
		     //是否有定时上架
			 /*if($integralgoods_data['goods_state']==0){
			   $dotime= D('cron')->info($goods_commonid,2,'exetime');
			   $integralgoods_data['dotime']=date("Y-m-d",$dotime['exetime']);
			 }*/
		  //活动商品
		     $prizegoods_data=D('prizegoods')->info($goods_id); 
		     //是否有定时上架
			 /*if($prizegoods_data['goods_state']==0){
			   $dotime= D('cron')->info($goods_commonid,3,'exetime');
			   $prizegoods_data['dotime']=date("Y-m-d",$dotime['exetime']);
			 }*/
		  $this->assign('goodscommon_data',$goodscommon_data);	
		  $this->assign('goodsstore',$goodsstore);	
		  $this->assign('goods_data',$goods_data);	
		  $this->assign('integralgoods_data',$integralgoods_data);	
		  $this->assign('prizegoods_data',$prizegoods_data);	
		  $this->assign('goods_id',$goods_id);	
          $this->display("editstate");
	    }
    }
	
  //修改上架状态
  public  function setgoods_state(){
       $goods_commonid=I('goods_commonid');
	   $goods_type=I('goods_type');
	   $type=I('type');
	   $goods = new GoodsService();
	   $flag=$goods->setstateGoods($goods_commonid,$goods_type,$type);
	   if($flag==1){
	      switch($goods_type){
		    case 1:
		    $this->success ( '操作成功！', U ( 'admin/goods/goods_list' ) ); 
			break;
			case 2:
		    $this->success ( '操作成功！', U ( 'admin/goods/goods_integral_list' ) ); 
			break;
			case 3:
		    $this->success ( '操作成功！', U ( 'admin/goods/goods_prize_list' ) ); 
			break;
		  }	
	   }else{
		 $this->error ("操作失败");
	   }
  }	
	

/**
 *现金商品限时抢购
 */
	public function goods_period(){
	    //获取商城信息
		$shop_info=get_shop_proxy();
		$shop_id=$shop_info['shop_id']?$shop_info['shop_id']:0;
		$goods_commonid=I('goods_id');
		//获取现金id
		$goods=M('goods')->where("goods_commonid=$goods_commonid")->find();
		$goods_id=$goods['goods_id'];
		if(IS_POST){
		   //保存信息
		   $period_start=I('period_start');
		   $period_end=I('period_end');
		   $start_m=I('start_m');
		   $start_i=I('start_i');
		   $end_m=I('end_m');
		   $end_i=I('end_i');
		   $goods_id=I('goods_id');
		   $type=I('type');
		   $is_use=I('is_use');
		   $data=array();
		   foreach($period_start as $key=>$val){
		     $data[$key]['goods_id'] = $goods_id;
			 $data[$key]['begin_seconds'] = $start_m[$key]*3600+$start_i[$key]*60;
			 $data[$key]['end_seconds'] = $end_m[$key]*3600+$end_i[$key]*60;;
			 $data[$key]['add_time'] =time();
			 $data[$key]['on_using'] ="";
			 $data[$key]['begin_date'] =strtotime($period_start[$key]);
			 $data[$key]['end_date'] = strtotime($period_end[$key]);
			 $data[$key]['time_cycle'] = 86400; 
			 $data[$key]['type'] = 1; 
			 $data[$key]['on_using'] = $is_use; 
			 $data[$key]['shop_id'] = $shop_id; 
		   }
		   //删除原有的
		   M('goods_period_sale')->where('goods_id='.$goods_id." and type=1 and shop_id=".$shop_id)->delete();
		   foreach($data as $val){
		     M('goods_period_sale')->add($val);
		   }
		   $goods=M('goods')->where("goods_id=$goods_id")->find();
		   $goods_commonid=$goods['goods_commonid'];
		   $this->success ( '操作成功！', U ( "admin/goods/goods_period/goods_id/$goods_commonid" ) ); 
		}else{
		
		  $list=M('goods_period_sale')->where("goods_id=".$goods_id." and type=1 and shop_id=".$shop_id)->find();
		  $list['goods_id'] = $goods_id;
		  $list['begin_h'] = sprintf("%02d", $list['begin_seconds']/3600);//生成2位数，不足前面补0 
		  $list['begin_i'] = sprintf("%02d",($list['begin_seconds']%3600)/60);
		  $list['end_h'] = sprintf("%02d",$list['end_seconds']/3600);
		  $list['end_i'] = sprintf("%02d",($list['end_seconds']%3600)/60);
		  $list['begin_date'] =$list['begin_date']?date('Y-m-d',$list['begin_date']):date('Y-m-d');
		  $list['end_date'] = $list['end_date']?date('Y-m-d',$list['end_date']):date('Y-m-d');
		  $this->assign('goods_commonid',$goods_commonid);
		  $this->assign('goods_id',$goods_id);
		  $this->assign('list',$list);	
          $this->display("goods_period");
		}
    }

/**
 *现金商品编辑,添加限时抢购
 */
	public function ajax_add_goods_period(){
	    $key=I('key');
		$this->assign('key',$key);	
	    $this->display("ajax_add_goods_period");
    }


/**
 *现金商品多时段抢购
 */
	public function goods_periods(){
	    //获取商城信息
		$shop_info=get_shop_proxy();
		$shop_id=$shop_info['shop_id']?$shop_info['shop_id']:0;
	    $goods_commonid=I('goods_id');
		//获取现金id
		$goods=M('goods')->where("goods_commonid=$goods_commonid")->find();
		$goods_id=$goods['goods_id'];
		
		$PeriodService = new PeriodService();
        $Period = $PeriodService->get_period_sales($goods_id,1246);
		if(IS_POST){
		   //保存信息
		   $period_start=I('period_start');
		   $period_end=I('period_end');
		   $start_m=I('start_m');
		   $start_i=I('start_i');
		   $end_m=I('end_m');
		   $end_i=I('end_i');
		   $goods_limit=I('goods_limit');
		   $goods_id=I('goods_id');
		   $type=I('type');
		   $is_use=I('is_use');
		   //判断是否为空
		   $data=array();
		   foreach($period_start as $key=>$val){
		     if($start_m[$key]==""){
			   $this->error ("开始日期不能为空");
			 }
			 if($end_m[$key]==""){
			   $this->error ("结束日期不能为空");
			 }
		     $data[$key]['goods_id'] = $goods_id;
			 $data[$key]['begin_seconds'] = $start_m[$key]*3600+$start_i[$key]*60;
			 $data[$key]['end_seconds'] = $end_m[$key]*3600+$end_i[$key]*60;;
			 $data[$key]['add_time'] =time();
			 $data[$key]['on_using'] ="";
			 $data[$key]['begin_date'] =strtotime($period_start[$key]);
			 $data[$key]['end_date'] = strtotime($period_end[$key]);
			 $data[$key]['time_cycle'] = 86400; 
			 $data[$key]['type'] = $type; 
			 $data[$key]['on_using'] = $is_use; 
			 $data[$key]['shop_id'] = $shop_id; 
			 $data[$key]['goods_limit'] = $goods_limit[$key]; 
		   }
		   //删除原有的
		   M('goods_period_sale')->where('goods_id='.$goods_id." and type=2 and shop_id=".$shop_id)->delete();
		   foreach($data as $val){
		     M('goods_period_sale')->add($val);
		   }
		   $goods=M('goods')->where("goods_id=$goods_id")->find();
		   $goods_commonid=$goods['goods_commonid'];
		   $this->success ( '操作成功！', U ( "admin/goods/goods_periods/goods_id/$goods_commonid" ) );  
		}else{	
/*$PeriodService = new PeriodService();
  $f=$PeriodService->get_period_sales($goods_id);
  print_r($f);
*/
		  $list=M('goods_period_sale')->where("goods_id=".$goods_id." and type=2 and   shop_id=".$shop_id)->order("id asc")->select();
		  foreach($list as $key=>$val){
		     $list[$key]['goods_id'] = $goods_id;
			 $list[$key]['begin_h'] = sprintf("%02d", $val['begin_seconds']/3600);//生成2位数，不足前面补0 
			 $list[$key]['begin_i'] = sprintf("%02d",($val['begin_seconds']%3600)/60);
			 $list[$key]['end_h'] = sprintf("%02d",$val['end_seconds']/3600);
			 $list[$key]['end_i'] = sprintf("%02d",($val['end_seconds']%3600)/60);
			 $list[$key]['begin_date'] =date('Y-m-d',$val['begin_date']);
			 $list[$key]['end_date'] = date('Y-m-d',$val['end_date']);
		  }
		  $this->assign('goods_commonid',$goods_commonid);
		  $this->assign('goods_id',$goods_id);
		  $this->assign('list',$list);	
          $this->display("goods_periods");
		}
    }
/**
 *现金商品编辑,添加分时端抢购
 */
	public function ajax_add_goods_periods(){
	    $key=I('key');
		$this->assign('key',$key);	
	    $this->display("ajax_add_goods_periods");
    }
	
/**
 *设置默认数据
 */
	public function goods_default_data(){
	    //获取商城信息
		$shop_info=get_shop_proxy();
		$shop_id=$shop_info['shop_id']?$shop_info['shop_id']:0;
	    $goods_commonid=I('goods_commonid');
		//保存信息
		if(IS_POST){
		//处理虚假评价字段
           $arr = array();
           $arr['name'] = I('evaluate_name');
		   $arr['nick'] = I('evaluate_nick');
           $arr['content'] = I('evaluate_content');
		   $arr['addtime'] = I('evaluate_time');
           $data['false_evaluate'] = array2string($arr);
		//虚假销售量
		   $data['false_salenum'] = I('false_salenum');
		//保存数据
		   $flag = M('goods_common')->where("goods_commonid=" . $goods_commonid)->data($data)->save();   
		//操作完成后跳转
		   $this->success ( '操作成功！', U ( "admin/goods/goods_default_data/goods_commonid/$goods_commonid" ));  
		}else{
		  $info=M('goods_common')->where("goods_commonid=".$goods_commonid." and   shop_id=".$shop_id)->find();
		  $data=array();
		  if($info){
		    $list=string2array($info['false_evaluate']);
			foreach($list['name'] as $key=>$val){
		     $data[$key]['goods_commonid'] = $goods_commonid;
			 $data[$key]['evaluate_name'] =$val;
			 $data[$key]['evaluate_nick'] = $list['nick'][$key];
			 $data[$key]['evaluate_content'] = $list['content'][$key];
			 $data[$key]['evaluate_time'] = $list['addtime'][$key];
		    }
			$this->assign('data',$data);
			$this->assign('false_salenum',$info['false_salenum']);
		  }
		  $this->assign('goods_commonid',$goods_commonid);
		  $this->assign('goods_id',$goods_commonid);
          $this->display("goods_default_data");
		}
    }
/**
 *ajax生成规则输入框
 */
	public function  ajax_goods_default_data(){
	  $key=I('key');
		$this->assign('key',$key);	
      $this->display("ajax_goods_default_data");
	}
	 

/**
  *生成execel文件，导出产品列表
  */	
  public function goodslist_exe(){
     
	 //调取商品信息
	  //获取商城信息
		$shop_info=get_shop_proxy();
		$shop_id=$shop_info['shop_id']?$shop_info['shop_id']:0;
		$goodsstore = new GoodsstoreService();
		$goods = new GoodsService();
		$arr=array();
		$arr['shop_id']=$shop_id;	
		//$arr['limit']=" 0,20 ";
		//单独查询分类
		if($_GET['gc_id']){
		$arr['goodsclass']=$_GET['gc_id'];
		}
		if(IS_POST){
		  $arr['goodsclass']=$_POST['goodsclass'];
		  $arr['state']=$_POST['goodsstatus'];
		  $arr['end_time']=$_POST['end_time'];
		  $arr['begin_time']=$_POST['begin_time'];
		  $arr['key']=$_POST['keywords'];
		  //渠道
		  $arr['channel_type']=$_POST['channel_type'];
		  $arr['channel_id']=$_POST['channel_id'];
		  $this->assign('class',$_POST['goodsclass']);	
		  $this->assign('goodsstatus',$_POST['goodsstatus']);	
		  $this->assign('keywords',$_POST['keywords']);	
		  $this->assign('begin_time',$_POST['begin_time']);	
		  $this->assign('end_time',$_POST['end_time']);	
		  $goodslist=$goods->goods_common_list($arr,false);
		}else{
	  	  $goodslist=$goods->goods_common_list($arr,false);
		}
		//加工数据
		foreach($goodslist as $key=>$val){
		  //商品总量
		  $goodslist[$key]['goods_num']=$val['goods_salenum']+$val['goods_storage'];
		  //现金上架库存
		 $goodslist[$key]['goods_storage_m'] = D("goods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_storage');
		 $goodslist[$key]['goods_salenum_m'] = D("goods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_salenum');
		 $goodslist[$key]['goods_storage_salenum_m']=$goodslist[$key]['goods_storage_m']."/".$goodslist[$key]['goods_salenum_m'];
		 
		  //积分上架库存
		 $goodslist[$key]['goods_storage_i'] =D("integralgoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_storage');
		 $goodslist[$key]['goods_salenum_i'] =D("integralgoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_salenum');
		 $goodslist[$key]['goods_storage_salenum_i']=$goodslist[$key]['goods_storage_i']."/".$goodslist[$key]['goods_salenum_i'];
		  //活动上架库存
		 $goodslist[$key]['goods_storage_p'] =D("prizegoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_storage');
		 $goodslist[$key]['goods_salenum_p'] =D("prizegoods")->where('goods_commonid='.$val['goods_commonid'])->getField('goods_salenum');
		 $goodslist[$key]['goods_storage_salenum_p']=$goodslist[$key]['goods_storage_p']."/".$goodslist[$key]['goods_salenum_p'];
		  //未上架库存
		  $goodslist[$key]['goods_storagein'] = $val['goods_storage']-$goodslist['list'][$key]['goods_storage_m']-$goodslist['list'][$key]['goods_storage_i']- $goodslist['list'][$key]['goods_storage_p'] ;
		  $goodslist[$key]['goods_addtime'] = date('Y-m-d  h:i:s',$val['goods_addtime']);
		  
		  
		}
		//定义execl操作方法
        $ExeclService = new ExeclService();
	    $ExeclService->downMoreColumnDateToExel($goodslist,"产品列表信息",array('goods_commonid','goods_serial','goods_name','goods_num','goods_storage','goods_storagein','goods_storage_salenum_m','goods_storage_salenum_i','goods_storage_salenum_p','goods_salenum','goods_addtime'),array('id','商品编号','商品名称','商品总量','总库存','未上架库存','上架库存 / 已售(现金)','上架库存 / 已售（积分）','上架库存 / 已售（活动）','已售总和','添加时间'),array(10,20,35,10,10,15,25,25,25,10,20),200);
	
	
		
		
	 
  }
  



/**
 *ajax生成规则输入框
 */
	public function  ajax_add_rules(){
      $this->display("ajax_add_rules");
	}

/**
 *ajax生成新增栏目
 */
	public function  ajax_add_content(){
      $this->display("ajax_add_content");
	}	
	
	
/**
 *ajax调取商户列表
 */    
	public function  ajax_search_shop_info(){
	  header('Content-type: text/html;charset=UTF-8');
	  $shop_proxy=get_shop_proxy();
	  $proxyid=$shop_proxy['proxy_id'];
	  $keywords=I('str');
	  //获取商户列表
	  $shoplist=get_merchant_by_proxy($proxyid,array('name'=>$keywords));
	  if($shoplist){
	
	  }else{
	 $shoplist = array(
      /* array("merchant_id" => 1000, "merchant_name" => "民乐园万达广场", "province" => "1", "city" => "2", "district" => "3", "address" => "解放路中段111号", "longitude" => "108.95862 ", "latitude" => "34.26519"),
        array("merchant_id" => 1001, "merchant_name" => "长安城堡大酒店", "province" => "陕西", "city" => "西安", "district" => "碑林区", "address" => "环城南路西段12号", "longitude" => "108.94067", "latitude" => "34.25047"),
        array("merchant_id" => 1002, "merchant_name" => "土门美华金都酒店", "province" => "陕西", "city" => "西安", "district" => "莲湖区", "address" => "劳动南路64号", "longitude" => "108.90521", "latitude" => "34.25185"),
        array("merchant_id" => 1003, "merchant_name" => "华浮宫酒店", "province" => "陕西", "city" => "西安", "district" => "未央区", "address" => "未央湖旅游开发区阳光大道1号", "longitude" => "108.9778", "latitude" => "34.4014"),
        array("merchant_id" => 1004, "merchant_name" => "绿地假日酒店酒店", "province" => "陕西", "city" => "西安", "district" => "高新区", "address" => "高新技术产业开发区锦业路5号", "longitude" => "108.86515", "latitude" => "34.19354"),*/
    );
	  }
	  $this->assign('shoplist',$shoplist);
      $this->display("ajax_search_shop_info");
	}
	
/**
 *ajax调取人员列表
 */    
	public function  ajax_get_channel(){
	  header('Content-type: text/html;charset=UTF-8');
	  $shop_proxy=get_shop_proxy();
	  $proxyid=$shop_proxy['proxy_id'];
	  $id=I('id');
	  $pid=I('pid');
	  //获取人员列表
	  $ExtendService = new ExtendService();
	  $channelList=$ExtendService->memberList($pid);
	  $this->assign('channelList',$channelList);
	  $this->assign('id',$id);
	  $this->assign('pid',$pid);
      $this->display("ajax_get_channel");
	}

	
	
/**
 *ajax验证商品编号是否存在
 */  	
	public function  ajax_check_serial(){
         header('Content-type: text/html;charset=UTF-8');
	     $shop_proxy=get_shop_proxy();
	     $proxyid=$shop_proxy['proxy_id'];
		 $shop_id=$shop_proxy['shop_id'];
		 $goods_serial=I('goods_serial');
		 $goods_commonid=I('goods_commonid');
		 if($goods_commonid){
		 $goods=M('goods_common')->where("goods_commonid<>$goods_commonid  and shop_id=$shop_id and goods_serial='$goods_serial'")->find();
		 }else{
		 $goods=M('goods_common')->where(" shop_id=$shop_id and goods_serial='$goods_serial'")->find();
		 }
		 if($goods){
		   echo "此商品编号已存在，请从新输入"; 
		 }else{
		   
		 }
    }

/**
 *ajax验证商品总库存是否合理，不能小于已分配出去的数量
 */  	
	public function  ajax_check_goods_storage(){
         header('Content-type: text/html;charset=UTF-8');
	     $shop_proxy=get_shop_proxy();
	     $proxyid=$shop_proxy['proxy_id'];
		 $shop_id=$shop_proxy['shop_id'];
		 $goods_storage=I('goods_storage');
		 $goods_commonid=I('goods_commonid'); 
		$goodsstore = new GoodsstoreService();
		$storage_num=$goodsstore->outstoreinfo($goods_commonid);
		$info=array();
		if($storage_num<=$goods_storage){
		  echo 1;
		}else{
		  echo 0;
		} 
    }
	
	
	


	
/**
一下方法为预留方法，前期不用*************************************************************************
*/	
	
	//产品附件操作
	public function goods_common_images(){
		if(IS_POST){
	     
	    }else{
		  $goods_id=I('goods_id');
		  $this->assign('goods_id',$goods_id);	
          $this->display("editimage");
	    }
    }
	//商品上架,现金商品上架
	public function goods_common_state1(){
		if(IS_POST){
		   $goods_commonid=I('goods_commonid');
		   $goods_id=I('goods_id');
		   $goods_price=I('goods_price');
		   $storage=I('storage');
		   //修改库存
	       $flag=D('goods')->setstorage($goods_commonid,$goods_id,$storage); 
		   if($flag!=1){
		     $this->error ( '库存操作失败！'); 
			 exit;
		   }
		   //修改商品信息
		   $goods_data=array();
		   $goods_data['goods_price']=$goods_price;
		   $flag=D('goods')->editGoodsbycommonid($goods_commonid,$goods_data);
		   $this->success ( '操作成功！');   
	    }else{
		  $goods_id=I('goods_id');
		  $goods_data=D('goods')->info($goods_id); 
		  $this->assign('goods_data',$goods_data);	
		  $this->assign('goods_id',$goods_id);	
          $this->display("editstate1");
	    }
    }
	
	//商品上架,积分商品上架
	public function goods_common_state2(){
		if(IS_POST){
	       $goods_commonid=I('goods_commonid');
		   $goods_id=I('goods_id');
		   $storage=I('storage');
		   $goods_integral=I('goods_integral');
		   //修改库存
	       $flag=D('integralgoods')->setstorage($goods_commonid,$goods_id,$storage); 
		   if($flag!=1){
		     $this->error ( '库存操作失败！'); 
			 exit;
		   }
		   //修改商品信息
		   $goods_data=array();
		   $goods_data['goods_integral']=$goods_integral;
		   $flag=D('integralgoods')->editGoodsbycommonid($goods_commonid,$goods_data);
		   $this->success ( '操作成功！');  
	    }else{
		  $goods_id=I('goods_id');
		  $goods_data=D('integralgoods')->info($goods_id); 
		  $this->assign('goods_data',$goods_data);	
		  $this->assign('goods_id',$goods_id);	
          $this->display("editstate2");
	    }
    }
	
	//商品上架,活动商品上架
	public function goods_common_state3(){
		if(IS_POST){
	       $goods_commonid=I('goods_commonid');
		   $goods_id=I('goods_id');
		   $storage=I('storage');
		   $goods_price=I('goods_price');
		   $goods_integral=I('goods_integral');
		   //修改库存
	       $flag=D('prizegoods')->setstorage($goods_commonid,$goods_id,$storage); 
		   if($flag!=1){
		     $this->error ( '库存操作失败！'); 
			 exit;
		   }
		   //修改商品信息
		   $goods_data=array();
		   $goods_data['goods_integral']=$goods_integral;
		   $goods_data['goods_price']=$goods_price;
		   $flag=D('prizegoods')->editGoodsbycommonid($goods_commonid,$goods_data);
		   $this->success ( '操作成功！');  
		   
	    }else{
		  $goods_id=I('goods_id');
		  $goods_data=D('prizegoods')->info($goods_id); 
		  $this->assign('goods_data',$goods_data);	
		  $this->assign('goods_id',$goods_id);	
          $this->display("editstate3");
	    }
    }
	
	public function ajax_uplaod_img(){
		$typeArr = array("jpg", "png", "gif");//允许上传文件格式
		$path = "./Uploads/";//上传路径
		if (isset($_POST)) {
			$name = $_FILES['file']['name'];
			$size = $_FILES['file']['size'];
			$name_tmp = $_FILES['file']['tmp_name'];
			if (empty($name)) {
			  echo json_encode(array("error"=>"您还未选择图片"));
			  exit;
		    }
			$type = strtolower(substr(strrchr($name, '.'), 1)); //获取文件类型
			
			if (!in_array($type, $typeArr)) {
			  echo json_encode(array("error"=>"清上传jpg,png或gif类型的图片！"));
			  exit;
			}
			if ($size > (500 * 1024)) {
			  echo json_encode(array("error"=>"图片大小已超过500KB！"));
			  exit;
			}
			
			$pic_name = time() . rand(10000, 99999) . "." . $type;//图片名称
			$pic_url = $path . $pic_name;//上传后图片路径+名称
			if (move_uploaded_file($name_tmp, $pic_url)) { //临时文件转移到目标文件夹
			  echo json_encode(array("error"=>"0","pic"=>$pic_url,"name"=>$pic_name));
			} else {
			  echo json_encode(array("error"=>"上传有误，清检查服务器配置！"));
			}
		}

	}



}