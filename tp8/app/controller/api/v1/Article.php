<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\model\Article as ArticleModel;
use app\model\Topic as TopicModel;
use app\model\Follow as FollowModel;
use app\model\ArticleReadLog;
use app\model\Collection;
use app\controller\api\Base;
class Article extends Base
{
    /**
     * 显示话题/分类下的文章列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $param = request()->param();
        // 排序
        $orderBy = request()->param("order");
        $order = "id desc";
        if($orderBy == "new"){
            $order = "create_time desc";
        } elseif($orderBy == "hot"){
            $order = "ding_count,id desc";
        }
        // 分类
        $value = request()->param('category_id',0);
        $key = "category_id";
        // 话题
        if(array_key_exists('topic_id',$param)){
            $value = request()->param('topic_id',0);
            $key = "topic_id";
        }
        // 用户
        if(array_key_exists('user_id',$param)){
            $value = request()->param('user_id',0);
            $key = "user_id";
        }

        $page = request()->param('page',1);
        $where = [];
        if($value != 0){
            $where = [
                $key => $value,
            ];
        }

        $data = [];
        if($key == "category_id" && $value == 0){
            // 获取关注用户的帖子列表
            $data = ArticleModel::getMyFollowArticleList($page,$order);
        } else {
            $data = ArticleModel::getArticleList($page,$where,$order);
        }
        
        return apiSuccess('ok',$data);
    }

    /**
     * 搜索资源列表
     *
     * @return \think\Response
     */
    public function search()
    {
        $keyword = request()->param('keyword');
        $page = request()->param('page',1);
        $data = ArticleModel::getArticleList($page,[
            ['content','like','%'. $keyword. '%']
        ]);
        return apiSuccess('ok',$data);
    }

    /**
     * 发布帖子
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        // ApiException('发帖功能目前仅限购买过课程的学员使用，详情可观看课程视频演示了解。');
        
        $user = request()->currentUser;
        $param = $request->param();
        $data = [
            'category_id' => $param['category_id'],
            'user_id' => $user->id,
            'content' => $param['content'],
            'images' => $param['images'],
        ];
        // 话题是否存在
        if(array_key_exists("topic_id",$param) && $param['topic_id'] > 0){
            if(!(TopicModel::find($param['topic_id']))) ApiException("话题不存在");
            $data['topic_id'] = $param['topic_id'];
        }
        $article = new ArticleModel();
        $res = $article->save($data);
        if($res){
            return apiSuccess('发布成功');
        }
        return apiFail('发布失败');
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $id = request()->param('id',0);
        $data = ArticleModel::getArticleById($id);
        if(!$data){
            return apiFail('帖子不存在');
        }
        // 更新阅读记录
        $data = ArticleReadLog::updateReadLog($id,$data);
        // 判断当前用户是否收藏该帖子
        $data->isCollect = Collection::isCurrentUserCollectArticle($id);
        return apiSuccess('ok',$data);
    }


    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        // 演示数据禁止操作
        isDemoData($id,[
            1775,1774,1773,1772,1771,1770,1769
        ]);

        $user = request()->currentUser;
        $article = ArticleModel::field('id,user_id')->find($id);
        if(!$article){
            ApiException('帖子不存在');
        }
        if($article->user_id != $user->id){
            ApiException('没有权限删除');
        }
        if($article->delete()){
            return apiSuccess('删除成功');
        }
        ApiException('删除失败');
    }

    // 获取我关注的人的帖子列表
    public function getFollowArticles(){
        $header = request()->header();
        if(!array_key_exists('token',$header)){
            return apiSuccess('ok');
        }
        $user = cache( $header['token']);
        if(!$user){
            return apiSuccess('ok');
        }
        // 获取我关注的人的id
        $follow_ids = FollowModel::where('user_id',$user["id"])->column('follow_id');
        // 获取他们的帖子列表
        $query = ArticleModel::page(1,10)->order("id","desc");
        if($follow_ids){
            $query = $query->whereIn('user_id',$follow_ids);
        }
        return apiSuccess('ok',$query->select());
    }
}
