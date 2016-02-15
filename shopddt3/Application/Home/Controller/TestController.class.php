<?php

namespace Home\Controller;

use BizService\GeohashService;

header("Content-type:text/html;charset=utf-8");

class TestController extends BaseController
{

    public function index()
    {
        $geohash = new GeohashService();
        //经纬度转换成Geohash
        //获取附近的信息
        $n_latitude = 34.236080797698;
        $n_longitude = 109.0145193757;
        echo "当前位置为：经度108.7455，纬度34.3608<br/><br/>以下网点离我最近：";
        //开始
        $b_time = microtime(true);
        //当前 geohash值
        $n_geohash = $geohash->encode($n_latitude, $n_longitude);
        //附近
        $n = 3;
        $like_geohash = substr($n_geohash, 0, $n);
        $sql = 'select * from ddt_goods where geohash like "' . $like_geohash . '%"';
        $results = M()->query($sql);
        foreach($results as $row){
            $data[] = array(
                "latitude" => $row["Latitude"],
                "longitude" => $row["Longitude"],
                "name" => $row["RetailersName"],
            );
        }
        //算出实际距离
        foreach ($data as $key => $val) {
            $distance = getDistance($n_latitude, $n_longitude, $val['latitude'], $val['longitude']);
            $data[$key]['distance'] = $distance;
            //排序列
            $sortdistance[$key] = $distance;
        }
        //距离排序
        array_multisort($sortdistance, SORT_ASC, $data);
        //结束
        $e_time = microtime(true);
        echo "（计算耗时：";
        echo $e_time - $b_time;
        echo "秒）<br/>";
        //var_dump($data);
        foreach ($data as $key => $val) {
            echo "</br>";
            echo $val['distance'] . " 米-------" . $val['name'];
        }
    }
}