<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\model\Comment as CommentModel;
use app\controller\api\Base;
class Comment extends Base
{
    /**
     * 评论/回复列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $hidden = ["update_time"];
        $param = request()->param();
        $isreply = array_key_exists('comment_id',$param);
        $where = [];
        // 回复列表
        if($isreply){
            $where = [
                ["comment_id","=",$param["comment_id"]]
            ];
        } else {
            $hidden[] = "quote";
            $where = [
                ["article_id","=",$param["article_id"]],
                ["comment_id","=",null],
            ];
        }
        
        // // 获取所有我拉黑/被我拉黑的用户ID
        // $BlackUserIds = getBlackUsers();
        // if(count($BlackUserIds) > 0){
        //     $where = [
        //         ["user_id","not in", $BlackUserIds]
        //     ];
        // }

        
        $page = request()->param('page',1);
        $data = CommentModel::page($page,10)
        ->with("user")
        ->order("id","desc")
        ->where($where)
        ->hidden($hidden)
        ->paginate(10);

        return apiSuccess('ok',$data);
    }

    /**
     * 发布帖子评论/回复评论
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        // ApiException('评论功能目前仅限购买过课程的学员使用。详情可观看课程视频演示了解。');
        
        $user = request()->currentUser;
        $content = request()->param('content');
        $param = request()->param();
        // 回复
        $res = null;
        if(array_key_exists('reply_id',$param)){
            $id = input('reply_id');
            $res = CommentModel::addReply($id,$content);
        } else {
            $id = input('article_id');
            $res = CommentModel::addComment($id,$content);
        }
        
        if($res){
            return apiSuccess('发布成功',$res);
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
        // 获取登录用户ID
        $currentUser_id = getCurrentUserIdByToken();

        $data = CommentModel::with([
            "user",
            // 判断是否已关注该作者
            "isFollowCurrentUser" => function(\think\Db\Query $query) use($currentUser_id){
                $query->where('follow.user_id', $currentUser_id);
            }
        ])->where('id',$id)->find();
        if(!$data){
            return apiFail("没有找到数据",404);
        }
        $data->isfollow = $data->isFollowCurrentUser ? true : false;
        $data->hidden(["update_time","quote","user","isFollowCurrentUser"]);
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
        $user = request()->currentUser;
        $comment = CommentModel::field('id,user_id,article_id,comment_id')->find($id);
        if(!$comment){
            ApiException('评论不存在');
        }
        if($comment->user_id != $user->id){
            ApiException('没有权限删除');
        }
        if($comment->delete()){
            return apiSuccess('删除成功');
        }
        ApiException('删除失败');
    }
}
