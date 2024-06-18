<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\model\Report as ReportModel;
use app\model\User as UserModel;
use app\controller\api\Base;
class Report extends Base
{
    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $user_id = request()->userId;
        $param = $request->param();
        $report_uid = $param["report_uid"];

        if(!UserModel::isUserExist('id',$report_uid)){
            ApiException("被举报的用户不存在");
        }

        $where = [
            'user_id' => $user_id,
            'report_uid' => $report_uid
        ];

        $r = ReportModel::where($where)->value("id");
        if($r){
            ApiException("您已举报过该用户，请勿重复提交");
        }

        $where["content"] = $param["content"];
        $res = ReportModel::create($where);
        if($res){
            return apiSuccess("提交成功，等待管理员处理");
        }
        ApiException("提交失败");
    }

}
