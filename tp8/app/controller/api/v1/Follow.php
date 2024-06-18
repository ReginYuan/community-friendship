<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\controller\api\Base;
use app\model\Follow as FollowModel;
use app\model\User as UserModel;
class Follow extends Base
{
    /**
     * 指定用户的关注/粉丝列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $options = [
            "follows"=>[
                "with" => "follow",
                "where" => "user_id",
                "user_id" => "follow_id"
            ],
            "fans"=>[
                "with" => "fan",
                "where" => "follow_id",
                "user_id" => "user_id"
            ]
        ];
        // 关注列表
        $k = "follows";
        // 粉丝列表
        if(strpos(request()->url(), "fans") !== false){
            $k = "fans";
        } 
        $param = $options[$k];

        $user_id = request()->param('user_id',0);
        $page = request()->param('page',1);
        $query = FollowModel::page($page,10)->order("id","desc");
        if($user_id != 0){
            $query = $query->with($param["with"])->where($param["where"],$user_id);
        }

        // 将 follow_id/user_id 转为id
        $data = $query->withAttr('id', function($value, $data) use($param){
            return $data[$param["user_id"]];
        })
        ->hidden(["follow_id","update_time"])
        ->paginate(10);


        $ds = [];
        // if($k == "fans"){
        //     // 获取所有粉丝ID
        //     $fansIds = [];
        //     $data->each(function($item) use(&$fansIds){
        //         $fansIds[] = $item->user_id;
        //     });
            
        //     // 判断用户是否关注了这些粉丝
        //     $ds = \think\facade\Db::name('follow')->where([
        //         "follow_id" => $fansIds,
        //         "user_id" => $user_id
        //     ])->column('follow_id');
        // }
        
        // 获取当前登录用户ID
        $currentUserId = getCurrentUserIdByToken();
        // 获取所有粉丝ID
        $fansIds = [];
        $data->each(function($item) use(&$fansIds,$k){
            $fansIds[] = $k == "follows" ? $item->id : $item->user_id;
        });
        // 判断用户是否关注了这些粉丝
        $ds = \think\facade\Db::name('follow')->where([
            "follow_id" => $fansIds,
            "user_id" => $currentUserId
        ])->column('follow_id');

        $data = $data->filter(function($item) use($k,$ds,$currentUserId,$user_id){
            // 登录者本人
            if($currentUserId == $user_id){
                if($k == "follows"){
                    $item->isfollow = true;
                } else {
                    $item->isfollow = in_array($item->user_id, $ds) ? true :  false;
                }
            } 
            else {
                $item->isfollow = in_array($item->id, $ds) ? true :  false;
            }

            if(!$item->desc){
                $item->desc = "暂无描述~";
            }
            return $item;
        });
        return apiSuccess('ok',$data);
    }
    
    
    // /**
    //  * 指定用户的粉丝列表
    //  *
    //  * @return \think\Response
    //  */
    // public function fans()
    // {
    //     $user_id = request()->param('user_id',0);
    //     $page = request()->param('page',1);
    //     $query = FollowModel::page($page,10)->order("id","desc");
    //     if($user_id != 0){
    //         $query = $query->with("fan")->where("follow_id",$user_id);
    //     }

    //     // 将 follow_id/user_id 转为id
    //     $data = $query->withAttr('id', function($value, $data){
    //         return $data["user_id"];
    //     })
    //     ->hidden(["follow_id","update_time"])
    //     ->paginate(10);


    //     $ds = [];
    //     // 获取所有粉丝ID
    //     $fansIds = [];
    //     $data->each(function($item) use(&$fansIds){
    //         $fansIds[] = $item->user_id;
    //     });
        
    //     // 判断用户是否关注了这些粉丝
    //     $ds = \think\facade\Db::name('follow')->where([
    //         "follow_id" => $fansIds,
    //         "user_id" => $user_id
    //     ])->column('follow_id');

    //     $data = $data->filter(function($item) use($k,$ds){
    //         $item->isfollow = in_array($item->user_id, $ds) ? true :  false; 
    //         if(!$item->desc){
    //             $item->desc = "暂无描述~";
    //         }
    //         return $item;
    //     });
        
    //     return apiSuccess('ok',$data);
    // }

    /**
     * 关注用户
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function save($id)
    {
        if(FollowModel::addFollow($id)){
            return apiSuccess("关注成功");
        }
        return apiFail("关注失败");
    }

    
    /**
     * 取消关注用户
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(FollowModel::removeFollow($id)){
            return apiSuccess("取消关注成功");
        }
        return apiFail("取消关注失败");
    }
}
