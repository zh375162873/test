<?php
namespace Home\Controller;
use Think\Controller;
class ArticleController extends Controller
{
    public function _initialize(){
        $shop_info = shop_info(session('city'));
        $this->assign("shop_base_info",$shop_info);
    }

    public function article_list(){
        //实例化文章模型
        $news=M('article');
        $count = $news->where("1=1")->count();
        $query = "SELECT id,author,subject,message,createtime,lastmodifytime FROM ddt_article ORDER BY id DESC";
        $news = mypage($count,$query);
        $news_list = $news['list'];
        foreach($news_list as $key=>$value){
            $news_list[$key]['createtime']=date("Y-m-d H:i:s",$value['createtime']);
            $news_list[$key]['lastmodifytime']=$value['lastmodifytime']==true?date("Y-m-d H:i:s",$value['lastmodifytime']):"暂未修改";
        }
        $this->assign('news_list',$news_list);
        $this->assign('page',$news['page']);
        $this->display();
    }


    /**
     * @函数    index
     * @功能    显示添加文章主页面
     */
    public function index()
    {
        if ($id = (int)$_GET['id']) {
            $article = M('article');
            $article_item = $article->where("id=$id")->find();
            if($article_item){
                $this->assign('article_item', $article_item);
            }else{
                $this->error("param error");
            }
        }else{
            $this->error("param error");
        }
        $this->display();
    }
}