<?php
/*
 * 订单商品模型
 */
namespace Home\model;
use Think\Model;
class OrdersGoodsModel extends Model {
   protected $fields=array("id","order_id","goods_id","goods_serial","goods_name","goods_plun","goods_divided","goods_price","market_price","goods_num","goods_image","store_id","buyer_id","shop_id","goods_type","extend_num","extend_code","extend_price","_pk"=>"id");
   
}