<?php
namespace Admin\Controller;
use BizService\OrderDisputeService;
use BizService\ExeclService;
class AftersalesController extends BaseController
{
	public $disputeService;
	public function _initialize()
    {
        parent::_initialize();
		$this->disputeService = new OrderDisputeService();
    }
	
	public function index(){
		$data = array();
		$data['type'] = I('get.dis_type', 99);
		$data['status'] = I('get.dis_status', 99);
		$data['begin_time'] = I('get.begin_time', '');
		$data['end_time'] = I('get.end_time', '');
		$data['key'] = I('get.dis_key', '');
		$data['shop_id'] = session('shopId');
		$sort = array();
		$sort['order'] = I('get.dis_order', '');
		$sort['sort'] = I('get.dis_sort', '');
		$dispute_list = $this->disputeService->dispute_list($data, $sort, true);
		$this->assign('dispute_list', $dispute_list['list']);
		$this->assign('pages', $dispute_list['page']);
		$this->assign('data', $data);
		$this->assign('sort', $sort);
		$this->display();
	}
	
	public function export(){
		$data = array();
		$data['type'] = I('get.dis_type', 99);
		$data['status'] = I('get.dis_status', 99);
		$data['begin_time'] = I('get.begin_time', '');
		$data['end_time'] = I('get.end_time', '');
		$data['key'] = I('get.dis_key', '');
		$data['shop_id'] = session('shopId');
		$sort = array();
		$sort['order'] = I('get.dis_order', '');
		$sort['sort'] = I('get.dis_sort', '');
		$dispute_list = $this->disputeService->dispute_list($data, $sort, false);
		$disput_type = array('0'=>'退换货','1'=>'问题投诉','2'=>'其他');
		$execl_service = new ExeclService();
		$execl_name = "售后申请列表";
		$execl_data = array();
		$i = 0;
		foreach($dispute_list['list'] as $val){
			$execl_data[$i]['order_sn'] = chunk_split($val['order_sn'], 4, ' ');
			$execl_data[$i]['goods_name'] = $val['goods_name'];
			$execl_data[$i]['goods_price'] = $val['goods_price'];
			$execl_data[$i]['goods_num'] = $val['goods_num'];
			$execl_data[$i]['shipping_fee'] = $val['shipping_fee'];
			$execl_data[$i]['order_amount'] = $val['order_amount'];
			$execl_data[$i]['type'] = $disput_type[$val['type']];
			$execl_data[$i]['content'] = $val['content'];
			$execl_data[$i]['tel'] = $val['tel'];
			$execl_data[$i]['user_name'] = "{$val['user_name']} / {$val['nick_name']}";
			$execl_data[$i]['add_time'] = date('Y-m-d H:i:s', $val['add_time']);
			$execl_data[$i]['status'] = $val['status'] == 0 ? '未处理' : '已处理';
			$i++;
		}
		$excel_keys = array('order_sn','goods_name','goods_price','goods_num','shipping_fee','order_amount','type','content','tel','user_name','add_time','status');
		$execl_cols = array('订单号','商品名称','单价','数量','运费','总价','申请类型','申请内容','联系电话','用户名 / 昵称','提交时间','状态');
		$excel_width = array(20, 35, 10, 10, 10, 15, 20, 35, 20, 30, 30, 20);
		$execl_service->downMoreColumnDateToExel($execl_data, $execl_name, $excel_keys, $execl_cols, $excel_width);
	}

	public function do_reply(){
		$id = I('post.dis_id', 0);
		$data = array();
		$data['status'] = 1;
		$data['reply'] = I('post.dis_reply', '');
		$data['remark'] = I('post.dis_remark', '');
		$data['update_time'] = time();
		if(!$id){
			echo json_encode(array('status'=>0, 'msg'=>'回复ID不能为空！'));
			exit;
		}
		if(!$data['reply']){
			echo json_encode(array('status'=>0, 'msg'=>'回复内容不能为空！'));
			exit;
		}
		$res = $this->disputeService->change_dispute($data, $id);
		if($res){
			echo json_encode(array('status'=>1, 'msg'=>'售后申请回复成功！'));
			exit;
		}else{
			echo json_encode(array('status'=>0, 'msg'=>'售后申请回复失败！'));
			exit;
		}
	}
}