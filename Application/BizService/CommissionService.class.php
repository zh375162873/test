<?php
namespace BizService;
use BizService\OrderService;

/**
 * 分销
 *
 * @author 张辉
 */ 
class CommissionService extends BaseService { 

	/**
	 * 获取渠道分成列表（后台）
	 * @param array $param
	 * @param int $ispagenation
	 * @return array
	 */
	public function channel_list($param=array(),$ispagenation=true){
	    $query = $this->	channellistsql($param);
		if($ispagenation){
		    $count = count(M()->query($query));
			$data=mypage($count,$query);
			foreach($data['list'] as $key=>$val){
			  $data['list'][$key]=M("commission_qd_money")->where('id='.$val['id'])->find();
			  $data['list'][$key]['total']=$data['list'][$key]['commission_total']*$data['list'][$key]['commission_rate']/100;
			}
			//统计总额
			$sum=M()->query("select sum(real_pay) as total  from ddt_commission_qd_money where  pay_status=2 and shop_id=".$param['shop_id']." and channel_id=".$param['channel_id']);
			$data['sum']=$sum[0]['total'];
			
			return $data;
		}else{
	        $data=	M()->query($query);
		    foreach($data as $key=>$val){
			  $data['list'][$key]=M("commission_qd_money")->where('id='.$val['id'])->find();
			}
			return $data;
		}
	}
	

	
	/**
	 * 获取渠道未结算列表（后台）
	 * @param array $param
	 * @return array
	 */
	public function channel_list_first($param=array()){
	        $data=array();
	        $commission_order=array();
			//获取未结算的订单
			$commission_order=M("commission_order")->where("qd_status=1 and shop_id=".$param['shop_id']." and channel_id=".$param['channel_id'])->order('id desc')->select();
			if($commission_order){
			$i=1;
			$income_rate=0;
			//补全信息，生成计算清单
			foreach($commission_order as $key=> $val){
			  if(($key>0)&&($income_rate!=$val['income_rate'])){
			    $i=$i+1;
			  }
			  $data[$i]['commission_total']=$data[$i]['commission_total']+$val['commission_fee'];
			  $data[$i]['income_rate']=$val['income_rate'];
			  $data[$i]['qd_calc_time1']=$val['qd_calc_time1'];
			  if($data[$i]['commission_order_id']){
			  $data[$i]['commission_order_id']=$val['id'].",".$data[$i]['commission_order_id'];
			  }else{
			  $data[$i]['commission_order_id']=$val['id'];
			  }
			  $income_rate=$val['income_rate'];
			}
			//计算单个数据的结算金额
			foreach($data as $key=> $val){
			  $data[$key]['pay_num']=$val['commission_total']*$val['income_rate']/100;
			}
			//计算本次计算总金额
			foreach($data as $key=>$val){ 
				   $num=$num+$val['pay_num'];
			}
		    $data[1]['pay_total']=$num;
			}
			
			return $data;
	}
	
	/**
	 * 渠道结算（后台）
	 * @param array $param
	 * @return int
	 */
	public function ChannelSettlement($param=array()){
	        $t=time();
			//添加结算信息
			$channel_list_first=$this->channel_list_first($param);
			$id=0;
			foreach($channel_list_first as $key=> $val){  
			   $data=array();
			   $data['commission_total']=$val['commission_total'];
			   $data['commission_rate']=$val['income_rate'];
			   $data['create_time']=$val['qd_calc_time1'];
			   $data['calc_time']=$t;
			   $data['shop_id']=$param['shop_id'];
			   $data['channel_id']=$param['channel_id'];
			   $data['pay_status']=1;
			   $id=M('commission_qd_money')->add($data);
			   //修改订单分成信息状态，并绑定结算信息
			   $commission_order_id= explode(',',$val['commission_order_id']);
			   if($commission_order_id){
			    foreach($commission_order_id as $val2){
			    $flag=M('commission_order')->where("qd_status=1 and shop_id=".$param['shop_id']." and channel_id=".$param['channel_id']." and id=".$val2)->data(array('qd_status'=>2,'commission_qd_id'=>$id,'commission_qd_id'=>$id,'qd_calc_time2'=>$t))->save();
				}
			   }
			}
			//给最后一条增加统计数据
			if($id>0){
			   $num=0;
			   foreach($channel_list_first as $val2){ 
				 $num=$num+($val2['commission_total']*$val2['income_rate']/100);
			   }
			   $flag=M('commission_qd_money')->where("pay_status=1 and shop_id=".$param['shop_id']." and channel_id=".$param['channel_id']." and id=".$id)->data(array('pay_total'=>$num))->save();
		    }
			//修改订单分成状态
	       // $flag=M('commission_order')->where("qd_status=1 and shop_id=".$param['shop_id']." and channel_id=".$param['channel_id'])->data(array('qd_status'=>2))->save();
			return 1;
	}

	
	/**
	 * 获取已结算未支付列表（后台）
	 * @param array $param
	 * @return array
	 */
	public function channel_list_second($param=array()){
	        $num=0;
            //调取状态为已结算未支付的记录信息
	        $commission_qd_money=array();
			$commission_qd_money=M("commission_qd_money")->where("pay_status=1 and shop_id=".$param['shop_id']." and channel_id=".$param['channel_id'])->order('id desc')->select();
			//整理出已结算未支付的列表
			if($commission_qd_money){
				foreach($commission_qd_money as $key=>$val){ 
				   $num=$num+($val['commission_total']*$val['commission_rate']/100);
				   $commission_qd_money[$key]['pay_total']=$val['commission_total']*$val['commission_rate']/100;
				}
				$commission_qd_money[0]['total']=$num;
			}
			return $commission_qd_money;
	}
	
	
	/**
	 * 支付（后台）
	 * @param array $param
	 * @return array
	 */
	public function ChannelPay($param=array()){
			//修改结算信息改为支付信息
			$channel_list_second=$this->channel_list_second($param);
			$guid=0;
			foreach($channel_list_second as $key=>$val){ 
			   $data=array(); 
			   //判断是否调整金额
			   if($key==0){
			       $real_pay=0;
				   $guid=$channel_list_second[0]['id'];
				   if($param['adjustment_type']==0){
					 $real_pay=$channel_list_second[0]['total']+$param['adjustment_money'];
				   }elseif($param['adjustment_type']==1){
					 $real_pay=$channel_list_second[0]['total']-$param['adjustment_money'];
				   }
				   $data['real_pay']=$real_pay; 
				   $data['adjustment_type']=$param['adjustment_type']; 
			       $data['adjustment_money']=$param['adjustment_money'];
				   $data['pay_desc']=$param['pay_desc'];
				   $data['pay_total']=$channel_list_second[0]['total']; 
			   }else{
			       $data['pay_total']=NULL; 
				   $data['pay_desc']=NULL; 
				   $data['real_pay']=NULL; 
				   $data['adjustment_money']=NULL;
			   }
			   $data['guid']=$guid;
			   $data['pay_status']=2;
			   $data['pay_time']=time();
			   $flag=M('commission_qd_money')->where("pay_status=1 and shop_id=".$param['shop_id']." and channel_id=".$param['channel_id']." and id=".$val['id'])->data($data)->save();
			   //修改订单分成信息状态，并绑定结算信息
			    $flag=M('commission_order')->where("qd_status=2 and shop_id=".$param['shop_id']." and channel_id=".$param['channel_id']." and commission_qd_id=".$val['id'])->data(array('qd_status'=>3))->save();

			}
			return 1;
	}
	

	 /**
	 * 渠道已支付总额（后台）
	 * @param array $param
	 * @return array
	 */
	public function ChannelPay_money($param=array()){
	   $sql="select sum(real_pay)as total  from  ddt_commission_qd_money where pay_status=2 and shop_id=".$param['shop_id']." and channel_id=".$param['channel_id'];
	   $query = M()->query($sql);
	   return $query[0]['total']?$query[0]['total']:0;
	}
	
	/**
	 * 渠道未确认消费的佣金总额（后台）
	 * @param array $param
	 * @return array
	 */
	public function Channel_no_money($param=array()){
	   $sql="select sum(referee_money)as total  from  ddt_commission_order where qd_status=0 and shop_id=".$param['shop_id']." and channel_id=".$param['channel_id'];
	   $query = M()->query($sql);
	   return $query[0]['total']?$query[0]['total']:0;
	}
	
	/**
	 * 渠道已结算，未支付的的佣金总额（后台）
	 * @param array $param
	 * @return array
	 */
	public function channel_list_second_money($param=array()){
	   $sql="select sum(real_pay)as total  from  ddt_commission_qd_money where pay_status=1 and shop_id=".$param['shop_id']." and channel_id=".$param['channel_id'];
	   $query = M()->query($sql);
	   return $query[0]['total']?$query[0]['total']:0;
	}
	
	/**
	 * 渠道未结算的佣金总额（后台）
	 * @param array $param
	 * @return array
	 */
	public function channel_list_first_money($param=array()){
	   $data=array();
	        $commission_order=array();
			$commission_order=M("commission_order")->where("qd_status=1 and shop_id=".$param['shop_id']." and channel_id=".$param['channel_id'])->order('id desc')->select();
			if($commission_order){
			$i=1;
			$income_rate=0;
			foreach($commission_order as $key=> $val){
			  if(($key>0)&&($income_rate!=$val['income_rate'])){
			    $i=$i+1;
			  }
			  $data[$i]['commission_total']=$data[$i]['commission_total']+$val['commission_fee'];
			  $data[$i]['income_rate']=$val['income_rate'];
			  $data[$i]['qd_calc_time1']=$val['qd_calc_time1'];
			  if($data[$i]['commission_order_id']){
			  $data[$i]['commission_order_id']=$val['id'].",".$data[$i]['commission_order_id'];
			  }else{
			  $data[$i]['commission_order_id']=$val['id'];
			  }
			  $income_rate=$val['income_rate'];
			}
			foreach($data as $key=> $val){
			  $data[$key]['pay_num']=$val['commission_total']*$val['income_rate']/100;
			}
			
			foreach($data as $key=>$val){ 
				   $num=$num+$val['pay_num'];
			}
		    $data[1]['pay_total']=$num;
			}
			$data['total']=$num;
			
			return $data['total']?$data['total']:0;
	}



	
	
	/**
	 * 获取推广分成列表（后台）
	 * @param array $param
	 * @param int $ispagenation
	 * @return array
	 */
	public function channelmember_list($param=array(),$ispagenation=true){
	    $query = $this->	extendlistsql($param);
		
		if($ispagenation){
		    $count = count(M()->query($query));
			$data=mypage($count,$query);
			if($data['list']){
				foreach($data['list'] as $key=>$val){
				 if($val['id']>0){
				  $data['list'][$key]=M("commission_user_money")->where('id='.$val['id'])->find();
				  $data['list'][$key]['total']=$data['list'][$key]['commission_total']*$data['list'][$key]['commission_rate']/100;
				  //统计总额
				$sum=M()->query("select sum(real_pay) as total  from ddt_commission_user_money where  pay_status=2 and shop_id=".$param['shop_id']." and referee_id=".$param['referee_id']);
				$data['sum']=$sum[0]['total'];
				  }
				}
			}
			return $data;
		}else{
	        $data=	M()->query($query);
		    foreach($data as $key=>$val){
			  $data['list'][$key]=M("commission_qd_money")->where('id='.$val['id'])->find();
			}
			return $data;
		}
	}
	
	/**
	 * 获取推广未结算列表（后台）
	 * @param array $param
	 * @return array
	 */
	public function channelmember_list_first($param=array()){
	        $data=array();
	        $commission_order=array();
			$commission_order=M("commission_order")->where("user_status=1 and shop_id=".$param['shop_id']." and referee_id=".$param['referee_id'])->order('id desc')->select();
			if($commission_order){
				foreach($commission_order as $key=> $val){
				  //结算的分成订单id
				  if($data['commission_order_id']){
					$data['commission_order_id']=$val['id'].",".$data['commission_order_id'];
				  }else{
					$data['commission_order_id']=$val['id'];
				  }
				  //结算总额
				  $data['pay_total']=($val['commission_fee']*(100-$val['income_rate'])/100)+$data['pay_total'];
				  $data['user_calc_time1']=$val['user_calc_time1'];
				}
			}
			return $data;
	}
	
	/**
	 * 推广结算（后台）
	 * @param array $param
	 * @return array
	 */
	public function ChannelmemberSettlement($param=array()){
	           $t=time();
			//添加结算信息
			   $channelmember_list_first=$this->channelmember_list_first($param);
			   $id=0;
			   $data=array();
			   $data['commission_total']=$channelmember_list_first['pay_total'];
			   $data['pay_total']=$channelmember_list_first['pay_total'];
			   $data['create_time']=$channelmember_list_first['user_calc_time1'];
			   $data['calc_time']=$t;
			   $data['shop_id']=$param['shop_id'];
			   $data['referee_id']=$param['referee_id'];
			   $data['pay_status']=1;
			   $id=M('commission_user_money')->add($data);
			   //修改订单分成信息状态，并绑定结算信息
			   $commission_order_id= explode(',',$channelmember_list_first['commission_order_id']);
			   if($commission_order_id){
			    foreach($commission_order_id as $val2){
			    $flag=M('commission_order')->where("user_status=1 and shop_id=".$param['shop_id']." and referee_id=".$param['referee_id']." and id=".$val2)->data(array('user_status'=>2,'commission_user_id'=>$id,'user_calc_time2'=>$t))->save();
				}
			   }
			return 1;
	}
	
	
	/**
	 * 获取推广未支付列表（后台）
	 * @param array $param
	 * @return array
	 */
	public function channelmember_list_second($param=array()){
	        $num=0;
	        $commission_user_money=array();
			$commission_user_money=M("commission_user_money")->where("pay_status=1 and shop_id=".$param['shop_id']." and referee_id=".$param['referee_id'])->order('id desc')->select();
			if($commission_user_money){
				foreach($commission_user_money as $key=>$val){ 
				   $num=$num+$val['commission_total'];
				}
				$commission_user_money[0]['total']=$num;
			}
			return $commission_user_money;
	}	
	
    /**
	 * 支付（后台）
	 * @param array $param
	 * @return int
	 */
	public function ChannelmemberPay($param=array()){
			//修改结算信息改为支付信息
			$channelmember_list_second=$this->channelmember_list_second($param);
			foreach($channelmember_list_second as $key=>$val){ 
			   $data=array(); 
			   //判断是否调整金额
			   if($key==0){
			       $real_pay=0;
				   if($param['adjustment_type']==0){
					 $real_pay=$channelmember_list_second[0]['total']+$param['adjustment_money'];
				   }elseif($param['adjustment_type']==1){
					 $real_pay=$channelmember_list_second[0]['total']-$param['adjustment_money'];
				   }
				   $data['real_pay']=$real_pay; 
				   $data['adjustment_type']=$param['adjustment_type']; 
			       $data['adjustment_money']=$param['adjustment_money'];
				   $data['pay_desc']=$param['pay_desc'];
				   $data['pay_total']=$channelmember_list_second[0]['total']; 
			   }else{
			       $data['pay_total']=NULL; 
				   $data['pay_desc']=NULL; 
				   $data['real_pay']=NULL; 
				   $data['adjustment_money']=NULL;
			   }
			   $data['pay_status']=2;
			   $data['pay_time']=time();
			   $flag=M('commission_user_money')->where("pay_status=1 and shop_id=".$param['shop_id']." and referee_id=".$param['referee_id']." and id=".$val['id'])->data($data)->save();
			   //修改订单分成信息状态，并绑定结算信息
			    $flag=M('commission_order')->where("user_status=2 and shop_id=".$param['shop_id']." and referee_id=".$param['referee_id']." and commission_user_id=".$val['id'])->data(array('user_status'=>3))->save();

			}
			return 1;
	}
	
	
	 /**
	 * 已支付总额（后台）
	 * @param array $param
	 * @return int
	 */
	public function ChannelmemberPay_money($param=array()){
	   $sql="select sum(real_pay)as total  from  ddt_commission_user_money where pay_status=2 and shop_id=".$param['shop_id']." and referee_id=".$param['referee_id'];
	   $query = M()->query($sql);
	   return $query[0]['total']?$query[0]['total']:0;
	}
	
	/**
	 * 未确认消费的佣金总额（后台）
	 * @param array $param
	 * @return int
	 */
	public function Channelmember_no_money($param=array()){
	   $sql="select sum(referee_money)as total  from  ddt_commission_order where user_status=0 and shop_id=".$param['shop_id']." and referee_id=".$param['referee_id'];
	   $query = M()->query($sql);
	   return $query[0]['total']?$query[0]['total']:0;
	}
	
	

	/**
	 * 获取渠道分成列表的sql语句（后台）
	 * @param array $data
	 * @return string
	 */
	public  function channellistsql($data){
		//搜索字段
		$array=$data;
		//排序
		$order =isset($data['order'])?$data['order']:0; 
		//分页数据
		$limit =isset($data['limit']) ?  '  limit '.$data['limit']:"";
		//分组
        $group = " GROUP BY id";
		$where="";
		//构造sql子查询语句
		$sql_select = "  a.id   ";
		$sql_table = " ddt_commission_qd_money a  "; 
	    $sql_where = " a.pay_status=2 and   a.shop_id=".$data['shop_id'];
		$sql_group = "  a.id   ";
		$sql_order = "  a.id  desc ";  
		//排序
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
		}
		//关键词搜索
		$keyword=isset($data['key'])?$data['key']:"";
		//如果传来keyword
		if($keyword){
			  $sql_where.="  and ( a.pay_title like '%$keyword%'  ) "; 
		}
		//渠道
		if($array['channel_id']){
		  $sql_where.=" and a.channel_id =".$array['channel_id'];
		}
	   $sql=" SELECT ".$sql_select." FROM ".$sql_table." where ".$sql_where.$group." order by ".$sql_order.$limit; 
	   return $sql ; 
	}
	
	
	/**
	 * 获取推广分成列表的sql语句（后台）
	 * @param array $data
	 * @return string
	 */
	public  function extendlistsql($data){
		//搜索字段
		$array=$data;
		//排序
		$order =$data['order']; 
		//分页数据
		$limit =$data['limit'] == '' ? '' : '  limit '.$data['limit'];
		//分组
        $group = " GROUP BY id";
		$where="";
		//构造sql子查询语句
		$sql_select = "  a.id   ";
		$sql_table = " ddt_commission_user_money a  "; 
	    $sql_where = " a.pay_status=2 and   a.shop_id=".$data['shop_id'];
		$sql_group = "  a.id   ";
		$sql_order = "  a.id  desc ";  
		//排序
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
		}
		//关键词搜索
		$keyword=$data['key'];
		//如果传来keyword
		if($keyword){
			  $sql_where.="  and ( a.pay_title like '%$keyword%'  ) "; 
		}
		//推广id
		if($array['referee_id']){
		  $sql_where.=" and a.referee_id =".$array['referee_id'];
		}
	   $sql=" SELECT ".$sql_select." FROM ".$sql_table." where ".$sql_where.$group." order by ".$sql_order.$limit;
	   return $sql ; 
	}
}