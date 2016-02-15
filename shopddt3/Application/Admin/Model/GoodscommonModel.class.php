<?php
/**
 * 商品公共数据模型
 */
namespace Admin\Model;

use Think\Model;

class GoodscommonModel extends Model
{
    //定义表
    protected $tableName = 'goods_common';
    //定义字段
    protected $fields = array(
        'goods_commonid', // int(10)  否   商品公用id
        'storehouse_id', // int(10)    商品仓库id
        'goods_serial',    //varchar(50)	否		商品编号
        'goods_name',//  varchar(50)	否		商品名称（+规格名称）
        'position_tags', //varchar(50)	否		位置标签
		'post_tags', //varchar(100)	否		包邮条件
        'subtitle', //varchar(50)	否		副标题
        'goods_plun',//  varchar(50)	否		宣传语，买点,详情页面标题
        'shop_id',//  	int(10) 	否		商城id
        'store_type',//  int(10)   供货商类型（1：代理下商家，2：其他）
        'store_id',//   int(30)   供货商id(默认是代理下面的商家id)
        'store_name',// varchar(50)	否		店铺名称
        'gc_id',//int(10) 	否		商品分类id
        'gc_id_1',//	int(10) 	否		一级分类id
        'gc_id_2',// 	int(10) 	否		二级分类id
        'gc_id_3',// 	int(10) 	否		三级分类id
        'goods_price',// 	decimal(10,2)	否		商品入库价格
        'goods_marketprice',// 	decimal(10,2)	否		市场价
        'goods_salenum',//	int(10) 	否	0	已买数量
		'false_salenum',//	int(10) 	否	0	虚假销售数量
        'goods_storage',//	int(10) 	否	0	商品库存
        'goods_image',//	varchar(100)	否		商品主图
        'goods_state',//  tinyint(3) 	否		商品审核   -1商品已删除， 1通过，0未通过，10审核中
        'goods_addtime',//  	int(10) 	否		商品添加时间
        'goods_edittime',//int(10) 	否		商品编辑时间
        'areaid_1',//int(10) 	否		一级地区id（省）
        'areaid_2',//	int(10) 	否		二级地区id（市）
        'areaid_3',// 	int(10) 	否		二级地区id（区）
        'goods_body',//text	否		商品内容（web）
        'mobile_body',//text	否		手机端商品描述
        'is_own_shop',//	tinyint(3) 	否	0	是否为平台自营
        'start_date',//varchar(50)	否		开始时间
        'end_date',//varchar(50)	否		结束时间
        'usetime',//varchar(50)	否		使用时间  ，为五个字段组成，最后一个如果值为1，表明为24小时营业
        'rules',//text	否		使用规则
        'channel_type',// int(10)  渠道分类
        'channel_id',//   int(10) 渠道人员id
        'seo_title',//   text	否		商品seo标题,为商品详情页面自定义title字段
        'keywords',//   text	否		商品关键词
        'description',//  text	否		商品描述
        'addconent',//text	否		添加的栏目信息
		'false_evaluate',//text	否		虚假评价
        '_pk' => 'goods_commonid'//主键
    );


    //定义自动填充
    protected $_auto = array(
        array('goods_addtime', 'time', 1, 'function'),
        array('goods_edittime', 'time', 2, 'function'),
    );

    /**
     * 获取商品详细信息
     * @param  milit $id 分类ID或标识
     * @param  boolean $field 查询字段
     * @return array     产品信息
     * @author zhanghui
     */
    public function info($id, $field = true)
    {
        /* 获取分类信息 */
        $map = array();
        if (is_numeric($id)) { //通过ID查询
            $map['goods_commonid'] = $id;
        } else { //通过标识查询
            $map['name'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }


    /**
     *添加商品公共信息
     * @param  $data  公共信息数组
     * @return  int   公共商品id
     * @author zhanghui
     */
    public function addGoods($data)
    {
        $data_common = array();
        $data_common['goods_serial'] = $data['goods_serial'];
        $data_common['store_type'] = 1;
        $data_common['store_id'] = $data['store_id'];
        $data_common['store_name'] = $data['store_name'];
        $data_common['areaid_1'] = $data['province'];
        $data_common['areaid_2'] = $data['city'];
        $data_common['areaid_3'] = $data['district'];
        $data_common['position_tags'] = $data['position_tags'];
        $data_common['goods_name'] = $data['goods_name'];
        $data_common['subtitle'] = $data['subtitle'];
        $data_common['goods_plun'] = $data['subtitle'];
        $data_common['goods_storage'] = $data['goods_storage'];
        $data_common['goods_limit'] = $data['goods_limit'];
        $data_common['goods_marketprice'] = $data['goods_marketprice'];
        $data_common['goods_price'] = $data['goods_price'];
        $data_common['channel_type'] = $data['channel_type'];
        $data_common['channel_id'] = $data['channel_id'];
        $data_common['goods_image'] = $data['goods_image'];
        $data_common['mobile_body'] = $data['mobile_body'];
        $data_common['start_date'] = strtotime($data['start_date']);
        $data_common['end_date'] = strtotime($data['end_date']);
		//添加营业条件到usetime中
		$data['usetime'][5]=$data['yingye'];
        $data_common['usetime'] = array2string($data['usetime']);
        $data_common['rules'] = array2string($data['rules']);
        $data_common['shop_id'] = $data['shop_id'];
        $data_common['goods_addtime'] = time();
        $data_common['goods_edittime'] = time();
        $data_common['seo_title'] = $data['seo_title'];
        $data_common['keywords'] = $data['keywords'];
        $data_common['description'] = $data['description'];
		//包邮条件
		$data_common['post_tags'] = isset($data['post_tags'])?$data['post_tags']:"";
        //处理新增栏目字段
        $arr = array();
        $arr['title'] = $data['addcontent_title'];
        $arr['content'] = $data['addcontent'];
        $data_common['addconent'] = array2string($arr);
        $goods_id = $this->add($data_common);
        return $goods_id;
    }

    /**
     *删除商品公共信息
     * @param  $id  商品公共id
     * @return  int   默认1
     * @author zhanghui
     */
    public function  delGoods($id)
    {
        $data = array();
        $data['goods_state'] = -1;
        $this->where('goods_commonid=' . $id)->data($data)->save();
        return 1;
    }

    /**
     *更新产品公共信息
     * @param  $data  商品公共信息数组
     * @param  $id  商品公共id
     * @return  int   默认1
     * @author zhanghui
     */
    public function   editGoods($data, $id)
    {
        $data_common = array();
        $data_common['goods_serial'] = $data['goods_serial'];
        $data_common['position_tags'] = $data['position_tags'];
        $data_common['subtitle'] = $data['subtitle'];
        $data_common['goods_plun'] = $data['subtitle'];
        $data_common['start_date'] = strtotime($data['start_date']);
        $data_common['end_date'] = strtotime($data['end_date']);
		//添加营业条件到usetime中
		$data['usetime'][5]=$data['yingye'];
        $data_common['usetime'] = array2string($data['usetime']);
        $data_common['rules'] = array2string($data['rules']);
        $data_common['channel_type'] = $data['channel_type'];
        $data_common['channel_id'] = $data['channel_id'];
        $data_common['goods_name'] = $data['goods_name'];
        $data_common['goods_price'] = $data['goods_price'];
        $data_common['goods_image'] = $data['goods_image'];
        $data_common['goods_marketprice'] = $data['goods_marketprice'];
        $data_common['goods_storage'] = $data['goods_storage'];
        $data_common['store_type'] = 1;
        $data_common['store_id'] = $data['store_id'];
        $data_common['store_name'] = $data['store_name'];
        $data_common['areaid_1'] = $data['province'];
        $data_common['areaid_2'] = $data['city'];
        $data_common['areaid_3'] = $data['district'];
        $data_common['goods_edittime'] = time();
        $data_common['mobile_body'] = htmlspecialchars_decode($data['mobile_body']);
        $data_common['seo_title'] = $data['seo_title'];
        $data_common['keywords'] = $data['keywords'];
        $data_common['description'] = $data['description'];
		//包邮条件
		$data_common['post_tags'] = isset($data['post_tags'])?$data['post_tags']:"";
        //处理新增栏目字段
        $arr = array();
        $arr['title'] = $data['addcontent_title'];
        $arr['content'] = $data['addcontent'];
        $data_common['addconent'] = array2string($arr);


        $flag = $this->where("goods_commonid=" . $id)->data($data_common)->save();
        return 1;
    }

    /**
     *通过公共id更新产品信息
     * @param  $data  商品公共信息数组
     * @param  $id  商品公共id
     * @return  int   默认1
     * @author zhanghui
     */
    public function   editGoodsbycommonid($goods_commonid, $data)
    {
        $flag = $this->where("goods_commonid=" . $goods_commonid)->data($data)->save();
        return 1;
    }


    /**
     *通过现金商品id更新产品信息
     * @param  $data  商品公共信息数组
     * @param  $id  现金商品id
     * @return  int   默认1
     * @author zhanghui
     */
    public function   getGoodsbymid($goods_id, $data = true)
    {
        /* 获取分类信息 */
        $map = array();
        if (is_numeric($goods_id)) { //通过ID查询
            $map['goods_id'] = $goods_id;
        }
        $goods_commonid = M('goods')->where($map)->getField('goods_commonid');
        return M('goods_common')->field($data)->where("goods_commonid=". intval($goods_commonid))->find();
    }

    /**
     *  卖货或者时修改总库存，和销售数量
     * @param  $goods_commonid   商品公共id
     * @param  $num      数量
     * @author  张辉
     */
    public function  changestorage($goods_commonid, $num)
    {
        if ($num > 0) {
            //卖出货时，增加销售数量
            $this->where("goods_commonid = $goods_commonid")->setInc('goods_salenum', $num);
            //卖出货时，减少库存
            $this->where("goods_commonid = $goods_commonid")->setDec('goods_storage', $num);
        } else {
            //退货时，减少销售数量
            $this->where("goods_commonid = $goods_commonid")->setDec('goods_salenum', abs($num));
            //退货时，增加总库存
            $this->where("goods_commonid = $goods_commonid")->setInc('goods_storage', abs($num));
        }

        return 1;
    }

// public function change_sale_number(){

//     $shop_info = get_shop_proxy();
//     $shop_id=$shop_info['shop_id']?$shop_info['shop_id']:1;
//     $goods_list = $this->where("shop_id=".$shop_id)->select();
//     foreach ($goods_list as $key => $value) {
//         $sql = "SELECT goods_sold_num FROM `ddt_old_goods` WHERE goods_sn='".$value['goods_serial']."'";

//         $goods_sold_num = $this->query($sql);
//         // dump($goods_sold_num);exit;
//         $goods_sold_num = $goods_sold_num[0]['goods_sold_num'];
//         if($goods_sold_num){
//             $msg = '货号:'.$value['goods_serial'].'商品名称:'.$value['goods_name'];
//             $res_common = $this->where('shop_id='.$shop_id.' and goods_commonid='.$value['goods_commonid'])->setInc('goods_salenum',$goods_sold_num);
//             if($res_common){
//                 $msg.='更改goods_common表增加已售'.$goods_sold_num.'成功!';
//             }else{
//                 $msg.='更改goods_common表失败!';
//             }
//             $res_goods = D('Admin/Goods')->where('shop_id='.$shop_id.' and goods_commonid='.$value['goods_commonid'])->setInc('goods_salenum',$goods_sold_num);
//             if($res_goods){
//                 $msg.='更改goods表增加已售'.$goods_sold_num.'成功!';
//             }else{
//                 $msg.='更改goods表失败!';
//             }
//             echo $msg.'<br/>';

//         }else{
//             echo '商品名称:'.$value['goods_name'].'的货号'.$value['goods_serial'].'不存在!<br/>';
//         }
//     }

//}

}

?>