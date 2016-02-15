<?php
/**
 * 收获地址
 * @author 梁健
 */
namespace BizService;
class AddressService extends BaseService
{
	/**
	 * 获取区域列表
	 * @param $region_id 区域ID
	 * @param $is_sub true为获取子列表,false为获取区域信息
	 * @return boolen|array
	 */
	public function get_region_list($region_id, $is_sub = true){
		if(!$region_id){
			return false;
		}
		if($is_sub){
			$query = "SELECT `region_id`,`region_name` FROM `ddt_region` WHERE parent_id=%d";
			$region_list = M()->query($query, $region_id);
			return $region_list;
		}else{
			$query = "SELECT `region_id`,`region_name` FROM `ddt_region` WHERE region_id=%d";
			$region_list = M()->query($query, $region_id);
			return $region_list[0];
		}
	}
	
	/**
	 * 获取用户收货地址
	 * @param $user_id 用户ID
	 * @return boolen|array
	 */
	public function get_address($user_id){
		if(!$user_id){
			return false;
		}else{
			$query = "SELECT * FROM `ddt_user_address` WHERE user_id=%d ORDER BY id DESC";
			$addr_list = M()->query($query, $user_id);
			return $addr_list;
		}
	}
	
	/**
	 * 获取收货地址详情
	 * @param $address_id 收货地址ID
	 * @return boolen|array
	 */
	public function get_address_by_id($address_id){
		if(!$address_id){
			return false;
		}else{
			$query = "SELECT * FROM `ddt_user_address` WHERE id=%d";
			$addr_info = M()->query($query, $address_id);
			return $addr_info[0];
		}
	}
	
	/**
	 * 添加收获地址
	 * @param $data 收货地址数据
	 * @return boolen|int
	 */
	public function add_address($data){
		if(!$data){
			return false;
		}else{
			$count = M()->table('ddt_user_address')->where("user_id=".session('userId'))->count();
			if($count > 20){
				return false;
			}else{
				$query = "INSERT INTO `ddt_user_address` (`user_id`,`consignee`,`province`,`city`,`district`,`address`,`tel`,`add_time`) ";
				$query .= "VALUES (%d, '%s', %d, %d, %d, '%s', '%s', %d)";
				$address_data = array(
					session('userId'),
					$data['consignee'],
					$data['province'],
					$data['city'],
					$data['district'],
					$data['address'],
					$data['tel'],
					time()
				);
				return M()->execute($query, $address_data);
			}
		}
	}
	
	/**
	 * 删除收获地址
	 * @param $address_id 收获地址ID
	 * @return boolen|int
	 */
	public function rm_address($address_id){
		if(!$address_id){
			return false;
		}
		$query = "SELECT 1 FROM `ddt_user_address` WHERE `id`=%d AND `user_id`=%d";
		$address = M()->query($query, $address_id, session('userId'));
		if($address){
			$query = "DELETE FROM `ddt_user_address` WHERE id=%d";
			return M()->execute($query, $address_id);
		}else{
			return false;
		}
	}
}