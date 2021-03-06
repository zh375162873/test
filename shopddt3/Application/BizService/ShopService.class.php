<?php

namespace BizService;

/**
 * 多商城Service
 *
 * @author 谢林
 */
class ShopService extends BaseService
{

    /**
     * 代理商平台登录状态判断
     */
    public function checkShop()
    {
        if (session('proxyKeyStatus') != 'on') {
            $url = 'location:' . C('WIFI_URL') . '/proxy/default/index';
            header($url);
            exit;
        }
    }


    /**
     * 根据shop_id获取商城信息
     * @param $shop_id
     */
    public function get_shop_info($shop_id, $columns = null)
    {
        if (empty($columns)) {
            $columns = "*";
        } else if (is_array($columns)) {
            $columns = implode(',', $columns);
        } else {
            $columns = $columns;
        }
        $query = "SELECT $columns FROM ddt_shop WHERE shop_id=%d";
        return M()->query($query, $shop_id);
    }

    /**
     * 根据域名获取商城ID
     * @param $domain
     * @param null $columns
     * @return mixed
     */
    public function get_shop_info_by_domain($domain,$columns = null)
    {
        if (empty($columns)) {
            $columns = "*";
        } else if (is_array($columns)) {
            $columns = implode(',', $columns);
        } else {
            $columns = $columns;
        }
        $query = "SELECT $columns FROM ddt_shop WHERE shop_domain='%s'";
        return M()->query($query, $domain);
    }
    /**
     * 根据代理商id获取商城信息
     * @param $proxy_id
     */
    public function get_shop_info_by_proxy($proxy_id, $columns = null)
    {
        if (empty($columns)) {
            $columns = "*";
        } else if (is_array($columns)) {
            $columns = implode(',', $columns);
        } else {
            $columns = $columns;
        }
        $query = "SELECT $columns FROM ddt_shop WHERE member_id=%d";
        return M()->query($query, $proxy_id);
    }

    /**
     * 获取商城列表
     * @todo 未进行条件查询
     * @param array $param
     * @param int $ispagenation
     * @return array
     */
    public function shop_list($param = array(), $ispagenation = true)
    {
        $query = "SELECT * FROM ddt_shop";
        if ($ispagenation) {
            $query_count = "SELECT count(*) as cnt FROM ddt_shop";
            $count = M()->query($query_count);
            return mypage($count[0]['cnt'], $query);
        } else {
            return M()->query($query);
        }
    }

    /**更新商城信息
     * @param $data
     * @return bool|false|int
     */
    public function update_shop($data)
    {
        if (empty($data['shop_id'])) {
            return false;
        }
        $sql = "UPDATE ddt_shop SET " .
            " shop_name = '%s' ," .
            " shop_company_name = '%s' ," .
            " shop_title = '%s' ," .
            " shop_img = '%s' ," .
            " shop_address = '%s' ," .
            " shop_keywords = '%s' ," .
            " kefu_phone = '%s' ," .
            " shop_email = '%s' ," .
            " shop_phone = '%s'" .
            " WHERE shop_id = '%d'";

        return M()->execute($sql,$data['shop_name'],$data['shop_company_name'],$data['shop_title'],$data['shop_img'],$data['shop_address'],$data['shop_keywords'],$data['kefu_phone'],$data['shop_email'],$data['shop_phone'],$data['shop_id']);
    }
}