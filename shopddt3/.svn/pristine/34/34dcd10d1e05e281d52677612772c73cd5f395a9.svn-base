<?php

namespace BizService;

/**
 * 摇一摇
 *
 * @author 谢林
 */
class ShakeService extends BaseService {

	/**
	 * 概率算法
	 * @param array $probability
	 * @return integer|string
	 */
	public function get_rand($probability)
	{
		// 概率数组的总概率精度
		$max = array_sum($probability);
		//echo "max==".$max."<hr>";
		foreach ($probability as $key => $val)
		{
			$rand_number = mt_rand(1, $max);
//            echo "max==".$max."<br>";
//            echo "rand_number==".$rand_number."<br>";
//            echo "val==".$val."<br>";
			if ($rand_number <= $val)
			{
				return $key;
			}
			else
			{
				$max -= $val;
			}
		}
	}

	/**
	 * 获取当前登录人上一次摇奖时间
	 * @param null $userid
	 * @return int|mixed
	 */
	public function get_last_yiy_time($userid=null){
		if($userid){
			$key = $userid."last_yiy_time";
		}else{
			$key = session("userId")."last_yiy_time";
		}
		return S(md5($key));
	}

	/**
	 * 摇奖间隔时间
	 * @return int 秒数
	 */
	public function get_yiy_wait_time(){
		return 10*60;
	}

	/**
	 * 获取抽奖奖品
	 * @return array
	 */
	public function get_prize_goods(){
		// 概率比例prize奖项名称，prob中奖概率（总和越大，概率越精确），type奖品类型（0,其他，1普通商品，2积分）
		return $data = array(
			array(
				"goods_id"=>1,"prize"=>"平板电脑","prob"=>1,"type"=>1
			),
			array(
				"goods_id"=>2,"prize"=>"数码相机","prob"=>2,"type"=>1
			),
			array(
				"goods_id"=>3,"prize"=>"音箱设备","prob"=>3,"type"=>1
			),
			array(
				"goods_id"=>4,"prize"=>"8G优盘","prob"=>10,"type"=>1
			),
			array(
				"goods_id"=>5,"prize"=>"10Q币","prob"=>30,"type"=>1
			),
			array(
				"goods_id"=>6,"prize"=>"积分赠送","prob"=>100,"type"=>2
			),
			array(
				"goods_id"=>7,"prize"=>"下次没准就能中噢","prob"=>9845,"type"=>0
			)
		);
	}
}