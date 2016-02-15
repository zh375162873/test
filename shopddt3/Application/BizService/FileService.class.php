<?php

namespace BizService;

use Think\Upload;
/**
 * 文件上传
 *
 * @author 谢林
 */
class FileService extends BaseService {

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
					$value['path'] = substr($setting['rootPath'], 1).$value['savepath'].$value['savename']; //在模板里的url路径
					continue;
				}

				$value['path'] = substr($setting['rootPath'], 1).$value['savepath'].$value['savename']; //在模板里的url路径
				/* 记录文件信息 */
				//@todo 未处理上传入库  20150924 谢林
			}
			return $info; //文件上传成功
		} else {
			$Upload->getError();
			return false;
		}
	}


}