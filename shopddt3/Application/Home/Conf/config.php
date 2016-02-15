<?php
return array(
	//'配置项'=>'配置值'
    /* 错误页面模板 */
    'TMPL_ACTION_ERROR' => MODULE_PATH . 'View/Index/error.html', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => MODULE_PATH . 'View/Index/success.html', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE' => MODULE_PATH . 'View/Index/exception.html',// 异常页面的模板文件
    //session相关
    'SESSION_AUTO_START'    =>  true,    // 是否自动开启Session
    'SESSION_OPTIONS'       =>  array(
        'name'=>'session_id',
        'expire'=>86400  //60分钟有效期
    ), // session 配置数组 支持type name id path expire domain 等参数
    //'SESSION_TYPE'          =>  '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX'        =>  'shop_home', // 商城home session 前缀
    'DEFAULT_FILTER'        =>  'strip_tags,htmlspecialchars',//I函数字符串过滤
);