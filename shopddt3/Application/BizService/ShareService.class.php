<?php

namespace BizService;

/**
 * 分享
 *
 * @author 谢林
 */
class ShareService extends BaseService
{

    /**
     * 设置微信分享设置
     * @param $data
     * @return false|int
     */
    public function share_set($data)
    {
        $create_time = time();
        $query = "SELECT 1 FROM ddt_share_set WHERE shop_id=%d";
        $row = M()->query($query,$data['shop_id']);
        if ($row) {
            $sql = "UPDATE ddt_share_set SET " .
                "shop_thumb = '%s' ," .
                "shop_title = '%s' ," .
                "shop_desc = '%s' ," .
                "goods_type = %d , " .
                "goods_thumb = '%s' ," .
                "goods_title = '%s' , " .
                "is_show_title = %d ," .
                "goods_desc = '%s' , " .
                "create_time = %d " .
                " WHERE shop_id = %d";
            return M()->execute($sql,$data['shop_thumb'], $data['shop_title'], $data['shop_desc'], $data['goods_type'], $data['goods_thumb'], $data['goods_title'], $data['is_show_title'], $data['goods_desc'], $create_time,$data['shop_id']);
        } else {
            $sql = "INSERT INTO ddt_share_set (shop_id,shop_thumb,shop_title,shop_desc,goods_type,goods_thumb,goods_title,is_show_title,goods_desc,create_time) VALUES (%d,'%s','%s','%s',%d,'%s','%s',%d,'%s',%d)";
            return M()->execute($sql, $data['shop_id'], $data['shop_thumb'], $data['shop_title'], $data['shop_desc'], $data['goods_type'], $data['goods_thumb'], $data['goods_title'], $data['is_show_title'], $data['goods_desc'], $create_time);
        }
    }

    /**
     * 获取分享设置
     * @param $shop_id
     * @return mixed
     */
    public function get_share_set($shop_id)
    {
        $query = "SELECT shop_id, shop_thumb, shop_title, shop_desc, goods_type, goods_thumb, goods_title, is_show_title, goods_desc, create_time FROM ddt_share_set WHERE shop_id=%d";
        return M()->query($query, $shop_id);
    }
}