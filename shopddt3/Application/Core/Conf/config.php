<?php
return array(
	//'配置项'=>'配置值'
    //session相关
    'SESSION_AUTO_START'    =>  true,    // 是否自动开启Session
    'SESSION_OPTIONS'       =>  array(
        'name'=>'session_id',
        'expire'=>1440  //24分钟有效期
    ), // session 配置数组 支持type name id path expire domain 等参数
    //'SESSION_TYPE'          =>  '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX'        =>  'wifi_admin', // 核心平台session 前缀
);