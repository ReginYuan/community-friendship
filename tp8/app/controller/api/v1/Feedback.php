<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\controller\api\Base;
use app\model\Feedback as FeedbackModel;
class Feedback extends Base
{
    /**
     * 获取用户反馈列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $user_id = request()->userId;
        $page = request()->param('page',1);
        $data = FeedbackModel::page($page,10)
        ->where("user_id",$user_id)
        ->with("user")
        ->hidden(["update_time","user_id"])
        ->order("id","desc")
        ->paginate(10)
        ->filter(function($item){
            if($item->type == "worker"){
                $item->name = "官方人员";
                $item->avatar = (request()->root(true))."/static/default-avatar.png";
            }
            return $item;
        });
        return apiSuccess('ok',$data);
    }


    /**
     * 用户反馈
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $content = request()->param('content');
        $images = request()->param('images');
        $res = FeedbackModel::addFeedback($content,$images);
        if($res){
            return apiSuccess('反馈成功');
        }
        return apiFail('反馈失败');
    }

}
