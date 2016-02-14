<?php
namespace Admin\Controller;

class IndexController extends BaseController {
    

    public function get_mobile_area($mobile){
    //根据淘宝的数据库调用返回值
    $url = "http://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=".$mobile."&t=".time();

    $content = file_get_contents($url);
	$content = iconv('GB2312', 'UTF-8', $content);
	$array = explode('=', $content);
	$find = array('"',"'",'{','}','\r','\n','\r\n');
	$json = str_replace($find, '', trim($array[1]));
	$array = explode(',', $json);
	$info = array();
	foreach($array as $val){
		$temp = explode(':', $val);
		$key = trim($temp[0]);
		$info[$key] = trim($temp[1]);
	}
	return $info;
}
}