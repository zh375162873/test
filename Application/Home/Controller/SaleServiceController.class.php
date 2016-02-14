<?php
/**
 * 售后服务
 * @author 梁健
 */
namespace Home\Controller;
use BizService\OrderDisputeService;
class SaleServiceController extends BaseController
{
	public $disputeService;
    public function _initialize(){
        parent::_initialize();
		$this->disputeService = new OrderDisputeService();
    }
	
	public function index(){
		$order_id = I('get.id', 0);
		if(!$order_id){
			redirect(U('Order/orderlist'));
		}
		$orderinfo = D("Orders")->getinfo($order_id, session('userId'));
		if(!$orderinfo){
			$this->error("无此订单信息");
		}
		$dispute_info = $this->disputeService->dispute_info($order_id);
		$this->assign('order_id', $order_id);
		$this->assign('dispute_info',$dispute_info[0]);
		$this->display('customer_service_apply');
	}
	
	public function add_dispute(){
		$dispute = array();
		$dispute['user_id'] = session('userId');
		$dispute['shop_id'] = session('shopId');
		$dispute['order_id'] = I('post.order_id', 0);
		$dispute['type'] = I('post.cs_mode', 0);
		$dispute['content'] = I('post.cs_desc', '');
		$dispute['tel'] = I('post.cs_tel', '');
		$_validate = array(
			array("order_id", "订单ID不能为空", 'required'),
			//array("type","请选择申请类型",'required'),
			array("content", "问题描述不能为空", 'required'),
			array("tel", "手机格式错误", 'regular', '/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/')
		);
		foreach($_validate as $val){
			switch($val[2]){
				case 'required':
					if(!$dispute[$val[0]]){
						$this->error($val[1]);
						exit;
					}
					break;
				case 'regular':
					if(!preg_match($val[3], $dispute[$val[0]])){
						$this->error($val[1]);
						exit;
					}
					break;
				default:
					break;
			}
		}
		$res = $this->disputeService->add_dispute($dispute);
		redirect(U('SaleService/index', array('id'=>$dispute['order_id'])));
	}
}