<?php

namespace Home\Controller;
use BizService\UserService;
use BizService\GoodsService;
use BizService\GoodsstoreService;
use Think\Controller;

class PinglunController extends BaseController {
    public $pinglun,$goods_service;
    public function __construct(){
        parent::__construct();
        $this->pinglun = D("PinglunManage"); 
        $this->goods_service = new GoodsstoreService();
    }

    public function pl_list(){
        //初始化数据
        $goods_id = I("get.goods_id");
        $arr=$this->pinglun->getlist($goods_id);
        $goodsinfo=$this->goods_service->getinfo($goods_id,array("goods_commonid,goods_id,goods_name"),1);
		
	//查找虚假评论
	$goods_common=M('goods_common')->where('goods_commonid='.$goodsinfo['goods_commonid'])->find();	
	$false_evaluate_list=string2array($goods_common['false_evaluate']);
	$data=array();
	foreach($false_evaluate_list['name'] as $key=>$val){
		 $data[$key]['goods_commonid'] = $goods_commonid;
	     $data[$key]['user_name'] =substr_replace($val,"****",3,4);
	     $data[$key]['pl_content'] = $false_evaluate_list['content'][$key];
		 $data[$key]['nickname'] = $false_evaluate_list['nick'][$key];
		 $data[$key]['pl_addtime'] = $false_evaluate_list['addtime'][$key];
    }
	$arr['list']=	array_merge($arr['list'],$data);
    //修改总数
	$arr['info']['count']=$arr['info']['count']+count($false_evaluate_list['name']);
	$arr['info']['five']=$arr['info']['five']+count($false_evaluate_list['name']);
	$arr['info']['count_xsd']=$arr['info']['count_xsd']?$arr['info']['count_xsd']:5;
		
        $this->assign("goodsinfo",$goodsinfo);
        $this->assign("list",$arr['list']);
        $this->assign('info',$arr['info']);  
        $this->display("list");
    }
    
}