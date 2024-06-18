<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Feedback extends Model
{
    // 自动将图片数组转成字符串
    public function setImagesAttr($value)
    {
        if(is_array($value)){
            return implode(',', $value);
        }
        return $value;
    }

    // 自动将图片字符串转成数组
    public function getImagesAttr($value)
    {
        if($value){
            return explode(',', $value);
        }
        return [];
    }

    // 关联用户
    public function user()
    {
        return $this->belongsTo('User','user_id','id')->bind([
            'name',
            'avatar'
        ]);
    }
    
    // 添加反馈
    public static function addFeedback($content,$images)
    {
        $data = [
            'content' => $content,
            'images' => $images,
            'user_id' => request()->currentUser->id
        ];
        $res = self::create($data);
        if($res){
            return true;
        }
        return false;
    }
}
