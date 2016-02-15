<?php

namespace BizService;

/**
 * Service 基类
 *
 * @author 谢林
 */
class BaseService {

	protected function ok($id = null) {
		if ($id) {
			return array(
					"success" => true,
					"id" => $id
			);
		} else {
			return array(
					"success" => true
			);
		}
	}

	protected function bad($msg) {
		return array(
				"success" => false,
				"msg" => $msg
		);
	}

	protected function todo($info = null) {
		if ($info) {
			return array(
					"success" => false,
					"msg" => "TODO: 功能还没开发, 附加信息：$info"
			);
		} else {
			return array(
					"success" => false,
					"msg" => "TODO: 功能还没开发"
			);
		}
	}

	protected function sqlError() {
		return $this->bad("数据库错误，请联系管理员");
	}




	/**
	 * 当用户不在线的时候，返回的提示信息
	 */
	protected function notOnlineError() {
		return $this->bad("当前用户已经退出系统，请重新登录");
	}

	/**
	 * 返回空列表
	 */
	protected function emptyResult() {
		return array();
	}

	/**
	 * 验证日期是否是正确的Y-m-d格式
	 * @param string $date
	 * @return boolean true: 是正确的格式
	 */
	protected function isDateValid($date) {
		$dt = strtotime($date);
		if (! $dt) {
			return false;
		}
		
		return date("Y-m-d", $dt) == $date;
	}
	/**
	 * 数组排序
	 * @param array $arr 排序数组
	 * @param string $keys 排序键值
	 * @param string $type 正序逆序
	 * @return array $new_array 排序后数组
	 */
	public function array_sort($arr, $keys, $type = 'ASC') {
        $keysvalue = $new_array = array ();
        foreach ( $arr as $k => $v ) {
            $keysvalue [$k] = $v [$keys];
        }
        if ($type == 'ASC') {
            asort ( $keysvalue );
        } else {
            arsort ( $keysvalue );
        }
        reset ( $keysvalue );
        $count = 0;
        foreach ( $keysvalue as $k => $v ) {
            $new_array [$count ++] = $arr [$k];
        }
        return $new_array;
    }
}
