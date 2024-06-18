<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\model\Adsense as AdsenseModel;
use app\controller\api\Base;
class Adsense extends Base
{
    /**
     * 显示资源列表
     * @return \think\Response
     */
    public function index()
    {
        $type = request()->param("type");
        $data =  (new AdsenseModel())->getList($type);
        return apiSuccess("成功",$data);
    }
}
