<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\Adsense as AdsenseModel;
class Adsense extends Base
{
    /**
     * 显示资源列表
     * @return \think\Response
     */
    public function index()
    {
        $page = request()->param("page");
        $data = AdsenseModel::page($page,10)->order("id","desc")->paginate(10);
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
            'src' => $param['src'],
            'url' => $param['url'],
            'type' => $param['type'],
        ];
        $adsense = new AdsenseModel();
        $res = $adsense->save($data);
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
            'src' => $param['src'],
            'url' => $param['url'],
            'type' => $param['type'],
        ];
        $res = AdsenseModel::update($data, ['id' => $id]);
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
        $res = AdsenseModel::destroy($ids);
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }
}
