<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\controller\api\Base;
use app\model\Collection as CollectionModel;
use app\model\Article as ArticleModel;
class Collection extends Base
{
    /**
     * 收藏列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $user_id = request()->currentUser->id;
        $page = request()->param('page',1);

        // 获取收藏ID
        $article_ids = CollectionModel::page($page,10)
        ->order("id","desc")
        ->where("user_id",$user_id)
        ->column("article_id");

        $data = ArticleModel::getArticleList($page,[
            ['id','in',$article_ids]
        ]);

        return apiSuccess('ok',$data);

    }

    /**
     * 收藏帖子
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save($article_id)
    {
        $count = CollectionModel::addCollection($article_id);
        if($count){
            return apiSuccess("收藏成功",$count);
        }
        return apiFail("收藏失败");
    }


    /**
     * 取消收藏帖子
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($article_id)
    {
        $count = CollectionModel::removeCollection($article_id);
        return apiSuccess("取消收藏成功",$count);
    }
}
