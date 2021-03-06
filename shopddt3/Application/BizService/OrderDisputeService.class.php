<?php
/**
 * 售后服务
 * @author 梁健
 */
namespace BizService;
class OrderDisputeService extends BaseService
{
	/**
	 * 所有售后列表
	 * @return array
	 */
	public function dispute_list($data = array(), $sort = array(), $page = false){
		$sql = "SELECT a.*,b.order_sn,b.order_amount,b.goods_num,b.shipping_fee,c.goods_name,c.goods_price,d.user_name,d.nick_name";
		$query = " FROM ddt_orders_dispute AS a,ddt_orders AS b,ddt_orders_goods AS c,ddt_users AS d";
		$query .= " WHERE a.order_id = b.order_id AND a.order_id = c.order_id AND a.user_id = d.user_id AND a.shop_id = {$data['shop_id']}";
		if($data['type'] != 99){
			$query .= " AND a.type = {$data['type']}";
		}
		if($data['status'] != 99){
			$query .= " AND a.status = {$data['status']}";
		}
		if(!empty($data['begin_time']) && !empty($data['end_time'])){
			$query .= " AND a.add_time BETWEEN ".strtotime($data['begin_time'])." AND ".strtotime($data['end_time']);
		}elseif(!empty($data['begin_time'])){
			$query .= " AND a.add_time >= ".strtotime($data['begin_time']);
		}elseif(!empty($data['end_time'])){
			$query .= " AND a.add_time <= ".strtotime($data['end_time']);
		}
		if(!empty($data['key'])){
			$query .= " AND (b.order_sn LIKE '%{$data['key']}%' OR c.goods_name LIKE '%{$data['key']}%' OR d.user_name LIKE '%{$data['key']}%' OR d.nick_name LIKE '%{$data['key']}%')";
		}
		if(!empty($sort['order']) && !empty($sort['sort'])){
			$a_array = array('type', 'content', 'tel', 'add_time', 'status');
			$b_array = array('order_sn', 'order_amount', 'goods_num', 'shipping_fee');
			$c_array = array('goods_name', 'goods_price');
			$d_cols = 'user_name';
			if(in_array($sort['order'], $a_array)){
				$query .= " ORDER BY a.{$sort['order']} {$sort['sort']}";
			}elseif(in_array($sort['order'], $b_array)){
				$query .= " ORDER BY b.{$sort['order']} {$sort['sort']}";
			}elseif(in_array($sort['order'], $c_array)){
				$query .= " ORDER BY c.{$sort['order']} {$sort['sort']}";
			}elseif($sort['order'] == $d_cols){
				$query .= " ORDER BY d.{$sort['order']} {$sort['sort']}";
			}else{
				$query .= " ORDER BY a.add_time DESC";
			}
		}else{
			$query .= " ORDER BY a.add_time DESC";
		}
		if($page){
			$count = "SELECT count(1) AS count".$query;
			$count = M()->query($count);
			$count = $count[0]['count'];
		    $page = new \Think\Page($count,PAGE_SIZE);
		    //$page = new \Think\Page($count, 2);
		    $show = $page->show();
			$query .= " LIMIT ".$page->firstRow.",".$page->listRows;
		}
		$sql .= $query;
		$list = M()->query($sql);
		if($page){
			return array('list'=>$list, 'page'=>$show);
		}else{
			return array('list'=>$list);
		}
	}
	
	/**
	 * 售后详情
	 * @return array
	 */
	public function dispute_info($order_id = 0){
		if(!$order_id){
			return false;
		}
		$query = "SELECT * FROM `ddt_orders_dispute` WHERE `order_id` = $order_id";
		return M()->query($query);
	}
	
	/**
	 * 添加售后信息
	 * @param $data 售后信息
	 * @return boolen|int
	 */
	public function add_dispute($data){
		if($data){
			$count = M()->table('ddt_orders_dispute')->where("order_id = {$data['order_id']}")->count();
			if($count > 0){
				return false;
			}else{
				$query = "INSERT INTO `ddt_orders_dispute` (`user_id`,`shop_id`,`order_id`,`status`,`type`,`content`,`tel`,`add_time`,`update_time`) ";
				$query .= "VALUES (%d, %d, %d, %d, %d, '%s', '%s', %d, %d)";
				$dispute_data = array(
					$data['user_id'],
					$data['shop_id'],
					$data['order_id'],
					0,
					$data['type'],
					$data['content'],
					$data['tel'],
					time(),
					time()
				);
				return M()->execute($query, $dispute_data);
			}
		}else{
			return false;
		}
	}
	
	/**
	 * 修改售后信息
	 */
	public function change_dispute($data, $id){
		$keys = '';
		foreach ($data as $key=>$val){
			$keys .= "$key='$val',";
		}
		$keys = substr($keys, 0,-1);
		$sql = "UPDATE ddt_orders_dispute SET ".$keys." WHERE id =".intval($id);
		return M()->execute($sql);
	}
}