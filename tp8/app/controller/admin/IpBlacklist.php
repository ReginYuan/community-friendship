<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\IpBlacklist as IpBlacklistModel;
class IpBlacklist extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $keyword = request()->param('keyword');
        $page = request()->param("page");
        $query = IpBlacklistModel::page($page,10)
        ->order("create_time","desc");

        if($keyword){
            $query = $query->where('ip', 'like', '%' . $keyword . '%');
        }

        $data = $query->paginate(10);
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
        $ip = $request->param("ip");
        $ip2 = preg_replace('/\d+$/', '*', $ip);
        $d = IpBlacklistModel::whereOrIp($ip)->whereOrIp($ip2)->find();
        if($d){
            ApiException("该IP已在黑名单中");
        }
        $res = (new IpBlacklistModel())->save([
            "ip"=>$ip
        ]);
        if($res){
            return apiSuccess('添加成功');
        }
        ApiException("添加失败");
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete()
    {
        $ids = request()->param("ids");
        $res = IpBlacklistModel::destroy($ids);
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }
}
