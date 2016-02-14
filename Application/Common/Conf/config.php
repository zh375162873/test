<?php
return array(
	//'配置项'=>'配置值'
    'LOAD_EXT_CONFIG' => 'definevar',
    'MODULE_ALLOW_LIST' => array('Home','Admin','Core','Api'),//可访问模块
    'DEFAULT_MODULE' => 'Home',  // 默认模块
    'MODULE_DENY_LIST' => array('Common','Runtime','BizService'),//禁止访问模块
    'URL_CASE_INSENSITIVE' => true,//忽略URL大小写
    'SHOW_ERROR_MSG' => true,
    //'SHOW_PAGE_TRACE' =>true,
    'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => '192.168.1.110', // 服务器地址
    'DB_NAME' => 'ddt_shop', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => '', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => 'ddt_',//数据表前缀
    'SHOP_URL'   =>  'http://localhost:8084',//商城地址
    'WIFI_URL'  =>  'http://localhost:8081',//原有核心平台地址
    'API_URL' => 'http://182.92.181.175:9009', //获取商家地址的api接口域名
    'PROXY_URL' => 'http://localhost:8081',//代理商系统地址

    'VAR_SESSION_ID' => 'session_id',	//修复uploadify插件无法传递session_id的bug
    //数据缓存配置
    'DATA_CACHE_TYPE' => 'Memcache',
    'MEMCACHE_HOST'   =>  'tcp://127.0.0.1:11211',
    'URL_NOTIY' => '192.168.1.110:8080',
    //'DATA_CACHE_TIME' => '0',
    //session相关
    'SESSION_AUTO_START'    =>  true,    // 是否自动开启Session

    'TMPL_PARSE_STRING' => array(
        '__UPLOAD__' => '/Uploads/', // 增加新的上传路径替换规则)
    ),
    'TAG_NESTED_LEVEL' =>5, //标签嵌套最深级数
    //支付宝配置参数
    'ALIPAY_CONFIG'=>array(
        'partner' =>'2088612778871717',   //这里是你在成功申请支付宝接口后获取到的PID；
        'key'=>'3a5k00st4wn3gydpebpk4w13or7ue4au',//这里是你在成功申请支付宝接口后获取到的Key
        'sign_type'=>strtoupper('MD5'),
        'input_charset'=> strtolower('utf-8'),
        'cacert'=> getcwd().'\\cacert.pem',
        'transport'=> 'http',
    ),
    //以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；
    'ALIPAY'   =>array(
        //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
        'seller_email'=>'211362460@qq.com',
    ),
    //支付宝wap参数配置
    'WAP_ALIPAY_CONFIG'=>array(
        'partner' =>'2088612778871717',   //这里是你在成功申请支付宝接口后获取到的PID；
        'key'=>'3a5k00st4wn3gydpebpk4w13or7ue4au',//这里是你在成功申请支付宝接口后获取到的Key
        'private_key_path'	=> VENWAP.'Wapalipay/key/rsa_private_key.pem',//商户的私钥（后缀是.pen）文件相对路径
        'ali_public_key_path'=> VENWAP.'Wapalipay/key/alipay_public_key.pem', //支付宝公钥（后缀是.pen）文件相对路径
        'sign_type'=>strtoupper('MD5'),
        'input_charset'=> strtolower('utf-8'),
        'cacert'=> getcwd().'\\cacert.pem',
        'transport'=> 'http',
    ),
    
    
    //微信配置
    'WXPAY'   =>array(
        'APPID' => 'wxa709bd24ae348e11', //微信公众号身份的唯一标识。审核通过后，在微信发送的邮件中查看
        'MCHID' => '1262522901', //受理商ID，身份标识
        'KEY'   => 'jKSTg2X8o4HHJ6iyZ9PgjUjxUQ52Spje', //商户支付密钥Key。审核通过后，在微信发送的邮件中查看
        'APPSECRET' => 'f0183c821fc33280a510b3af4a73b055', //JSAPI接口中获取openid，审核后在公众平台开启开发模式后可查看
        'JS_API_CALL_URL' => "", //获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
        'SSLCERT_PATH' => '/ThinkPHP/Library/Vendor/WxPayPubHelper/cacert/apiclient_cert.pem', //证书路径,注意应该填写绝对路径
        'SSLKEY_PATH' => '/ThinkPHP/Library/Vendor/WxPayPubHelper/cacert/apiclient_key.pem', //证书路径,注意应该填写绝对路径
        'NOTIFY_URL' => "http://".$_SERVER['HTTP_HOST']."/index.php?m=Home&c=Wxpay&a=wx_notiy_do", //异步通知url，商户根据实际开发过程设定
        'CURL_TIMEOUT' => 30, //本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
    ),
    //正式上线后，需要变更为正式参数
	'MAP_KEY' => 'biTK0mmy5ZoLxW1cYYIY8GVx',//百度地图key测试用，正式：biTK0mmy5ZoLxW1cYYIY8GVx
    'MAP_TABLEID'  => '126155',//百度地图lbs表id，正式：126155
    
    
);