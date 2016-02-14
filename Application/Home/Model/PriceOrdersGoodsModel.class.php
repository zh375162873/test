<?php
/*
 * 兑换奖品订单商品模型
 */
namespace Home\model;
use Think\Model;
class PriceOrdersGoodsModel extends Model {
    protected $fields=array("id","order_id","goods_id","goods_name","goods_num","goods_price","goods_image","shop_id","store_id","buyer_id","_pk"=>"id");
	
    
}