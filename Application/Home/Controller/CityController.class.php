<?php

namespace Home\Controller;

use Think\Controller;

/**
 * Class 分站切换
 * @package Home\Controller
 */
class CityController extends Controller
{
    public function index()
    {
        $city = I("city", 'xian');
        $ck_flag = I("ck_flag");
        if(!self::isMobile() && !$ck_flag){
            $this->assign("domain",$_SERVER['HTTP_HOST']);
            $this->display("cookie_enable");
        }
        //todo 后期考虑加缓存
        $shop_service = new \BizService\ShopService();
        $shop_info = $shop_service->get_shop_info_by_domain($city, array('shop_id', 'shop_title', 'member_id', 'shop_company_name', 'shop_keywords'));
        if ($shop_info) {
            cookie("city", $city);
            session('proxyId', $shop_info[0]['member_id']);//代理ID
            session('shopId', $shop_info[0]['shop_id']);//商城ID
            session('city', $city);//城市分站
            if(self::isMobile() || $ck_flag){
                redirect(U('Index/index'));
            }
        } else {
            $this->error("很抱歉，该城市暂未开通！");
        }

    }

    private function isMobile(){
        $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';
        $mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
        $mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');

        $found_mobile=self::CheckSubstrs($mobile_os_list,$useragent_commentsblock) ||
            self::CheckSubstrs($mobile_token_list,$useragent);

        if ($found_mobile){
            return true;
        }else{
            return false;
        }
    }

    private function CheckSubstrs($substrs,$text){
        foreach($substrs as $substr)
            if(false!==strpos($text,$substr)){
                return true;
            }
        return false;
    }
}