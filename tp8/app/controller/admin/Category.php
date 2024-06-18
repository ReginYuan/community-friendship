<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\Category as CategoryModel;
class Category extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = request()->param("page");
        $limit = (int)(request()->param("limit",10));

        $query = CategoryModel::page($page,$limit);

        $type = request()->param("type");
        if($type){
            $query = $query->where("type",$type);
        }

        $data = $query->withCount(["articles","topics"])->order("id","desc")->paginate(100);
        return apiSuccess("成功",$data);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $param = $request->param();
        $data = [
            'title' => $param['title'],
            'type' => $param['type'],
            'status' => $param['status'],
        ];
        $category = new CategoryModel();
        $res = $category->save($data);
        if($res){
            return apiSuccess('发布成功');
        }
        ApiException("发布失败");
    }


    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $param = $request->param();
        $data = [
            'title' => $param['title'],
            'type' => $param['type'],
            'status' => $param['status'],
        ];
        $res = CategoryModel::update($data, ['id' => $id]);
        if($res){
            return apiSuccess('修改成功');
        }
        ApiException("修改失败");
    }

    /**
     * 删除资源
     *
     * @param  array  $ids
     * @return \think\Response
     */
    public function delete()
    {
        $ids = request()->param("ids");
        if(count($ids) > 1){
            ApiException("一次只能删除一个");
        }
        $res = CategoryModel::destroy($ids);
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }
}
