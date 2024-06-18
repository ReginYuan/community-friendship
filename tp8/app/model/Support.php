<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Support extends Model
{
    // 新增/更新之后
    public static function onAfterWrite($data)
    {
        Article::updateSupportCount($data->article_id);
    }

    // 删除之后
    public static function onAfterDelete($data)
    {
        Article::updateSupportCount($data->article_id);
    }

    // 获取顶踩操作
    public function getUserSupportActionAttr($value,$data)
    {
        return $data["type"] == 1? 'ding' : 'cai';
    }

    // 顶踩操作
    public static function handleAction($article_id,$type){
        $action = $type == 1? '顶' : '踩';
        $user = request()->currentUser;
        // 踩
        $support = self::where('user_id',$user->id)
        ->where('article_id',$article_id)
        ->find();

        // 之前操作过
        if($support){
            // 如果是一样的操作，则取消
            if($support->getData('type') == $type){
                if($support->delete()){
                    return "取消{$action}成功";
                }
                ApiException("取消{$action}失败");
            }
            // 如果操作不一样，则修改
            if($support->save(['type' => $type])){
                return "{$action}成功";
            }
        }

        // 之前没有操作过
        if(self::create([
            'user_id' => $user->id,
            'article_id' => $article_id,
            'type' => $type
        ])){
            return "{$action}成功";
        }
        
        // $support = new self();
        // $support->user_id = $user->id;
        // $support->article_id = $article_id;
        // $support->type = $type;
        // if($support->save()){
        //     return "{$action}成功";
        // }

        ApiException("{$action}失败");
    }
}
