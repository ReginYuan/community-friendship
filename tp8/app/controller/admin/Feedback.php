<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\Feedback as FeedbackModel;
class Feedback extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = request()->param("page");
        $data = FeedbackModel::page($page,10)->with("user")->order("id","desc")->paginate(10);
        return apiSuccess("成功",$data);
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
        $res = FeedbackModel::destroy($ids);
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }
}
