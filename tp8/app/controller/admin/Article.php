<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\Article as ArticleModel;
use app\model\Topic as TopicModel;
class Article extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = request()->param("page");
        $keyword = request()->param('keyword');
        $where = [];
        if($keyword){
            $where = [
                ['content','like','%'. $keyword. '%']
            ];
        }
        $data = ArticleModel::page($page,10)
        ->where($where)
        ->with(["user","topic","category"])
        ->order("id","desc")
        ->paginate(10);
        return apiSuccess("成功",$data);
    }


    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
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
            if(!(TopicModel::find($param['topic_id']))) {
                ApiException("话题不存在");
            }
            $data['topic_id'] = $param['topic_id'];
        }
        $article = new ArticleModel();
        $res = $article->save($data);
        if($res){
            return apiSuccess('发布成功');
        }
        ApiException("发布失败");
    }


    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $param = $request->param();
        $data = [
            'category_id' => $param['category_id'],
            'content' => $param['content'],
            'images' => $param['images'],
        ];
        // 话题是否存在
        if(array_key_exists("topic_id",$param) && $param['topic_id'] > 0){
            if(!(TopicModel::find($param['topic_id']))) {
                ApiException("话题不存在");
            }
            $data['topic_id'] = $param['topic_id'];
        }
        $res = ArticleModel::update($data, ['id' => $id]);
        if($res){
            return apiSuccess('修改成功');
        }
        ApiException("修改失败");
    }

    /**
     * 删除资源
     *
     * @param  array  $ids
     * @return \think\Response
     */
    public function delete()
    {
        $ids = request()->param("ids");
        $res = ArticleModel::destroy($ids);
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }
}
