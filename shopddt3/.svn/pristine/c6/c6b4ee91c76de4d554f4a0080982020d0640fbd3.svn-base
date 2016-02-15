<?php
/**
 * 收货地址
 * @author 梁健
 */
namespace Home\Controller;
use BizService\AddressService;

class AddressController extends BaseController
{
	public $addressService;
    public function _initialize(){
        parent::_initialize();
		$this->addressService = new AddressService();
    }
	
    public function index(){
        $this->display();
    }
	    
    public function addressList(){
		$addr_list = $this->addressService->get_address(session('userId'));
		$address = array();
		$i = 0;
		foreach($addr_list as $addr){
			$province = $this->addressService->get_region_list($addr['province'], false);
			$city = $this->addressService->get_region_list($addr['city'], false);
			$area = $this->addressService->get_region_list($addr['district'], false);
			$address[$i]['id'] = $addr['id'];
			$address[$i]['name'] = $addr['consignee'];
			$address[$i]['addr'] = "{$province['region_name']}{$city['region_name']}{$area['region_name']}{$addr['address']}";
			$address[$i]['tel'] = $addr['tel'];
			$i ++;
		}
		$this->assign('addr_list', $address);
		$this->display("address_list");
    }

    public function addAddress(){
    	if(IS_POST){
    		$address = array();
			$address['consignee'] = I('post.name', '');
			$address['province'] = I('post.province', 0);
			$address['city'] = I('post.city', 0);
			$address['district'] = I('post.district', 0);
			$address['address'] = I('post.address', '');
			$address['tel'] = I('post.tel', '');
			$validate = array(
				array("consignee", "收货人姓名不能为空", "required"),
				array("tel", "收货人联系电话格式错误", 'regular', '/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/'),
				array("province", "请选择省", "required"),
				array("city", "请选择市", "required"),
				//array("district", "请选择区/县", "required"),
				array("address", "详细地址不能为空", "required"),
			);
			foreach($validate as $val){
				switch($val[2]){
					case 'required':
						if(!$address[$val[0]]){
							$this->error($val[1], U('Address/addAddress'));
							exit;
						}
						break;
					case 'regular':
						if(!preg_match($val[3], $address[$val[0]])){
							$this->error($val[1], U('Address/addAddress'));
							exit;
						}
						break;
					default:
						break;
				}
			}
			$res = $this->addressService->add_address($address);
			if(I('order_id')>0){
			  redirect(U('Address/addressList',array('order_id'=>I('order_id'),'sendtype'=>I('sendtype'))));
			}else{
			  redirect(U('Address/addressList'));
			}
    	}
        $region_list = $this->addressService->get_region_list(1, true);
		$this->assign('region_list', $region_list);
        $this->display("add_address");
    }
	
    public function regionList(){
    	$region_id = I('post.id', 0);
        $region_list = $this->addressService->get_region_list($region_id, true);
        echo json_encode($region_list);
    }
	
	public function rmAddress(){
		$address_id = I('get.id', 0);
		$result = $this->addressService->rm_address($address_id);
		redirect(U('Address/addressList'));
	}
}