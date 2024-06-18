<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\model\Blacklist as BlacklistModel;
use app\controller\api\Base;
class Blacklist extends Base
{
    /**
     * 我的黑名单列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $user_id = request()->userId;
        $page = request()->param('page',1);
        $data = BlacklistModel::page($page,10)
        ->with("blackuser")
        ->order("id","desc")
        ->where("user_id",$user_id)
        ->withAttr('id', function($value, $data){
            return $data["black_id"];
        })
        ->hidden(["black_id","user_id","update_time"])
        ->paginate(10);

        // 获取所有被拉黑的用户ID
        $userIds = [];
        $data->each(function($item) use(&$userIds){
            $userIds[] = $item->id;
        });
        // 判断用户是否关注了这些被拉黑的用户
        $ds = \think\facade\Db::name('follow')->where([
            "follow_id" => $userIds,
            "user_id" => $user_id
        ])->column('follow_id');

        $data = $data->filter(function($item) use($ds){
            $item->isfollow = in_array($item->user_id, $ds) ? true :  false;
            if(!$item->desc){
                $item->desc = "暂无描述~";
            }
            return $item;
        });

        return apiSuccess('ok',$data);
    }

    /**
     * 加入黑名单
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function save($id)
    {
        if(BlacklistModel::isBlacklist($id)){
            return apiFail("已在黑名单中");
        }
        
        BlacklistModel::addBlacklist($id);
        return apiSuccess("加入黑名单成功");
    }

    
    /**
     * 移除黑名单
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(BlacklistModel::isBlacklist($id)){
            BlacklistModel::removeBlacklist($id);
            return apiSuccess("移除黑名单成功");
        }
        return apiFail("未在黑名单中");
    }
}
