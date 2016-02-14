<?php
    define('IN_ECS', true);
    define('ECS_ADMIN', true);
    require(dirname(__FILE__) . '/includes/init.php');
    //session_start();
    $act = isset($_GET['act']) ? $_GET['act'] : '';
    $_SESSION['code'] = isset($_SESSION['code']) ? $_SESSION['code'] : '';
    if ($act == 'getCode_reg'){
        $phone_num = $_POST['username'];
         if(!preg_match('/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/', $phone_num)){         
               echo json_encode(array("code"=>0,"msg"=>"请输入合法手机号码~!"));exit();
        }else{
            $res = $GLOBALS['db']->getOne("SELECT user_name FROM " . $GLOBALS['ecs']->table('users') .
                " WHERE user_name = '$phone_num'");
            if($res){
                echo json_encode(array("code"=>3,"msg"=>"该手机号码已注册~!"));exit();
            }
            $code = strval(rand(1000,9999));
            $data ="您好,您的注册验证码是：" . $code . " 欢迎您使用点点通掌上商城,请勿将验证码告知他人!" ;
            
            if(empty($_SESSION['send_time'])){
               
              //////////////////////////////////
            }else{
                if(time()-$_SESSION['send_time'] < 60){
                    $again_time = 60- (time() - $_SESSION['send_time']);
                    // exit('请求过于频繁,请等待' .$again_time. '秒');
                    echo json_encode(array("code"=>0,"msg"=>"请求过于频繁,请等待" .$again_time. "秒"));exit();
                }
            }
            
            $_SESSION['code'] = $code . "_" . $phone_num;
            $post_data = array();
            $post_data['account'] = iconv('GB2312', 'GB2312',"LKJX888");
            $post_data['pswd'] = iconv('GB2312', 'GB2312',"lkjx2015MSG");
            $post_data['mobile'] =$phone_num;
            $post_data['msg']=mb_convert_encoding("$data",'UTF-8', 'UTF-8');
            $post_data['needstatus']='true';
            $url='http://222.73.117.158/msg/HttpBatchSendSM?'; 
            $parse = parse_url($url);
            // var_dump($parse);die();
            // for($i=0;$i<10;$i++)
            // echo "<br />";
            $o="";
            foreach ($post_data as $k=>$v)
            {
               $o.= "$k=".urlencode($v)."&";
            }
            $post_data=substr($o,0,-1);
             
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            $result = curl_exec($ch) ;
            $pos = strpos($result,',');
            // echo $result;
            //用于截取判断状态码
            $co=substr($result,15,1);
            $_SESSION['send_time'] = time();//重新赋值时间
            if($co == '0'){
                //echo $co;
                echo json_encode(array("code"=>1,"msg"=>"验证码已发送~!"));exit();               
            }            
        }
    }
    elseif ($act == 'getCode_pas'){
        $phone_num = $_POST['username'];
         if(!preg_match('/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/', $phone_num)){         
               echo json_encode(array("code"=>0,"msg"=>"请输入合法手机号码~!"));exit();

        }else{
            $res = $GLOBALS['db']->getOne("SELECT user_name FROM " . $GLOBALS['ecs']->table('users') .
                " WHERE user_name = '$phone_num'");
            if(!$res){
                echo json_encode(array("code"=>3,"msg"=>"该手机号不存在!"));exit();
            }
            $code = strval(rand(1000,9999));
            $data ="您好,您的修改密码的验证码是：" . $code . " 欢迎您使用点点通掌上商城,请勿将该验证码告知他人!" ;
            
            if(empty($_SESSION['send_time'])){
               
              //////////////////////////////////
            }else{
                if(time()-$_SESSION['send_time'] < 60){
                    $again_time = 60- (time() - $_SESSION['send_time']);
                    // exit('请求过于频繁,请等待' .$again_time. '秒');
                    echo json_encode(array("code"=>0,"msg"=>"请求过于频繁,请等待" .$again_time. "秒"));exit();
                }
            }
            
            $_SESSION['code'] = $code . "_" . $phone_num;
            $post_data = array();
            $post_data['account'] = iconv('GB2312', 'GB2312',"LKJX888");
            $post_data['pswd'] = iconv('GB2312', 'GB2312',"lkjx2015MSG");
            $post_data['mobile'] =$phone_num;
            $post_data['msg']=mb_convert_encoding("$data",'UTF-8', 'UTF-8');
            $post_data['needstatus']='true';
            $url='http://222.73.117.158/msg/HttpBatchSendSM?'; 
            $parse = parse_url($url);
            // var_dump($parse);die();
            // for($i=0;$i<10;$i++)
            // echo "<br />";
            $o="";
            foreach ($post_data as $k=>$v)
            {
               $o.= "$k=".urlencode($v)."&";
            }
            $post_data=substr($o,0,-1);
             
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            $result = curl_exec($ch) ;
            $pos = strpos($result,',');
            // echo $result;
            //用于截取判断状态码
            $co=substr($result,15,1);
            $_SESSION['send_time'] = time();//重新赋值时间
            if($co == '0'){
                //echo $co;
                echo json_encode(array("code"=>1,"msg"=>"验证码已发送~!"));exit();               
            }    
        }
    }
    elseif ($act == 'validate') {
        $phone_num = $_POST['username'];
        $check_code = strval($_POST['check_code']);
        if(preg_match('/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/', $phone_num)){
            $validate_code = $check_code . "_" . $phone_num;
            if($_SESSION['code'] == $validate_code){
               echo json_encode(array("code"=>1,"msg"=>""));exit();
            }else{
               echo json_encode(array("code"=>0,"msg"=>"验证码输入错误~!"));exit();
            }
        }else{
            echo json_encode(array("code"=>2,"msg"=>"请输入合法手机号~!"));exit();
        }       
    }
//     public function actionSend(){
//         $phoneNum = array("18789454258,13002912070,18629015170,17791672236,18646775255,13488278284,15249298090,13152441108,18691956453，18710829235，15619335675");
//         foreach ($phoneNum as $key => $value) {
//             // var_dump($value);
//             $this->actionIndex($value);
//         }
//     }

// }

// zhw LKJX888
// mmw lkjx2015MSG
?>