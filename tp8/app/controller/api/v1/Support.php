<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\controller\api\Base;
use app\model\Support as SupportModel;
class Support extends Base
{
    /**
     * 顶/踩操作
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function action(Request $request)
    {
        $user = request()->currentUser;
        $type = request()->param("type");
        $article_id = request()->param("article_id");
        // trace("type {$type}",'参数信息');
        $type = $type === "ding" ? 1 : 0;
        return apiSuccess(SupportModel::handleAction($article_id,$type));
    }
}
