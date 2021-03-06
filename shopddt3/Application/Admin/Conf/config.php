<?php
return array(
    //'配置项'=>'配置值'
    //session相关
    'SESSION_AUTO_START' => true,    // 是否自动开启Session
    'SESSION_OPTIONS' => array(
        'name' => 'session_id',
        'expire' => 3600  //60分钟有效期
    ), // session 配置数组 支持type name id path expire domain 等参数
    //'SESSION_TYPE'          =>  '', // session hander类型 默认无需设置 除非扩展了session hander驱动
    'SESSION_PREFIX' => 'shop_admin', // 商城后台session 前缀

	/* 错误页面模板 */
	'TMPL_ACTION_ERROR' => MODULE_PATH . 'View/Index/error.html', // 默认错误跳转对应的模板文件
	'TMPL_ACTION_SUCCESS' => MODULE_PATH . 'View/Index/success.html', // 默认成功跳转对应的模板文件
	'TMPL_EXCEPTION_FILE' => MODULE_PATH . 'View/Index/exception.html',// 异常页面的模板文件

	'IMG_INFO' => array(
	    '1'=>array('w'=>1000/*750*/,'h'=>'304'),//首页轮播图
		'2'=>array('w'=>300/*200*/,'h'=>'200'),//首页广告1图片
		'3'=>array('w'=>300/*200*/,'h'=>'200'),//产品列表展示图
		'4'=>array('w'=>1000/*750*/,'h'=>'402'),//产品详情页面头部轮播图
		'5'=>array('w'=>750,'h'=>'750'),//产品详情页面内容图片
	),

);