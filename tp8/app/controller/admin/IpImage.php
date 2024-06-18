<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\IpImage as IpImageModel;
use app\model\IpBlacklist as IpBlacklistModel;
class IpImage extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = request()->param("page");
        $only_nocheck = request()->param("only_nocheck");
        $where = [];
        if($only_nocheck == 1){
            $where['status'] = 2;
        }
        $query = IpImageModel::page($page,10);

        $data = $query->where($where)
        ->with("user")
        ->order("create_time","desc")
        ->paginate(10);

        // 获取被封禁的ip地址
        $whereIps = [];
        $data->each(function($item) use(&$whereIps){
            $whereIps[] = $item->ip;
            $whereIps[] = preg_replace('/\d+$/', '*', $item->ip);
        });
        $whereIps = array_unique($whereIps);

        $blackIps = IpBlacklistModel::where("ip","in",$whereIps)
        ->column("ip");

        // 添加ip状态
        $data->map(function($item) use($blackIps){
            if(!$item->user_id){
                $item->name = "游客";
            }
            $ip1 = $item->ip;
            $ip2 = preg_replace('/\d+$/', '*', $item->ip);
            $item->in_ip_blacklist = in_array($ip1,$blackIps) || in_array($ip2,$blackIps);
            return $item;
        });

        return apiSuccess("成功",$data);
    }

    /**
     * 保留/删除图片
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request)
    {
        $ids = request()->param("ids");

        if (count($ids) == 0) {
            ApiException("请选择图片");
        }

        $status = $request->param("status");

        $ms = IpImageModel::where("id","in",$ids)->select();
        $count = 0;
        $ms->each(function($item) use($status, &$count) {
            $item->status = $status;
            $item->save();
            $count++;
        });

        return apiSuccess("操作成功", $count);
    }

    /**
     * 删除指定资源
     */
    public function delete()
    {
        $ids = request()->param("ids");
        $res = IpImageModel::destroy($ids);
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }
}
