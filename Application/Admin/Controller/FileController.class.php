<?php
namespace Admin\Controller;
use BizService\FileService;
/**
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */
class FileController extends BaseController {

    public function _initialize(){
        parent::_initialize();
    }
    /* 文件上传 */
    public function upload(){
        /* 返回标准数据 */
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');
        /* 调用文件上传组件上传文件 */
        $upload = new FileService();
        //设置上传参数
        $setting['maxSize'] = 3145728 ;// 设置附件上传大小
        $setting['exts']  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $setting['rootPath'] = './Uploads/'; // 设置附件上传根目录
		$setting['autoSub'] = true;
        $setting['savePath'] = 'goods/'; // 设置附件上传（子）目录
        $info = $upload->upload($_FILES,$setting);
		

		
		
		

        /* 记录图片信息 */
        if($info){
            $return['status'] = 1;
            $return = array_merge($info['download'], $return);
			
			
			//压缩图片
/*{
    "name": "72_P_1440043566134.jpg",
    "type": "image/jpeg",
    "size": 76965,
    "key": "download",
    "ext": "jpg",
    "md5": "eab7842fdb39f80a227c1388662a5ed6",
    "sha1": "38e71e57c19203821b6d01873863fc0c3d65334c",
    "savename": "564bddc60f8dc.jpg",
    "savepath": "goods/2015-11-18/",
    "path": "/Uploads/goods/2015-11-18/564bddc60f8dc.jpg",
    "status": 1,
    "info": "上传成功",
    "data": ""
}*/     
          $type=$_GET['type'];
		  if($type>0){
          $img_info=C('IMG_INFO');
		  $h=$img_info[$type]['h'];
		  $w=$img_info[$type]['w'];
          $d=date('Y-m-d',time());
		  $return['day']=$d;
          thumb_img('.'.$return['path'],$w, $h, "./Uploads/goods/".$d.'/'.$return['savename'],false);
		  $return['path']= "/Uploads/goods/".$d.'/'.$return['savename'];
		  }
		  	
        } else {
            $return['status'] = 0;
            $return['info']   = $upload->getError();
        }

        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }
	
	
	/* 文件上传文本编辑器使用 */
    public function upload_Editor(){
        /* 返回标准数据 */
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');

        /* 调用文件上传组件上传文件 */
        $upload = new FileService();
        //设置上传参数
        $setting['maxSize'] = 3145728 ;// 设置附件上传大小
        $setting['exts']  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $setting['rootPath'] = './Uploads/'; // 设置附件上传根目录
		$setting['autoSub'] = true;
        $setting['savePath'] = 'goods/old/'; // 设置附件上传（子）目录
        $info = $upload->upload($_FILES,$setting);

        /* 记录图片信息 */
        if($info){
			$return['status'] = 1;
            $return = array_merge($info['download'], $return);
			
			
		  $type=$_GET['type'];
		  if($type>0){
			 $img_info=C('IMG_INFO');
			  $h=$img_info[$type]['h'];
			  $w=$img_info[$type]['w'];
			  $d=date('Y-m-d',time());
			  thumb_img('.'.$info['imgFile']['path'],$w, $h, "./Uploads/goods/".$d.'/'.$info['imgFile']['savename'],false);
			  echo json_encode(array('error' => 0, 'url' => "/Uploads/goods/".$d.'/'.$info['imgFile']['savename']));
		  }else{
		      echo json_encode(array('error' => 0, 'url' => $info['imgFile']['path']));
		  }
			
			exit;
        } else {
            echo "ddddd";// $upload->getError();
			exit;
        }

        /* 返回JSON数据 */
        echo json_decode($return);
    }

    /* 下载文件 */
    public function download($id = null){

    }
}
