<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Upgradation extends Model
{
    public function getPlatformAttr($value){
        return explode(',', $value);
    }
    public function setPlatformAttr($value){
        return is_array($value) ? join(',', $value) : $value;
    }
    public function getStablePublishAttr($value){
        return $value ? true : false;
    }
    public function setStablePublishAttr($value){
        return $value ? 1 : 0;
    }
    // is_mandatory
    public function getIsMandatoryAttr($value){
        return $value ? true : false;
    }
    public function setIsMandatoryAttr($value)
    {
        return $value ? 1 : 0;
    }
    // is_silently
    public function getIsSilentlyAttr($value){
        return $value ? true : false;
    }
    public function setIsSilentlyAttr($value)
    {
        return $value ? 1 : 0;
    }
}
