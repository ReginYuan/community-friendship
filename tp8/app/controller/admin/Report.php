<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\Report as ReportModel;
class Report extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = request()->param("page");
        $data = ReportModel::page($page,10)->with(["user","reportUser"])->order("id","desc")->paginate(10);
        return apiSuccess("成功",$data);
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
        $report = ReportModel::find($id);
        if(!$report){
            ApiException("当前举报记录不存在！");
        }
        if($report->state != 'pending'){
            ApiException("当前举报已经处理过了！");
        }
        $report->state = $param['state'];
        if($report->save()){
            return apiSuccess('处理成功');
        }
        ApiException("处理失败");
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
        $res = ReportModel::destroy($ids);
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }
}
