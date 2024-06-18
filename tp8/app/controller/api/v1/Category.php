<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\model\Category as CategoryModel;
use app\controller\api\Base;
class Category extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $type = request()->param("type");
        $data = CategoryModel::type($type)->select();
        return apiSuccess("成功",$data);
    }
}
