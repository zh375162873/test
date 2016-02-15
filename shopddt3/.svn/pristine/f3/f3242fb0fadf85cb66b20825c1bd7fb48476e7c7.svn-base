<?php
namespace Admin\Model;
use Think\Model;

class RegionModel extends Model{
	protected $tableName = 'region';
	/**
	 * 得到地区id和地区名
	 * @param $type			地区类型
	 * @param $parent		上级地区
	 * @return array
	 */
	public function getRegions($type = 0 ,$parent = 0)
	{
		$data = $this->where("region_type = '$type' and parent_id = '$parent'")->field('region_id, region_name')->select();
	    return $data;
	}
}

?>