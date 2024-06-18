<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Adsense extends Model
{
    // 获取广告列表
    public function getList($type){
        return $this->where('type',$type)->field("src,url")->select();
    }
}
