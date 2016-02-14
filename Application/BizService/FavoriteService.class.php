<?php

namespace BizService;

/**
 * 商品收藏、商城收藏
 *
 * @author 谢林
 */
class FavoriteService extends BaseService {

	/**
	 * @param $uid 收藏用户ID
	 * @param $fav_id 收藏ID
	 * @param int $type 收藏类型 默认0（0现金商品 1积分商品 9商城）
	 * @return array
	 */
	public function addFavorite($shop_id,$uid,$fav_id,$type=0){
		if(empty($uid) || empty($fav_id)){
			return false;
		}
		$time = time();
		$sql = "INSERT INTO ddt_favorites (shop_id,user_id,fav_id,fav_type,fav_time) VALUES (%d,%d,%d,%d,%d)";
		return M()->execute($sql,$shop_id,$uid,$fav_id,$type,$time);
	}

	/**
	 * 是否收藏
	 * @param $uid
	 * @param $fav_id
	 * @param int $type （0现金商品 1积分商品 9商城）
	 * @return bool
	 */
	public function getFavorite($shop_id,$uid,$fav_id=0,$type=0){
		$query = "SELECT 1 FROM ddt_favorites WHERE user_id=%d AND fav_id=%d AND fav_type=%d AND shop_id=%d";
		$row=array();
		$row = M()->query($query,$uid,$fav_id,$type,$shop_id);
		if($row&&$row[0]){
			return true;
		}
		return false;
	}
	/**
	 * @param $uid 收藏用户ID
	 * @param $fav_id 收藏ID
	 * @param int $type 收藏类型 默认0（0现金商品 1积分商品 9商城）
	 * @return array
	 */
	public function delFavorite($shop_id,$uid,$fav_id,$type=0){
		if(empty($uid) || empty($fav_id)){
			return false;
		}
		$sql = "DELETE FROM ddt_favorites WHERE user_id = %d AND fav_id = %d AND fav_type = %d AND shop_id=%d";
		return M()->execute($sql,$uid,$fav_id,$type,$shop_id);
	}
	/**
	 * @param $uid 收藏ID
	 * @param int $type 1商品 2商城
	 * @param bool|true $ispagenation 是否分页
	 * @return mixed|\multitype
	 */
	public function getFavorites($shop_id,$uid,$type=1,$ispagenation=true){
		//根据收藏类型不同组装查询SQL
		if($type==2){
			//@todo 收藏商家暂不实现
		}else{
			//现金商品
			$query = "SELECT a.*,b.goods_id,b.goods_name,b.store_name,b.goods_price,b.goods_marketprice,b.goods_storage,b.goods_image,b.goods_state FROM ddt_favorites as a INNER JOIN ddt_goods as b ON a.fav_id=b.goods_id WHERE a.fav_type = 0 AND a.user_id=%d AND a.shop_id=%d";
			//积分商品
			$query1 = "SELECT a.*,b.goods_id,b.goods_name,b.store_name,b.goods_price,b.goods_marketprice,b.goods_storage,b.goods_image,b.goods_state FROM ddt_favorites as a INNER JOIN ddt_integral_goods as b ON a.fav_id=b.goods_id WHERE a.fav_type = 1 AND a.user_id=%d AND a.shop_id=%d";

			$fav_list = M()->query($query,$uid,$shop_id);
			$fav_list1 = M()->query($query1,$uid,$shop_id);
			array_merge($fav_list,$fav_list1);
			$fav_list = arraySort($fav_list,'fav_time',SORT_DESC);
		}
		if($ispagenation){
			return array_page($fav_list);
		}else{
			return $fav_list;
		}
	}
}