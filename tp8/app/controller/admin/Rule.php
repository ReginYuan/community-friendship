<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\Rule as RuleModel;
class Rule extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = (int)(request()->param("page"));
        $data = RuleModel::where("ismenu",1)->page($page,20)->with(["children"])->order("order,id asc")->paginate(20);
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
        $rule_id = $request->param("rule_id",0);
        $name = $request->param("name","");
        $status = $request->param("status",1);
        $order = $request->param("order",50);
        $ismenu = $request->param("ismenu",0);
        $icon = $request->param("icon");
        $method = $request->param("method","GET");
        $condition = $request->param("condition","");
        $data = [
            'name'=>$name,
            'rule_id' => $rule_id,
            'status' => $status,
            'order' => $order,
            'ismenu' => $ismenu,
            'icon' => $icon,
            'method' => $method,
            'condition' => $condition,
        ];
        $role = new RuleModel();
        $res = $role->save($data);
        if(!$res){
            ApiException("创建失败");
        }
        return apiSuccess('创建成功');
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
        $this->test($id);
        $rule_id = $request->param("rule_id",0);
        $name = $request->param("name","");
        $status = $request->param("status",1);
        $order = $request->param("order",50);
        $ismenu = $request->param("ismenu",0);
        $icon = $request->param("icon");
        $method = $request->param("method","GET");
        $condition = $request->param("condition","");
        $data = [
            'rule_id' => $rule_id,
            'name'=>$name,
            'status' => $status,
            'order' => $order,
            'ismenu' => $ismenu,
            'icon' => $icon,
            'method' => $method,
            'condition' => $condition,
        ];
        
        $res = RuleModel::update($data, ['id' => $id]);
        if($res){
            return apiSuccess('修改成功');
        }
        ApiException("修改失败");
    }

    /**
     * 删除资源
     *
     * @param  array
     * @return \think\Response
     */
    public function delete($id)
    {
        $this->test($id);
        $res = RuleModel::destroy($id);
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }
    
    public function test($id){
        if($id <= 84){
            ApiException("内置权限菜单，不可操作");
        }
    }
}
