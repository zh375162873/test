<?php
namespace Admin\Model;
use Think\Model;
use Think\Upload;
class GoodsimagesModel extends Model 
{ 
  protected $tableName = 'goods_images'; 
  protected $fields = array(
  'goods_imageid', //	int(10) 	否		商品图片id
  'goods_commonid', // 	int(10) 	否		商品公共内容id
  'shop_id', 	//int(10) 	否		商城id
  'goods_image', // 	varchar(1000)	否		商品图片
  'goods_image_sort',//	tinyint(3) 	否		排序
  'is_default',//	tinyint(3) 	否	0	默认主题，1是，0否
  '_pk'=>'goods_imageid'//主键
  );
  
  //增加图片信息，支持上传
  public function  addGoodsImages($data,$goods_commonid){
    
  }
  
 
  
  //删除图片
  public function  delGoodsImages($condition){
  
  }
  //修改图片
  public function  editGoodsImages($condition){
  
  }
  
  //获取单个图片信息
  public function  getGoodsImagesinfo($image_id){
  
  }
  
  //获取单个商品下的所有图片信息
  public function  getImageslistbyid($good_commonid){
  
  }
  
  
  
   /**
 * 获取商品图片信息
 * @param  milit   $id 分类ID或标识
 * @param  boolean $field 查询字段
 * @return array     产品信息
 * @author zhanghui
 */
 public function info($id, $type=0){
	/* 获取分类信息 */
	$map = array();
	if(is_numeric($id)){ //通过ID查询
	    $map['goods_commonid'] = $id;
	    if($type==0){
		 $map['is_default'] = 0;
		}elseif($type==1){
		 $map['is_default'] = 1;
		}
	}
	return $this->where($map)->select();
 }
  
  
    /**
     * 文件上传
     * @param  array  $files   要上传的文件列表（通常是$_FILES数组）
     * @param  array  $setting 文件上传配置
     * @param  string $driver  上传驱动名称
     * @param  array  $config  上传驱动配置
     * @return array           文件上传成功后的信息
     */
    public function upload($files, $setting, $driver = 'Local', $config = null){
        /* 上传文件 */
        $setting['callback'] = array($this, 'isFile');
		$setting['removeTrash'] = array($this, 'removeTrash');
        $Upload = new Upload($setting, $driver, $config);
        $info   = $Upload->upload($files);

        if($info){ //文件上传成功，记录文件信息
            foreach ($info as $key => &$value) {
                /* 已经存在文件记录 */
                if(isset($value['id']) && is_numeric($value['id'])){
                    continue;
                }

                /* 记录文件信息 */
                $value['path'] = substr($setting['rootPath'], 1).$value['savepath'].$value['savename'];	//在模板里的url路径
                if($this->create($value) && ($id = $this->add())){
                    $value['id'] = $id;
                } else {
                    //TODO: 文件上传成功，但是记录文件信息失败，需记录日志
                    unset($info[$key]);
                }
            }
            return $info; //文件上传成功
        } else {
            $this->error = $Upload->getError();
            return false;
        }
    }
	
	 /**
     * 检测当前上传的文件是否已经存在
     * @param  array   $file 文件上传数组
     * @return boolean       文件信息， false - 不存在该文件
     */
    public function isFile($file){
        if(empty($file['md5'])){
            throw new \Exception('缺少参数:md5');
        }
        /* 查找文件 */
		$map = array('md5' => $file['md5'],'sha1'=>$file['sha1'],);
        return $this->field(true)->where($map)->find();
    }
	
	/**
	 * 清除数据库存在但本地不存在的数据
	 * @param $data
	 */
	public function removeTrash($data){
		$this->where(array('id'=>$data['id'],))->delete();
	}
  
}
?>