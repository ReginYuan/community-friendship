<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\UserActionLog as UserActionLogModel;
use app\model\IpBlacklist as IpBlacklistModel;
class UserActionLog extends Base
{
    // 不需要验证的方法
    protected $excludeValidateCheck = ["clear"];
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $keyword = request()->param('keyword');
        $page = request()->param("page");
        $query = UserActionLogModel::page($page,10);

        if($keyword){
            $query = $query->where('ip|url|notes|user_agent', 'like', '%' . $keyword . '%');
        }

        $data = $query->with("user")
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
     * 删除指定资源
     */
    public function delete()
    {
        $ids = request()->param("ids");
        $res = UserActionLogModel::destroy($ids);
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }

    // 清空所有
    public function clear(){
        $res = UserActionLogModel::where("id",">",0)->delete();
        if(!$res){
            ApiException("清空失败");
        }
        return apiSuccess("清空成功",);
    }
}
