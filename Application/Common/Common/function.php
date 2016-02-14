<?php
/**
 * Created by ddt.
 * User: 谢林
 * Date: 2015/9/21 10:54
 * Description:公共函数库
 */


/**
 * 价格格式化,保留不为零的数字
 *
 * @param int	$price
 * @return string	$price_format
 */
function price_format($price) {
    //$price_format	= number_format($price,2,'.','');
    $price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\1\2\3', number_format($price, 2, '.', ''));
    if (substr($price, -1) == '.')
    {
        $price = substr($price, 0, -1);
    }
    return $price;
}

//mysql like 字符转义
function mysql_like_quote($str)
{
    return addslashes(strtr($str, array("\\\\" => "\\\\\\\\", '_' => '\_', '%' => '\%', "\'" => "\\\\\'")));
}
/**
 * 设置cookie
 *
 * @param string $name cookie 的名称
 * @param string $value cookie 的值
 * @param int $expire cookie 有效周期
 * @param string $path cookie 的服务器路径 默认为 /
 * @param string $domain cookie 的域名
 * @param string $secure 是否通过安全的 HTTPS 连接来传输 cookie,默认为false
 */
function setddtCookie($name, $value, $expire='3600', $path='', $domain='', $secure=false){
    if (empty($path)) $path = '/';
    if (empty($domain)) $domain = '';
    $name = strtoupper(substr(md5(MD5_KEY),0,4)).'_'.$name;
    $expire = intval($expire)?intval($expire):3600;
    $result = setcookie($name, $value, time()+$expire, $path, $domain, $secure);
    $_COOKIE[$name] = $value;
}
/**
 * 对多维数组进行排序
 * @param $multi_array 数组
 * @param $sort_key需要传入的键名
 * @param $sort排序类型
 */
function arraySort($multi_array, $sort_key, $sort = SORT_ASC)
{
    if (is_array($multi_array)) {
        foreach ($multi_array as $row_array) {
            if (is_array($row_array)) {
                $key_array[] = $row_array[$sort_key];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_array, $sort, $multi_array);
    return $multi_array;
}

/**
 * 分页
 *
 * @param int $count
 *            总数
 * @param string $sql
 *            查询sql
 * @param array $param
 *            查询参数
 * @return multitype:unknown Ambigous <string, mixed>
 */
function mypage($count, $sql, $param = array())
{
    $model = M();
    $p = new Think\Page ($count, PAGE_SIZE);
    $p->setConfig('header', 'Totle');
    //$p->setConfig ( 'first', 'First' );
    $p->setConfig('prev', '<<');
    $p->setConfig('next', '>>');
    //$p->setConfig ( 'last', 'Last' );
    $p->setConfig('theme', ' <ul  class="pagination"> %upPage% %prePage% %linkPage% %downPage%</ul> <p class="page-info"> %totalRow% %header% %nowPage%/%totalPage% page </p>');
    //$p->setConfig('theme', '<li><a>%totalRow% %header%</a></li> %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end% ');
    $list = $model->query($sql . " LIMIT " . $p->firstRow . "," . $p->listRows);
    foreach ($param as $key => $val) {
        $p->parameter .= "$key=" . urlencode($val) . '&';
    }
    return array(
        'list' => $list,
        'page' => $p->show(),
        'count' => $count
    );
}

/**
 * 数组分页
 * @param array $array
 * @param int $rows
 * @return multitype:
 */
function array_page($array)
{
    $count = count($array);
    $Page = new Think\Page($count, PAGE_SIZE);
    $Page->setConfig('header', 'Totle');
    //$p->setConfig ( 'first', 'First' );
    $Page->setConfig('prev', '<<');
    $Page->setConfig('next', '>>');
    //$p->setConfig ( 'last', 'Last' );
    $Page->setConfig('theme', ' <ul  class="pagination"> %upPage% %prePage% %linkPage% %downPage%</ul> <p class="page-info"> %totalRow% %header% %nowPage%/%totalPage% page </p>');
    //$p->setConfig('theme', '<li><a>%totalRow% %header%</a></li> %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end% ');
    $list = array_slice($array, $Page->firstRow, $Page->listRows);
    return array(
        'list' => $list,
        'page' => $Page->show(),
        'count' => $count
    );
}


/**
 * 将字符串转换为数组
 *
 * @param    string $data 字符串
 * @return    array    返回数组格式，如果，data为空，则返回空数组
 */
function string2array($data)
{
    if ($data == '') return array();
    @eval("\$array = $data;");
    return $array;
}

/**
 * 发送HTTP请求方法
 * @param  string $url 请求URL
 * @param  array $params 请求参数
 * @param  string $method 请求方法GET/POST
 * @return array  $data   响应数据
 */
function curl_http($url, $params, $method = 'GET', $header = array(), $multi = false)
{
    $opts = array(
        CURLOPT_TIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => $header
    );

    /* 根据请求类型设置特定参数 */
    switch (strtoupper($method)) {
        case 'GET':
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        case 'POST':
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
            throw new Exception('不支持的请求方式！');
    }
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) throw new Exception('请求发生错误：' . $error);
    return $data;
}

/**
 * 将数组转换为字符串
 *
 * @param    array $data 数组
 * @param    bool $isformdata 如果为0，则不使用new_stripslashes处理，可选参数，默认为1
 * @return    string    返回字符串，如果，data为空，则返回空
 */
function array2string($data, $isformdata = 1)
{
    if ($data == '') return '';
    if ($isformdata) $data = new_stripslashes($data);
    return var_export($data, TRUE);
}

/**
 * 返回经addslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_addslashes($string)
{
    if (!is_array($string)) return addslashes($string);
    foreach ($string as $key => $val) $string[$key] = new_addslashes($val);
    return $string;
}

/**
 * 返回经stripslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_stripslashes($string)
{
    if (!is_array($string)) return stripslashes($string);
    foreach ($string as $key => $val) $string[$key] = new_stripslashes($val);
    return $string;
}

/**
 * 获取当前后台登录人的代理和商城唯一标识
 * @return array
 */
function get_shop_proxy()
{
    return array("proxy_id" => session("proxyId"), "shop_id" => session("shopId"));
}

/**
 * 获取代理商信息
 * @param $proxy_id 代理ID
 * @return array proxy_id 代理ID，shop_id 商城ID，proxy_name 代理名称
 */
function get_proxy_info($proxy_id)
{
    $url = C('API_URL') . "/index.php/Home/Api/proxyInfo";
    $params['proxy_id'] = $proxy_id;
    $response = json_decode(curl_http($url, $params), true);
    if ($response['code'] == 0) {
        return null;
    }
    //dump($response);
    return array("proxy_id" => $response['data']['id'], "proxy_name" => $response['data']['proxy_name'], "location" => $response['data']['location'], "province" => $response['data']['province_id'], "city" => $response['data']['city_id'], "district" => $response['data']['area_id']);
}

/**
 * 获取某个代理下的商家信息
 * @param $proxy_id 代理ID
 * @param $condition array $condition['name'] 商家名称关键字
 * @return array merchant_id 商家ID，merchant_name 商家名称，province 所在省，city 所在市，district 所在区，address 具体地址，longitude 经度，latitude 纬度
 */
function get_merchant_by_proxy($proxy_id, $condition)
{
    $url = C('API_URL') . "/index.php/Home/Api/proxyBizlist";
    $params['proxy_id'] = $proxy_id;
    if ($condition['name']) {
        $params['bizname'] = $condition['name'];
    }
    $response = json_decode(curl_http($url, $params), true);
    if ($response['code'] == 0) {
        return null;
    }
    $merchants = array();
    $i = 0;
    foreach ($response['data'] as $merchant) {
        $merchants[$i]['merchant_id'] = $merchant['id'];
        $merchants[$i]['merchant_name'] = $merchant['name'];
        $merchants[$i]['address'] = $merchant['address'];
        $merchants[$i]['province'] = $merchant['province_id'];
        $merchants[$i]['city'] = $merchant['city_id'];
        $merchants[$i]['district'] = $merchant['area_id'];
        $mer_location = explode(",", $merchant['location']);
        $merchants[$i]['longitude'] = trim($mer_location[0]);
        $merchants[$i]['latitude'] = trim($mer_location[1]);
        $i++;
    }
//    $merchants = array(
//        array("merchant_id" => 1000, "merchant_name" => "民乐园万达广场", "province" => "陕西", "city" => "西安", "district" => "新城区", "address" => "解放路中段111号", "longitude" => "108.95862 ", "latitude" => "34.26519"),
//        array("merchant_id" => 1001, "merchant_name" => "长安城堡大酒店", "province" => "陕西", "city" => "西安", "district" => "碑林区", "address" => "环城南路西段12号", "longitude" => "108.94067", "latitude" => "34.25047"),
//        array("merchant_id" => 1002, "merchant_name" => "土门美华金都酒店", "province" => "陕西", "city" => "西安", "district" => "莲湖区", "address" => "劳动南路64号", "longitude" => "108.90521", "latitude" => "34.25185"),
//        array("merchant_id" => 1003, "merchant_name" => "华浮宫酒店", "province" => "陕西", "city" => "西安", "district" => "未央区", "address" => "未央湖旅游开发区阳光大道1号", "longitude" => "108.9778", "latitude" => "34.4014"),
//        array("merchant_id" => 1004, "merchant_name" => "绿地假日酒店酒店", "province" => "陕西", "city" => "西安", "district" => "高新区", "address" => "高新技术产业开发区锦业路5号", "longitude" => "108.86515", "latitude" => "34.19354"),
//    );
    return $merchants;
}

/**
 * 获取单个商家详细信息
 * @param $proxy_id 代理ID
 * @param $merchant_id 商家ID
 * @return array merchant_id 商家ID，merchant_name 商家名称，merchant_desc 商家简介，merchant_logo 商家logo地址，merchant_tel 商家电话，province 所在省，city 所在市，district 所在区，address 具体地址，longitude 经度，latitude 纬度
 */
function get_merchant_info($merchant_id)
{
    $url = C('API_URL') . "/index.php/Home/Api/bizInfo";
    $params['biz_id'] = $merchant_id;
    $response = json_decode(curl_http($url, $params), true);
    if ($response['code'] == 0) {
        return null;
    }
    $location = explode(",", $response['data']['location']);
    $merchant_info = array("merchant_id" => $response['data']['id'], "merchant_name" => $response['data']['name'], "merchant_desc" => $response['data']['introduction'], "merchant_logo" => C('WIFI_URL') . $response['data']['logo'], "merchant_tel" => $response['data']['phone'], "address" => $response['data']['address'], "longitude" => trim($location[0]), "latitude" => trim($location[1]), "province" => $response['data']['province_id'], "city" => $response['data']['city_id'], "district" => $response['data']['area_id']);
    //$merchant_info = array("merchant_id" => 1000, "merchant_name" => "民乐园万达广场", "merchant_desc" => "万达广场，市民吃喝游乐好去处", "merchant_logo" => "http://xxxxx/logo.jpg", "merchant_tel" => "88812345", "province" => "陕西", "city" => "西安", "district" => "新城区", "address" => "解放路中段111号", "longitude" => "108.95862 ", "latitude" => "34.26519");
    return $merchant_info;
}


/**
 * 根据两点间的经纬度计算距离
 * @param float $latitude 纬度值
 * @param float $longitude 经度值
 * @return float
 */
function getDistance_admin($latitude1, $longitude1, $latitude2, $longitude2)
{
    $earth_radius = 6371000;
    $dLat = deg2rad($latitude2 - $latitude1);
    $dLon = deg2rad($longitude2 - $longitude1);
    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * asin(sqrt($a));
    $d = $earth_radius * $c;
    return round($d);   //四舍五入
}

/*
 * 按照原图的比例生成一个最大为$thumb_w*$thumb_w的缩略图并保存
* $img_path 被压缩的图片的路径
* $thumb_w 压缩的宽
* $thumb_w 压缩的高
* $save_path 压缩后图片的存储路径
* $is_del 是否删除原文件，默认删除
*/
function thumb_img($img_path, $thumb_w, $thumb_h, $save_path, $is_del = true)
{
    $image = new \Think\Image();
    $image->open($img_path);

    //如果文件路径不存在则创建
    $save_path_info = pathinfo($save_path);
    if (!is_dir($save_path_info['dirname'])) {
        mkdir($save_path_info['dirname'], 0777);
    }
    $image->thumb($thumb_w, $thumb_h)->save($save_path);
    if ($is_del) {
        @unlink($img_path); //删除源文件
    }
}

/*
 * 获取时间差
 * @description    取得两个时间戳相差的年龄
 * @before         较小的时间戳
 * @after          较大的时间戳
 * @return str     返回相差年龄y岁m月d天
**/
function datediff($before, $after) {
    if ($before>$after) {
        $b = getdate($after);
        $a = getdate($before);
    }
    else {
        $b = getdate($before);
        $a = getdate($after);
    }
    $n = array(1=>31,2=>28,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31);
    $y=$m=$d=0;
    if ($a['mday']>=$b['mday']) { //天相减为正
        if ($a['mon']>=$b['mon']) {//月相减为正
            $y=$a['year']-$b['year'];$m=$a['mon']-$b['mon'];
        }
        else { //月相减为负，借年
            $y=$a['year']-$b['year']-1;$m=$a['mon']-$b['mon']+12;
        }
        $d=$a['mday']-$b['mday'];
    }
    else {  //天相减为负，借月
        if ($a['mon']==1) { //1月，借年
            $y=$a['year']-$b['year']-1;$m=$a['mon']-$b['mon']+12;$d=$a['mday']-$b['mday']+$n[12];
        }
        else {
            if ($a['mon']==3) { //3月，判断闰年取得2月天数
                $d=$a['mday']-$b['mday']+($a['year']%4==0?29:28);
            }
            else {
                $d=$a['mday']-$b['mday']+$n[$a['mon']-1];
            }
            if ($a['mon']>=$b['mon']+1) { //借月后，月相减为正
                $y=$a['year']-$b['year'];$m=$a['mon']-$b['mon']-1;
            }
            else { //借月后，月相减为负，借年
                $y=$a['year']-$b['year']-1;$m=$a['mon']-$b['mon']+12-1;
            }
        }
    }
    return ($y==0?'':$y.'年').($m==0?'':$m.'个月').($d==0?'':$d.'天');
}

/**
 * 加密函数
 *
 * @param string $txt 需要加密的字符串
 * @param string $key 密钥
 * @return string 返回加密结果
 */
function encrypt($txt, $key = ''){
    if (empty($txt)) return $txt;
    if (empty($key)) $key = md5(MD5_KEY);
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
    $ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
    $nh1 = rand(0,64);
    $nh2 = rand(0,64);
    $nh3 = rand(0,64);
    $ch1 = $chars{$nh1};
    $ch2 = $chars{$nh2};
    $ch3 = $chars{$nh3};
    $nhnum = $nh1 + $nh2 + $nh3;
    $knum = 0;$i = 0;
    while(isset($key{$i})) $knum +=ord($key{$i++});
    $mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum%8,$knum%8 + 16);
    $txt = base64_encode(time().'_'.$txt);
    $txt = str_replace(array('+','/','='),array('-','_','.'),$txt);
    $tmp = '';
    $j=0;$k = 0;
    $tlen = strlen($txt);
    $klen = strlen($mdKey);
    for ($i=0; $i<$tlen; $i++) {
        $k = $k == $klen ? 0 : $k;
        $j = ($nhnum+strpos($chars,$txt{$i})+ord($mdKey{$k++}))%64;
        $tmp .= $chars{$j};
    }
    $tmplen = strlen($tmp);
    $tmp = substr_replace($tmp,$ch3,$nh2 % ++$tmplen,0);
    $tmp = substr_replace($tmp,$ch2,$nh1 % ++$tmplen,0);
    $tmp = substr_replace($tmp,$ch1,$knum % ++$tmplen,0);
    return $tmp;
}

/**
 * 解密函数
 *
 * @param string $txt 需要解密的字符串
 * @param string $key 密匙
 * @return string 字符串类型的返回结果
 */
function decrypt($txt, $key = '', $ttl = 0){
    if (empty($txt)) return $txt;
    if (empty($key)) $key = md5(MD5_KEY);

    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
    $ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
    $knum = 0;$i = 0;
    $tlen = @strlen($txt);
    while(isset($key{$i})) $knum +=ord($key{$i++});
    $ch1 = @$txt{$knum % $tlen};
    $nh1 = strpos($chars,$ch1);
    $txt = @substr_replace($txt,'',$knum % $tlen--,1);
    $ch2 = @$txt{$nh1 % $tlen};
    $nh2 = @strpos($chars,$ch2);
    $txt = @substr_replace($txt,'',$nh1 % $tlen--,1);
    $ch3 = @$txt{$nh2 % $tlen};
    $nh3 = @strpos($chars,$ch3);
    $txt = @substr_replace($txt,'',$nh2 % $tlen--,1);
    $nhnum = $nh1 + $nh2 + $nh3;
    $mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum % 8,$knum % 8 + 16);
    $tmp = '';
    $j=0; $k = 0;
    $tlen = @strlen($txt);
    $klen = @strlen($mdKey);
    for ($i=0; $i<$tlen; $i++) {
        $k = $k == $klen ? 0 : $k;
        $j = strpos($chars,$txt{$i})-$nhnum - ord($mdKey{$k++});
        while ($j<0) $j+=64;
        $tmp .= $chars{$j};
    }
    $tmp = str_replace(array('-','_','.'),array('+','/','='),$tmp);
    $tmp = trim(base64_decode($tmp));

    if (preg_match("/\d{10}_/s",substr($tmp,0,11))){
        if ($ttl > 0 && (time() - substr($tmp,0,11) > $ttl)){
            $tmp = null;
        }else{
            $tmp = substr($tmp,11);
        }
    }
    return $tmp;
}

