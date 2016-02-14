<?php
/*
 * 商品前台展示模块。列表页面，详情页面，搜索页面
 */
namespace Home\Controller;
use BizService\FavoriteService;
use BizService\ShareService;
use Think\Controller;
use BizService\GoodshomeService;
use BizService\ExtendService;
use BizService\PeriodService;

header("Content-type:text/html;charset=utf-8");

class GoodsController extends Controller
{ 
    public $shop_info,$shop_id,$proxy_id;
    public function _initialize()
    {
        $this->shop_info = shop_info(session('city'));
		$this->shop_id=$this->shop_info['shop_id'];
		$this->proxy_id=$this->shop_info['member_id'];
        $this->assign("shop_base_info", $this->shop_info);
		
		 
    }

    //产品列表页面默认
    public function index()
    {
        $this->goodslist();
    }

    //产品列表页面
    public function goodslist()
    {
        $type = I("type", 0, 'intval');
        $gc_id = I("gc_id", 0, 'intval');
        $lat = I("lat") ? I("lat") : 0;
        $long = I("long") ? I("long") : 0;
        $data = array();
        $data['page'] = 1;
        $data['size'] = 7;
        //分页
        $data['limit'] = "0,7";
        if ($gc_id) {
            $data['gc_id'] = $gc_id;
            //搜索分类名称作为标题
            $class_info = M('goods_class')->where('gc_id=' . $gc_id)->field('gc_name,gc_description,gc_keywords')->find();
            $this->assign('description', $class_info['gc_description']);
            $this->assign('keywords', $class_info['gc_keywords']);
            $this->assign('title', $class_info['gc_name']);
        }
        if ($type) {
            $data['order'] = $type;
        }
        if ($lat) {
            //gps定位
            $map = new \BizService\MapService();
            $f = $map->locationByGPS($long, $lat);
            $data['lat'] = $f['lat'];
            $data['lng'] = $f['lng'];
        } elseif (session('lat') && session('lng')) {
            $data['lat'] = session('lat');
            $data['lng'] = session('lng');
        } else {
            $data['lat'] = "34.26567";
            $data['lng'] = "108.953435";
            //百度定位
            /*$mapService = new \BizService\MapService();
            $map = $mapService->locationByIP();
            $data['lat'] = $map['lat'];
            $data['lng'] = $map['lng'];*/
        }

        $goodslist = array();
        $GoodshomeService = new GoodshomeService();
        $goodslist = $GoodshomeService->mgoods_list($data, 0);

        foreach ($goodslist as $key => $val) {
            $id = $val['goods_commonid'] ? $val['goods_commonid'] : 0;
            $good_info = D('Admin/goods')->where('goods_commonid=' . $id)->find();
            if ($good_info) {

            } else {
                unset($goodslist[$key]);
            }
        }
        //测试ip定位
        /* $ip = get_client_ip();
         $Ips = new \Org\Net\IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
         $area = $Ips->getlocation($ip); // 获取某个IP地址所在的位置*/
        //获取gps信息


        $this->assign('goodslist', $goodslist);
        $this->assign('gc_id', $gc_id);
        $this->assign('type', $type);
        $this->assign('lat', $lat);
        $this->assign('long', $long);
        $this->display('goods_list');
    }

    //产品列表页面(分页)
    public function ajax_goodslist()
    {
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
        
      
        $p_data = $GoodshomeService->mgoods_list($data, 0);
        $count = count($p_data);
        //如果是lbs
        if($type==1){
        	//定义url
        	$url="http://api.map.baidu.com/geosearch/v3/nearby";
        	//定义参数数组
        	$params=array();
        	//设置排序
        	$params['sortby']="distance:1|price:1";
        	//用户信息
        	$params['geotable_id']=C('MAP_TABLEID');
	        $params['ak']=C('MAP_KEY');
        	//位置信息
        	$params['location']=$data['lng'].",".$data['lat'];
        	//半径
        	$params['radius']="5000000000";
        	//筛选条件
        	if($gc_id > 1){
        		//获取分类名称
        		$gc=M("goods_class")->where('gc_id='.$gc_id)->find();
        		$params['q']=$gc['gc_name'];
        	}
        	$params['filter']="shop_id:".$shop_id;
        	$f=curl_http($url, $params, $method = 'GET');
        	$d=json_decode($f,true);
        	$count=$d['total'];
        }
        
        
        $max_page = ($count > 0) ? ceil($count / $size) : 1;
        if($type!=1){
          if ($page > $max_page) {
            $array = array();
            echo json_encode($array);
            exit;
          }
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
		if ((I('loadLength') == 1)&&($type==1)) {
		    $data['page']= 1;
            $data['size'] = $page  * $num;
			$data['loadLength']=1;
		}
		
        $goodslist = $GoodshomeService->mgoods_list($data, 0);
        if ($max_page < $page) {
            $array = array();
            echo json_encode($array);
        } else {
            echo json_encode($goodslist);
        }

    }

    //产品详情页面
    public function goodsview()
    {
        $goods_id = I('id', 0, 'intval');
        $goods_commonid = I('goods_commonid', 0, 'intval');
        //获取产品详情信息
        $goods = array();
        if ($goods_id) {
            $goods = D('goods')->getinfobyid($goods_id);
			$goods_commonid = $goods['goods_commonid'];
        } elseif ($goods_commonid) {
            $goods = D('goods')->getinfobycommonid($goods_commonid);
        } else {
            $this->error('参数有误！', 'index');
        }
		
        if ($goods) {
            //调取商品轮播图
            $goods_lunbo = D('Admin/goodsimages')->info($goods['goods_commonid']);
            $goods['lunbo'] = $goods_lunbo;
            //获取公共信息
            $goods_common = D('Admin/goodscommon')->where("goods_commonid=" . $goods['goods_commonid'])->find();
            $goods['subtitle'] = $goods_common['subtitle'];
			$goods['post_tags'] = $goods_common['post_tags'];
            $goods['position_tags'] = $goods_common['position_tags'];
            $goods['usetime'] = string2array($goods_common['usetime']);
            $goods['start_date'] = $goods_common['start_date'];
            $goods['end_date'] = $goods_common['end_date'];
            $goods['rules'] = string2array($goods_common['rules']);
            $goods['mobile_body'] = htmlspecialchars_decode($goods_common['mobile_body']);
            $goods['title'] = $goods_common['seo_title'] ? $goods_common['seo_title'] : $goods_common['goods_name'];
            $goods['keywords'] = $goods_common['keywords'];
            $goods['description'] = $goods_common['description'];
            $goods['addcontent'] = string2array($goods_common['addconent']);
			//虚假销售数量
			$goods['goods_salenum'] = $goods_common['false_salenum']+$goods_common['goods_salenum'];

            
            //获取商家信息
            $store = get_merchant_info($goods_common['store_id']);
            $goods['store'] = $store;
            //判断是否为虚假库存
            if ($goods['storage_type']) {
                //判断是否小于等于最小调拨值，如果小于就重新调配库存
                if ($goods['storage_num'] <= $goods['storage_type_num']) {
                    //调取现有的库存是否够最大库存的数量,就将显示的库存显示为最大库存值，如果不是，就显示现有库存
                    if ($goods['goods_storage'] >= $goods['storage_type_max']) {
                        $goods['storage_num'] = $goods['storage_type_max'];
                    } else {
                        $goods['storage_num'] = $goods['goods_storage'];
                    }
                    M('goods')->where('goods_id=' . $goods['goods_id'])->data('storage_num=' . $goods['storage_num'])->save();
                }
                $goods['goods_storage'] = $goods['storage_num'];
            }
        } else {
            $this->error('无此商品信息', 'index');
        }
		
		
        //检查此商品是否是特惠推广商品
        $ExtendService = new ExtendService();
        $channel = $ExtendService->checkExtendGoods($goods_id);
		$this->assign('channel', $channel);
        if (I('goods_code', '', 'strip_tags')) {
            $goods_code = I('goods_code', '', 'strip_tags');
            $goods_id = I('id', '', 'intval');
            $channel_info = $ExtendService->getExtendGoods($goods_id, $goods_code);
            if ($channel_info) {
                if ($channel_info['end_time'] > time()) {
                    session('goods_code', $goods_code);
                    session('goods_id', $goods_id);
                    $this->assign('goods_code', $goods_code);
                    if($channel_info['discount_type']==1){
                        $this->assign('channel_price', $goods['goods_price'] * $channel_info['discount'] / 100);
                    }else if($channel_info['discount_type']==2){
                        $this->assign('channel_price', $channel_info['discount_price']);
                    }
                    
                } else {
                    $this->error('优惠码已过期！');
                }
            } else {
                $this->error('优惠码不正确！');
            }
        }
		
        $fav = new FavoriteService();
        $is_fav = $fav->getFavorite(session('userId'), $goods_id);
        if (I('gc_id')) {
            $this->assign('gc_id', I('gc_id', 0, 'intval'));
        }
		
		//判断是否有限时抢购和定时段抢购
		$PeriodService = new PeriodService();
        $Period = $PeriodService->get_period_sales($goods_id);
		if($Period){
		  if($Period['goods_limit']>0){
		    $goods['goods_storage'] = $Period['goods_num'];
		  }
		}

        vendor("WeixinJssdk.WeixinJssdk");
        $jssdk = new \WeixinJssdk();
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', $signPackage);
        $share_service = new ShareService();
        $shop_proxy = get_shop_proxy();
        $share = $share_service->get_share_set($shop_proxy['shop_id']);
        $this->assign('share', $share[0]);

        $this->assign('is_fav', $is_fav);
		$this->assign('is_fav', $is_fav);
        $this->assign('Period', $Period);
        $this->assign('goods', $goods);
        $this->assign('goods_id', $goods['goods_id']);
        $this->display('goods');
    }


    //产品详情页面
    public function setpostion()
    {
        $lat = I("lat") ? I("lat") : 0;
        $lng = I("lng") ? I("lng") : 0;
        $map = new \BizService\MapService();
        $f = $map->locationByGPS($lng, $lat);
        if ($lat) {
            session("lat", $f['lat']);
        }
        if ($lng) {
            session("lng", $f['lng']);
        }
        echo session("lat");
    }

    //商家位置页面
    public function  storemap()
    {
        //调取商家信息
        $store_id = I('store_id');
        $goods_id = I('goods_id');
        $info = get_merchant_info($store_id);
        $this->assign('info', $info);
        $this->display('storemap');
    }

    public function ajax_browse()
    {
        $goods_id = I('goods_id');
        $goods_type = I('goods_type') ? I('goods_type') : 1;
        $gc_id = I('gc_id') ? I('gc_id') : 0;
        $username = session('userName');
        $userid = D('users')->findUserIdByName($username);
        $data = array();
        $data['goods_id'] = $goods_id;
        $data['gc_id'] = $gc_id;
        $data['userid'] = $userid;
        $flag = D('goodsbrowse')->addBrowse($data);
        echo "1" . $flag;
    }

    //验证优惠信息
    public function ajax_check_channel_code()
    {
        $goods_id = I('id') ? I('id') : 0;
        $goods_code = I('goods_code') ? I('goods_code') : "";
        $goods_price = I('goods_price') ? I('goods_price') : 0;
        if ($goods_code == "") {
            $arr = array();
            $arr['status'] = -4;
            $arr['message'] = '请填写优惠码！';
            echo json_encode($arr);
            exit;
        }
        //检查此商品是否是特惠推广商品
        $ExtendService = new ExtendService();
        $channel = $ExtendService->checkExtendGoods($goods_id);
        if ($goods_code) {
            $channel_info = $ExtendService->getExtendGoods($goods_id, $goods_code);
            if ($channel_info) {
                if ($channel_info['end_time'] > time()) {
				
                    session('goods_code', $goods_code);
                    session('goods_id', $goods_id);
                    $arr = array();
                    $arr['status'] = 1;
                    $arr['goods_code'] = $goods_code;
					if($channel_info['discount_type']==1){
                      $arr['price'] = $goods_price * $channel_info['discount'] / 100;
					}else{
					  $arr['price'] = $channel_info['discount_price'];
					}
					
					
                    echo json_encode($arr);
                } else {
                    $arr = array();
                    $arr['status'] = -1;
                    $arr['message'] = '优惠码已过期！';
                    echo json_encode($arr);
                }
            } else {
                $arr = array();
                $arr['status'] = -2;
                $arr['message'] = '优惠码不正确！';
                echo json_encode($arr);
            }
        } else {
            $arr = array();
            $arr['status'] = -3;
            $arr['message'] = '参数不对！';
            echo json_encode($arr);
        }
    }


}