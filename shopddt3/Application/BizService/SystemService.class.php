<?php

namespace BizService;

/**
 * 商城设置
 *
 * @author 谢林
 */
class SystemService extends BaseService
{
    /**
     * 设置系统设置
     * @param $shop_id 商城ID
     * @param $code 编码
     * @param $value 值
     * @return bool|false|int
     */
    public function update_set($shop_id, $code, $value)
    {
        if ($shop_id == '' || $code == '') {
            return false;
        }
        $query = "SELECT `code`, `value` FROM ddt_setting WHERE shop_id=%d AND `code`='%s'";
        //echo $query;
        if (M()->query($query, $shop_id, $code)) {
            $sql = "UPDATE ddt_setting SET `value` = '%s' WHERE `shop_id` = %d AND `code`='%s'";
            //echo $sql;exit;
            return M()->execute($sql, $value, $shop_id, $code);
        } else {
            $sql = "INSERT INTO ddt_setting (`shop_id`,`code`,`value`) VALUES (%d,'%s','%s')";
            //echo $sql;exit;
            return M()->execute($sql, $shop_id, $code, $value);
        }
    }

    /**获取系统设置
     * @param $shop_id
     * @param $code
     * @return mixed
     */
    public function get_code($shop_id, $code)
    {
        $query = "SELECT `value` FROM ddt_setting WHERE shop_id=%d AND `code`='%s'";
        $set_info = M()->query($query, $shop_id, $code);
        return $set_info[0]['value'];
    }
}