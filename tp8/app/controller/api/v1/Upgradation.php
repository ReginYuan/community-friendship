<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\model\Upgradation as UpgradationModel;
use app\controller\api\Base;
class Upgradation extends Base
{
    /**
     * 返回更新内容
     * @return \think\Response
     */
    public function index()
    {
        $param = request()->param();
        $appVersion = $param["appVersion"];
        $wgtVersion = $param["wgtVersion"];

        $data = UpgradationModel::where([
            'appid'=>$param['appid'],
            'platform'=>$param['platform'],
			'stable_publish'=> 1
        ])->order('create_time desc')->select();

        if($data->isEmpty()){
            return ApiSuccess("暂无更新",null);
        }

        $appVersionInDb = null;
        $wgtVersionInDb = null;
        $data->each(function($item) use(&$appVersionInDb,&$wgtVersionInDb){
            if($item->type === "native_app"){
                $appVersionInDb = $item;
            } else if($item->type === "wgt"){
                $wgtVersionInDb = $item;
            }
        });

        $hasAppPackage = $appVersionInDb != null;
        $hasWgtPackage = $wgtVersionInDb != null;

        // 取两个版本中版本号最大的包，版本一样，使用wgt包
        $stablePublishDb;  
        if ($hasAppPackage) {  
            if ($hasWgtPackage) {  
                $stablePublishDb = ($compare($wgtVersionInDb->version, $appVersionInDb->version) >= 0) ? $wgtVersionInDb : $appVersionInDb;  
            } else {  
                $stablePublishDb = $appVersionInDb;  
            }  
        } else {  
            $stablePublishDb = $wgtVersionInDb;  
        }  

        $version = $stablePublishDb->version;
        $min_uni_version = $stablePublishDb->min_uni_version;

        // 库中的version必须满足同时大于appVersion和wgtVersion才行，因为上次更新可能是wgt更新
        $appUpdate = $this->compare($version, $appVersion) === 1; // app包可用更新
        $wgtUpdate = $this->compare($version, $wgtVersion) === 1; // wgt包可用更新

        if ($appUpdate && $wgtUpdate) {
            // 判断是否可用wgt更新
            if ($min_uni_version && $this->compare($min_uni_version, $appVersion) < 1) {
                return ApiSuccess("wgt",array_merge($stablePublishDb->toArray(),[
                    'code'=>101,
                    "message"=>"wgt更新"
                ]));
            } else if ($hasAppPackage && $this->compare($appVersionInDb->version, $appVersion) === 1) {
                return ApiSuccess("app",array_merge($appVersionInDb->toArray(),[
                    'code'=>102,
                    "message"=>"app更新"
                ]));
            }
        }

        return ApiSuccess("暂无更新",null);
    }
    /**
     * 对比版本号
     * 支持比对	("3.0.0.0.0.1.0.1", "3.0.0.0.0.1")	("3.0.0.1", "3.0")	("3.1.1", "3.1.1.1") 之类的
     * @param {string} v1
     * @param {string} v2
     * v1 > v2 return 1
     * v1 < v2 return -1
     * v1 == v2 return 0
     */
    public function compare($v1 = '0', $v2 = '0') {  
        $v1 = explode('.', strval($v1));  
        $v2 = explode('.', strval($v2));  
        $minVersionLens = min(count($v1), count($v2));  
      
        $result = 0;  
        for ($i = 0; $i < $minVersionLens; $i++) {  
            $curV1 = floatval($v1[$i]);  
            $curV2 = floatval($v2[$i]);  
      
            if ($curV1 > $curV2) {  
                $result = 1;  
                break;  
            } elseif ($curV1 < $curV2) {  
                $result = -1;  
                break;  
            }  
        }  
      
        if ($result === 0 && count($v1) !== count($v2)) {  
            $v1BiggerThenv2 = count($v1) > count($v2);  
            $maxLensVersion = $v1BiggerThenv2 ? $v1 : $v2;  
            for ($i = $minVersionLens; $i < count($maxLensVersion); $i++) {  
                $curVersion = floatval($maxLensVersion[$i]);  
                if ($curVersion > 0) {  
                    $result = $v1BiggerThenv2 ? 1 : -1;  
                    break;  
                }  
            }  
        }  
      
        return $result;  
    }
}
