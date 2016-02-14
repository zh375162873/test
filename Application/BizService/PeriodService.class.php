<?php

namespace BizService;

/**
 * 定时开枪
 *
 * @author 张辉
 */
class PeriodService extends BaseService
{


    /* 获得定时段抢购的商品是否开放
    *  @return $period_info array
    *  ['shut'] 0,开放购买;1,未开始;2,已结束;  ['begin'] 距开始时间  ['end'] 距结束时间
    */
    public function get_period_sales($goods_id)
    {
        //限时抢购，在活动时间之内,限时有效,活动时间之外不影响购买
        $goods_period1 = M('goods_period_sale')->where(" type=1 and on_using=1 and goods_id = '$goods_id' ")->find();
		//如果有的话
        if ($goods_period1) {
            $period_info = array();
            $t = time();
            $start_time = $goods_period1['begin_date'] + $goods_period1['begin_seconds'];
            $end_time = $goods_period1['end_date'] + $goods_period1['end_seconds'];
            if ($t < $start_time) {
                $period_info['start_time'] = $start_time;
                $period_info['end_time'] = $end_time;
                $period_info['shut'] = 1;
                $period_info['goods_id'] = $goods_period1['goods_id'];
                $period_info['begin'] = $start_time - $t;
                $period_info['end'] = $end_time - $start_time;
            } elseif ($t > $start_time && $t < $end_time) {
                $period_info['start_time'] = $start_time;
                $period_info['end_time'] = $end_time;
                $period_info['shut'] = 0;
                $period_info['goods_id'] = $goods_period1['goods_id'];
                $period_info['end'] = $end_time - $t;
            } elseif ($t > $end_time) {
                $period_info['start_time'] = $start_time;
                $period_info['end_time'] = $end_time;
                $period_info['shut'] = 2;
                $period_info['end'] = 0;
                $period_info['goods_id'] = $goods_period1['goods_id'];
            }
            return $period_info;
        } else {
            //分时段抢购在活动时间之内,限时有效,活动时间之外不影响购买
            $goods_period = M('goods_period_sale')->where("  type=2 and on_using=1  and goods_id = '$goods_id' and begin_date < " . time() . " and end_date >" . time())->select();
			$goods_num=0;
            //如果该商品存在定时段销售活动
            if ($goods_period) {
                $period_info = array();//数组记录定时段的信息
                $now_seconds = time() - strtotime(date('Y-m-d', strtotime('+0 day')));
                $nowday1 = date('Y-m-d', time());
                $nowday2 = strtotime($nowday1);
                foreach ($goods_period as $key => $value) {
                    //当前时间在设定购买时间内，开放购买,直接返回状态shut=0和最大剩余时间end_seconds
                    if ($now_seconds >= $value['begin_seconds'] && $now_seconds <= $value['end_seconds']) {
					    //判断在抢购过程中，当前购买的数量是否达到设定的库存
						if($value['goods_limit']>0){
							$start_time = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['begin_seconds'];
							$end_time = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
							//查找抢购时间段内，购买商品数量，包括未付款和已付款的。
							$order_goodsnum=M()->query("select sum(a.goods_num) as num from ddt_orders_goods as a ,ddt_orders as b  where b.order_id=a.order_id  and  a.goods_id=".$value['goods_id']." and b.add_time> ".$start_time." and b.add_time <".$end_time);
							//计算还剩多少库存可以抢购
							$goods_storage=M()->query("select goods_storage from ddt_goods where goods_id= ".$goods_id);
							if($goods_storage[0]['goods_storage']>=$value['goods_limit']){
							  if($value['goods_limit']>=$order_goodsnum[0]['num']){
							   $goods_num=$value['goods_limit']-$order_goodsnum[0]['num'];
							  }else{
							   $goods_num=0;
							  } 
							}else{
							   if($goods_storage[0]['goods_storage']>0){
							     $goods_num=$goods_storage[0]['goods_storage'];
							   }else{
							     $goods_num=0;
							   }	 
							}
							//当时间段内的商品超过的设定的数量就关闭抢购
							if($goods_num==0){
							    //查询还有多少未付款的
								$start_time = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['begin_seconds'];
							$end_time = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
								$order_status=M()->query("select count(*) as num from ddt_orders_goods as a ,ddt_orders as b  where b.order_id=a.order_id and b.order_status=0 and  a.goods_id=".$value['goods_id']." and b.add_time> ".$start_time." and b.add_time <".$end_time);
								$period_info['start_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['begin_seconds'];
								$period_info['end_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
								$period_info['shut'] = 2;
								$period_info['goods_id'] = $value['goods_id'];
								$period_info['end'] = 0;
								$period_info['goods_limit'] = $value['goods_limit'];
								$period_info['goods_num'] = $goods_num;
								$period_info['order_status0'] = $order_status[0]['num'];
								return $period_info;
							}
						}
						//当库存已经没有了得时候就关闭抢购
						$goods_storage=M()->query("select goods_storage from ddt_goods where goods_id= ".$goods_id);
						if($goods_storage[0]['goods_storage']==0){
						    $period_info['start_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['begin_seconds'];
							$period_info['end_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
							$period_info['shut'] = 2;
							$period_info['goods_id'] = $value['goods_id'];
							$period_info['end'] = 0;
							$period_info['goods_limit'] = $value['goods_limit'];
						    $period_info['goods_num'] = $goods_num;
							return $period_info;
						} 
						
                        $period_info['shut'] = 0;
                        //设置剩余时间
                        $period_info['end'] = 0;
                        //得到最大剩余时间
                        if ($period_info['end'] < $value['end_seconds'] - $now_seconds) {
                            $period_info['start_time'] = $nowday2 + $value['begin_seconds'];
                            $period_info['end_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
                            $period_info['id'] = $value['id'];
                            $period_info['goods_id'] = $value['goods_id'];
                            $period_info['end'] = $value['end_seconds'] - $now_seconds;
							$period_info['goods_limit'] = $value['goods_limit'];
						    $period_info['goods_num'] = $goods_num;
                        }
                    }
                }
                if ($period_info['end']) {
                    return $period_info;
                }
                foreach ($goods_period as $key => $value) {
                    //当前时间小于开始时间，抢购之前，封闭购买,直接返回状态shut=1和最小开始时间begin_seconds
                    if ($now_seconds < $value['begin_seconds']) {
					       //计算还剩多少库存可以抢购
							$goods_storage=M()->query("select goods_storage from ddt_goods where goods_id= ".$goods_id);
							if($goods_storage[0]['goods_storage']>=$value['goods_limit']){
							   $goods_num=$value['goods_limit'];
							}else{
							     $goods_num=$goods_storage[0]['goods_storage']; 
							}
					
                        $period_info['shut'] = 1;
                        //得到最小开始时间
                        //初始化还有一天时间
                        $period_info['begin'] = 86400;
                        if ($period_info['begin'] > $value['begin_seconds'] - $now_seconds) {
                            $period_info['start_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['begin_seconds'];
                            $period_info['end_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
                            $period_info['id'] = $value['id'];
                            $period_info['goods_id'] = $value['goods_id'];
                            $period_info['begin'] = $value['begin_seconds'] - $now_seconds;
                            $period_info['end'] = $value['end_seconds'] - $value['begin_seconds'];
							$period_info['goods_limit'] = $value['goods_limit'];
							$period_info['goods_num'] = $goods_num;
                        }
                    }
                }
                if ($period_info['begin']) {
                    return $period_info;
                }
                foreach ($goods_period as $key => $value) {
                    //当前时间小于开始时间或者大于结束时间，抢购之后，封闭购买,直接返回状态shut=2和最小开始时间end_seconds
                    if ($now_seconds > $value['end_seconds']) {
                        $period_info['start_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['begin_seconds'];
                        $period_info['end_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
                        $period_info['shut'] = 2;
                        $period_info['goods_id'] = $value['goods_id'];
                        $period_info['end'] = 0;
						$period_info['goods_limit'] = $value['goods_limit'];
						$period_info['goods_num'] = $goods_num;
                        return $period_info;
                    }
                }
            }
        }
    }
	
	/* 获得定时段抢购的商品是否开放(有限购数量)
    *  @return $period_info array
    *  ['shut'] 0,开放购买;1,未开始;2,已结束;-1:无库存，  ['begin'] 距开始时间  ['end'] 距结束时间
    */
    public function get_period_sales_limit($goods_id,$userid)
    {
		//限时抢购，在活动时间之内,限时有效,活动时间之外不影响购买
        $goods_period1 = M('goods_period_sale')->where(" type=1 and on_using=1 and goods_id = '$goods_id' ")->find();
		//如果有的话
        if ($goods_period1) {
            $period_info = array();
            $t = time();
            $start_time = $goods_period1['begin_date'] + $goods_period1['begin_seconds'];
            $end_time = $goods_period1['end_date'] + $goods_period1['end_seconds'];
            if ($t < $start_time) {
                $period_info['start_time'] = $start_time;
                $period_info['end_time'] = $end_time;
                $period_info['shut'] = 1;
                $period_info['goods_id'] = $goods_period1['goods_id'];
                $period_info['begin'] = $start_time - $t;
                $period_info['end'] = $end_time - $start_time;
            } elseif ($t > $start_time && $t < $end_time) {
                $period_info['start_time'] = $start_time;
                $period_info['end_time'] = $end_time;
                $period_info['shut'] = 0;
                $period_info['goods_id'] = $goods_period1['goods_id'];
                $period_info['end'] = $end_time - $t;
            } elseif ($t > $end_time) {
                $period_info['start_time'] = $start_time;
                $period_info['end_time'] = $end_time;
                $period_info['shut'] = 2;
                $period_info['end'] = 0;
                $period_info['goods_id'] = $goods_period1['goods_id'];
            }
            return $period_info;
        } else {
            //分时段抢购在活动时间之内,限时有效,活动时间之外不影响购买
            $goods_period = M('goods_period_sale')->where("  type=2 and on_using=1  and goods_id = '$goods_id' and begin_date < " . time() . " and end_date >" . time())->select();
            //如果该商品存在定时段销售活动
            if ($goods_period) {
                $period_info = array();//数组记录定时段的信息
                $now_seconds = time() - strtotime(date('Y-m-d', strtotime('+0 day')));
                $nowday1 = date('Y-m-d', time());
                $nowday2 = strtotime($nowday1);
                foreach ($goods_period as $key => $value) {
                    //当前时间在设定购买时间内，开放购买,直接返回状态shut=0和最大剩余时间end_seconds
                    if ($now_seconds >= $value['begin_seconds'] && $now_seconds <= $value['end_seconds']) {
					    //判断在抢购过程中，当前购买的数量是否达到设定的库存
						if($value['goods_limit']>0){
							$start_time = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['begin_seconds'];
							$end_time = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
							$goodsnum=M()->query("select sum(a.goods_num) as num from ddt_orders_goods as a ,ddt_orders as b  where b.order_id=a.order_id  and b.order_status>0 and  a.goods_id=".$value['goods_id']." and b.add_time> ".$start_time." and b.add_time <".$end_time);
							print_r($goodsnum);
							//当时间段内的商品超过的设定的数量就关闭抢购
							if($goodsnum[0]['num']>=$value['goods_limit']){
								$period_info['start_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['begin_seconds'];
								$period_info['end_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
								$period_info['shut'] = 2;
								$period_info['goods_id'] = $value['goods_id'];
								$period_info['end'] = 0;
								return $period_info;
							}
						}
						//当库存已经没有了得时候就关闭抢购
						$goods_storage=M()->query("select goods_storage from ddt_goods where goods_id= ".$goods_id);
						if($goods_storage[0]['goods_storage']==0){
						    $period_info['start_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['begin_seconds'];
							$period_info['end_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
							$period_info['shut'] = 2;
							$period_info['goods_id'] = $value['goods_id'];
							$period_info['end'] = 0;
							return $period_info;
						}
						
						//以上都么有问题呢的话，抢购开始
						$period_info['shut'] = 0;
						//设置剩余时间
						$period_info['end'] = 0;
						//得到最大剩余时间
						if ($period_info['end'] < $value['end_seconds'] - $now_seconds) {
							$period_info['start_time'] = $nowday2 + $value['begin_seconds'];
							$period_info['end_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
							$period_info['id'] = $value['id'];
							$period_info['goods_id'] = $value['goods_id'];
							$period_info['end'] = $value['end_seconds'] - $now_seconds;
						}
						
                    }
                }
                if ($period_info['end']) {
                    return $period_info;
                }
                foreach ($goods_period as $key => $value) {
                    //当前时间小于开始时间，抢购之前，封闭购买,直接返回状态shut=1和最小开始时间begin_seconds
                    if ($now_seconds < $value['begin_seconds']) {
                        $period_info['shut'] = 1;
                        //得到最小开始时间
                        //初始化还有一天时间
                        $period_info['begin'] = 86400;
                        if ($period_info['begin'] > $value['begin_seconds'] - $now_seconds) {
                            $period_info['start_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['begin_seconds'];
                            $period_info['end_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
                            $period_info['id'] = $value['id'];
                            $period_info['goods_id'] = $value['goods_id'];
                            $period_info['begin'] = $value['begin_seconds'] - $now_seconds;
                            $period_info['end'] = $value['end_seconds'] - $value['begin_seconds'];
                        }
                    }
                }
                if ($period_info['begin']) {
                    return $period_info;
                }
                foreach ($goods_period as $key => $value) {
                    //当前时间小于开始时间或者大于结束时间，抢购之后，封闭购买,直接返回状态shut=2和最小开始时间end_seconds
                    if ($now_seconds > $value['end_seconds']) {
                        $period_info['start_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['begin_seconds'];
                        $period_info['end_time'] = strtotime(date('Y-m-d', strtotime('+0 day'))) + $value['end_seconds'];
                        $period_info['shut'] = 2;
                        $period_info['goods_id'] = $value['goods_id'];
                        $period_info['end'] = 0;
                        return $period_info;
                    }
                }
            }
        }
    }



}