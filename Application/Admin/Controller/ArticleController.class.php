<?php
namespace Admin\Controller;

class ArticleController extends BaseController
{
    public function _initialize()
    {
        parent::_initialize();
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
            $this->assign('article_item', $article_item);
            $this->assign('btn_ok_act', 'update');
        } else {
            $this->assign('btn_ok_act', 'add');
        }
        $this->display();
    }

    /**
     * @函数    add
     * @功能    文章添加完成，写入数据库
     */
    public function add()
    {
        $article = D('Article');
        if ($article->create()) {

            $article->message = $_POST['message'];
            $article->author = session('proxyName')."(".session('proxyId').")";

            //将文章写入数据库
            if ($article->add()) {
                $this->success('文章添加成功', U('article/article_list'));
            } else {
                $this->error('文章添加失败，返回上级页面');
            }

        } else {
            $this->error($article->getError());
        }
    }

    /**
     * @函数    delete
     * @功能    删除文章
     */
    public function delete()
    {
        $article = M('article');
        if ($article->delete($_GET['id'])) {
            $this->success('文章删除成功');
        } else {
            $this->error($article->getLastSql());
        }
    }

    /**
     * @函数    edit
     * @功能    删除文章
     */
    public function edit()
    {
        if ($_GET['id']) {
            redirect(U('admin/article/index/id/' . $_GET['id']), 0, '编辑文章');
        }
    }

    /**
     * @函数    update
     * @功能    更新修改后的文章到数据库
     */
    public function update()
    {
        $article = M('Article');
        $data = array('subject' => $_POST['subject'], 'message' => $_POST['message'], 'lastmodifytime' => time());
        $id = $_POST['id'];
        $row = $article->where('id=' . $id)->setField($data); // 根据条件保存修改的数据
        if (false!==$row) {
            $this->success('文章编辑成功', U('article/article_list'));
        } else {
            $this->error('文章编辑失败，返回上级页面');
        }
    }
}