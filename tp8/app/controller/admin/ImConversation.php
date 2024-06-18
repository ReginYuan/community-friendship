<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\ImConversation as ImConversationModel;
class ImConversation extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = (int)(request()->param("page"));
        $keyword = request()->param("keyword");
        $data = ImConversationModel::where("last_msg_note","like","%".$keyword."%")->with(["user2","target2"])->page($page,10)->order("id","desc")->paginate(10);
        return apiSuccess("成功",$data);
    }

    /**
     * 删除指定资源
     */
    public function delete()
    {
        $ids = request()->param("ids");
        $res = ImConversationModel::destroy($ids);
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }
}
