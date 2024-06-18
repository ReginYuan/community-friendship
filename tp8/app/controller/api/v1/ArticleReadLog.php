<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\model\ArticleReadLog as ArticleReadLogModel;
use app\model\Article as ArticleModel;
use app\controller\api\Base;
class ArticleReadLog extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $user_id = getCurrentUserIdByToken();
        $page = request()->param('page',1);
        $where = [];
        if($user_id){
            $where["user_id"] = $user_id;
        } else {
            $where["ip"] = request()->ip();
        }

        $data = ArticleReadLogModel::page($page,10)
        ->where($where)
        ->field("article_id,update_time")
        ->with([
            "article"=>function($query){
                ArticleModel::withArticleDetail($query->hidden([ "content"]));
            }
        ])
        ->order("update_time","desc")
        ->paginate(10)
        ->map(function($item){
            return ArticleModel::formatArticleItem($item->article);
        });

        return apiSuccess('ok',$data);
    }

}
