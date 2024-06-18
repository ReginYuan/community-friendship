<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\model\Topic as TopicModel;
use app\controller\api\Base;
class Topic extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $category_id = request()->param('category_id',0);
        $page = request()->param('page',1);
        $where = [];
        if($category_id != 0){
            $where['category_id'] = $category_id;
        }
        return apiSuccess('ok',TopicModel::getTopicList($page,$where));
    }

    /**
     * 搜索资源列表
     *
     * @return \think\Response
     */
    public function search()
    {
        $keyword = request()->param('keyword');
        $page = request()->param('page',1);
        $where = [
            ['title','like','%'. $keyword .'%']
        ];
        return apiSuccess('ok',TopicModel::getTopicList($page,$where));
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $id = request()->param('id',0);
        $data = TopicModel::withCount(['todayArticle'])->with("category")->find($id);
        if(!$data){
            return apiFail("没有找到数据",404);
        }
        return apiSuccess('ok',$data);
    }
}
