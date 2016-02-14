<?php
namespace Admin\Controller;
use BizService\UserService;
use Think\Controller;
use Think\Model;
use Think\Upload;

class ClassController extends BaseController {
    private  $class_db;    
	
	public function _initialize() {
       $this->class_db=D('goodsclass');
		parent::_initialize();
    }   
	/**
	 * 默认首页
	 * @author zhanghui
	 */
    public function index(){
        $this->class_list();
    }
	
	/**
	 * 获取分类列表
	 * @author zhanghui
	 */
	public function class_list(){
		//获取商城信息
		$shop_proxy=get_shop_proxy();
		$shop_id=$shop_proxy['shop_id']?$shop_proxy['shop_id']:0;
		if($shop_id==0){
		  exit('请先登录！');
		}
	    //判断是否有默认分类
		$class_moren=$this->class_db->where("is_all=1 and shop_id=".$shop_proxy['shop_id'])->select();
		if($class_moren){
		  
		}else{
		   //判断是否有原始的默认分类
		   $class_moren2=$this->class_db->where("gc_name='随便看看' and shop_id=".$shop_proxy['shop_id'])->find();  
		   if($class_moren2){
		      $data['is_all'] = 1;
		      $this->class_db->where('gc_id='.$class_moren2['gc_id']." AND shop_id=".$shop_proxy['shop_id'])->data($data)->save();
		   }else{
			   //添加一个默认的分类
			   $data['gc_name'] = "随便看看";
			   $data['gc_sort'] = 99;
			   $data['is_all'] = 1;
			   $data['gc_images']='class_images/0.png';
			   $data['shop_id'] = $shop_proxy['shop_id'];
			   $this->class_db->data($data)->add();
		   }	   
		}

		$class_data=$this->class_db->getTree(0,true,$shop_proxy['shop_id']);  
		
		foreach ($class_data as $key=>$value){
			$id[$key] = $value['id'];
			$price[$key] = $value['goods_num'];
		}
		if(I('order')==1){
		array_multisort($price,SORT_NUMERIC,SORT_DESC,$id,SORT_STRING,SORT_DESC,$class_data);
		}elseif(I('order')==2){
		array_multisort($price,SORT_NUMERIC,SORT_ASC,$id,SORT_STRING,SORT_ASC,$class_data);
		}
		
		//print_r($this->class_db->tree_to_list($class_data,'_child','goods_num'));
		
		
        $this->assign('classdata',$class_data);	
		$this->display("list");
    }  
	
	/**
	 * 获取一个分类列表
	 * @author zhanghui
	 */
	public function class_sublist(){
	    $gc_id = I('gc_id');
	    $class_data=$this->class_db->getTree($gc_id);
        $this->assign('classdata',$class_data);	
		$this->assign('subclass',$class_data['child']);	
		$this->display("sublist");
    }
	
	
	/**
	 * 添加一个分类信息
	 * @author zhanghui
	 */
	public  function class_add(){
		//获取商城信息
		$shop_proxy=get_shop_proxy();
		if (IS_POST) { // 提交表单
		    $data['gc_id'] = I('id');
		    $data['gc_images'] = I('upload_validate');
		    $data['gc_name'] = I('name');
		    $data['gc_sort'] = I('sort');
			$data['gc_parent_id'] = I('parent_id');
			$data['gc_keywords'] = I('gc_keywords');
			$data['gc_description'] = I('gc_description');  
		    $data['shop_id'] = $shop_proxy['shop_id'];
			//判断是否重复
			$info=M("goods_class")->where("gc_name='".$data['gc_name']."'")->find();
		    if($info){
			   $this->error ("分类名称重复！");
			}

			 // $data['gc_images']=I('gc_images');
			//保存数据
			if (false !== $this->class_db->where('gc_id='.$data['gc_id'])->data($data)->add()) {
				$this->success ( '添加成功成功！', U ( 'class_list' ) );
			} else {
				$error = $this->class_db->getError ();
				$this->error ( empty ( $error ) ? '未知错误！' : $error );
			}
		} else {
		    $gc_parent_id = I('gc_id');
		    $class_data=$this->class_db->getTree(0,true,$shop_proxy['shop_id']);
			$this->assign('classdata',$class_data);	
			$this->assign('gc_parent_id',$gc_parent_id);	
			$this->display ('add');
		}
	  
	}
	
	/*
	ajax验证是否名字重复
	*/
	public function ajax_rename(){
		//判断是否重复
		$name=I("name");
		$gc_id=I("gc_id")?I("gc_id"):0;
		if($gc_id>0){
		  $info=M("goods_class")->where("gc_name='".$name."'")->find();
		}else{
		  $info=M("goods_class")->where("gc_name='".$name."' and gc_id !=".$gc_id)->find();
		}
		if($info){
		   echo 1;
		}else{
		   echo 0;
		}
	}
	
	/* 文件上传 */
	public function upload(){
		/* 返回标准数据 */
		$return  = array('status' => 1, 'info' => '上传成功', 'data' => '');

		//设置上传参数
		$config['maxSize'] = 3145728 ;// 设置附件上传大小
		$config['exts']  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$config['rootPath'] = './Uploads/'; // 设置附件上传根目录
		$config['autoSub'] = false;
		$config['savePath'] = 'class_images/'; // 设置附件上传（子）目录
		/* 调用文件上传组件上传文件 */
		$upload = new Upload($config);

		$info = $upload->upload();

		/* 记录图片信息 */
		if($info){
			$return['status'] = 1;
			$return = array_merge($info['download'], $return);
			//thumb_img("./Uploads/".$return['savepath'].$return['savename'],100,100, "./Uploads/class_images/".$return['savename'],false);
		} else {
			$return['status'] = 0;
			$return['info']   = $upload->getError();
		}

		/* 返回JSON数据 */
		$this->ajaxReturn($return);
	}
	/**
	 * 修改一个分类信息
	 * @author zhanghui
	 */
	public function class_edit(){
		//获取商城信息
		$shop_proxy=get_shop_proxy();
		if (IS_POST) { // 提交表单
		    $id=I('id');
			$data['gc_images'] = I('upload_validate');
		    $data['gc_parent_id'] = I('parent_id');
		    $data['gc_name'] = I('name');
		    $data['gc_sort'] = I('sort');
			$data['gc_keywords'] = I('gc_keywords');
			$data['gc_description'] = I('gc_description');  
            //判断是否重复
			$info=M("goods_class")->where("gc_name='".$data['gc_name']."'")->find();
		    if($info){
			   if($info['gc_id']!=$id){
			     $this->error ("分类名称重复！");
			   }	 
			}

			 //$data['gc_images']=I('gc_images');
			//保存数据
			if (false !== $this->class_db->where('gc_id='.$id." AND shop_id=".$shop_proxy['shop_id'])->data($data)->save()) {
			    //修改模板上的分类信息
			    D('tempitem')->editclassall($shop_proxy['shop_id']);
				$this->success ( '编辑成功！', U ( 'class_list' ) );
			} else {
				$error = $this->class_db->getError ();
				$this->error ( empty ( $error ) ? '未知错误！' : $error );
			}
		} else {
		    $gc_id = I ( 'gc_id' );
			/* 获取分类信息 */
			$info = $gc_id ? $this->class_db->info ( $gc_id ) : '';
			$class_data=$this->class_db->getTree(0,true,$shop_proxy['shop_id']);
			 $this->assign('classdata',$class_data);	
			$this->assign ( 'info', $info );
			$this->assign ( 'id', $gc_id );
			$this->display ('edit');
		}
    }
	
	/**
	 * 删除一个分类
	 * @author zhanghui
	 */
	public function class_del() { 
	    //获取商城信息
		$shop_proxy=get_shop_proxy();
		
		$class_id = I ( 'gc_id' );
		if (empty ( $class_id )) {
			$this->error ( '参数错误!' );
		}
		// 判断该分类下有没有子分类，有则不允许删除
		$child = $this->class_db->where ( array (
				'gc_parent_id' => $class_id 
		) )->field ( 'gc_id' )->select ();
		if (! empty ( $child )) {
			$this->error ( '请先删除该分类下的子分类' );
		}
		// 判断该分类下有没有内容
		$goodsclassrelation = D ( 'goodsclassrelation' )->where ( array (
				'class_id' => $class_id 
		) )->field ( 'class_id' )->select ();
		if (! empty ( $goodsclassrelation )) {
			$this->error ( '请先删除该分类下的产品（包含回收站）' );
		}
		
		
		
		// 删除该分类信息
		$res = $this->class_db->where("gc_id=".$class_id)->delete (  );
		if ($res !== false) {
		    //修改模板上的分类信息
			 D('tempitem')->editclassall($shop_proxy['shop_id']);
			$this->success ( '删除分类成功！' );
		} else {
			$this->error ( '删除分类失败！' );
		}
	}
	
	
//分类向上
public function listup(){
  //获取商城信息
  $shop_proxy=get_shop_proxy();
  $shop_id=$shop_proxy['shop_id'];
  $gc_id=$_GET['gc_id'];
  $data=D('goodsclass')->where('shop_id='.$shop_id)->order('gc_sort asc')->select();
  $num=0;
  foreach($data as $key=>$val){
	if($val['gc_id']==$gc_id&&$num>0){
	  $this->class_db->where('gc_id='.$num)->data(array('gc_sort'=>$key))->save()  ;
	  $this->class_db->where('gc_id='.$val['gc_id'])->data(array('gc_sort'=>$key-1))->save();
	}else{
	  $this->class_db->where('gc_id='.$val['gc_id'])->data(array('gc_sort'=>$key))->save();
	}
	$num=$val['gc_id'];
  }
  $this->success ( '操作成功！' );
}


//分类向下
public function listdown(){
 //获取商城信息
  $shop_proxy=get_shop_proxy();
  $shop_id=$shop_proxy['shop_id'];
  $gc_id=$_GET['gc_id'];
  
  $data=D('goodsclass')->where('shop_id='.$shop_id)->order('gc_sort asc')->select();
  $flag=0;
  $num=0;
  foreach($data as $key=>$val){
		  if($flag==1){
			$this->class_db->where('gc_id='.$num)->data(array('gc_sort'=>$key))->save()  ;
			$this->class_db->where('gc_id='.$val['gc_id'])->data(array('gc_sort'=>$key-1))->save();
			$flag=0;
		  }else{
			$this->class_db->where('gc_id='.$val['gc_id'])->data(array('gc_sort'=>$key))->save()  ;
		  } 
		  if($val['gc_id']==$gc_id){
	       $flag=1;
	      }
	      $num=$val['gc_id'];
  }
 
   $this->success ( '操作成功！' );
}

//转移商品
function moveclass(){
  //获取商城信息
  $shop_proxy=get_shop_proxy();
  $shop_id=$shop_proxy['shop_id'];
  if (IS_POST) { // 提交表单 
     //接收id
	 $gc_ids=I('gc_ids');
	 $gc_id_old=I('gc_id_old');
	 $type=I('type');
	 //获取此分类下商品信息
	 $goods=D('goodsclassrelation')->where('class_id='.$gc_id_old)->select();
	 if($type==1){
		 //删除此分类下商品信息
		 D('goodsclassrelation')->where('class_id='.$gc_id_old)->delete(); 
	  }
	  
	  foreach($gc_ids as $val){
		   //增加分类
		   foreach($goods as $val_g){
		        //如果没有此分类就增加
				$goods_d=D('goodsclassrelation')->where('class_id='.$val.' and goods_commonid='.$val_g['goods_commonid'])->find();
				if($goods_d){
				
				}else{
				$data['goods_commonid'] = $val_g['goods_commonid'];
				$data['shop_id'] = $val_g['shop_id'];
				$data['class_id'] = $val;
				D('goodsclassrelation')->data($data)->add() ;
				}
		   }
	  }
	 $this->success ( '操作成功！', U ( 'class_list' ) );
  }else{
    $gc_id=I('gc_id');
    $class_data=$this->class_db->getTree(0,true,$shop_proxy['shop_id']);
	foreach($class_data as $key=>$val){
	   if($gc_id==$val['gc_id']){
	     unset($class_data[$key]);
	   }
	}
	$this->assign('gc_id_old',$gc_id);
    $this->assign('classdata',$class_data);	
    $this->display ('moveclass');
  }	
}

}