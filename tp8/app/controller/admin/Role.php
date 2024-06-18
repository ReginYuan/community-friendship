<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\Role as RoleModel;
use think\facade\Db;
class Role extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = request()->param("page");
        $data = RoleModel::page($page,10)->with([
            "roleRules"=>function ($query) {
                $query->field("rule_id,role_id");
            }
        ])
        ->order("id","desc")
        ->paginate(10);
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
        $name = $request->param("name");
        $desc = $request->param("desc","");
        $data = [
            'name' => $name,
            'desc' => $desc
        ];
        $role = new RoleModel();
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
        $name = $request->param("name");
        $desc = $request->param("desc","");
        $data = [
            'name' => $name,
            'desc' => $desc
        ];
        $res = RoleModel::update($data, ['id' => $id]);
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
        $res = RoleModel::destroy($id);
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }

    // 给角色设置权限
    public function setRule(){
        $role_id = request()->param("role_id");
        $rule_ids = request()->param("rule_ids");
        
        $this->test($role_id);
        
        $role = RoleModel::find($role_id);
        if(!$role){
            ApiException("角色不存在");
        }
        // 获取角色现有权限ID
        $has_ids = Db::table("role_rule")->where("role_id",$role_id)->column("rule_id");
        // 取出需要添加的权限ID
        $add_ids = array_diff($rule_ids,$has_ids);
        // 取出需要删除的权限ID
        $del_ids = array_diff($has_ids,$rule_ids);
        
        // 添加权限ID
        if(count($add_ids)){
            $add_data = [];
            foreach($add_ids as $id){
                $add_data[] = [
                    "role_id" => $role_id,
                    "rule_id" => $id
                ];
            }
            Db::table("role_rule")->insertAll($add_data);
        }
        // 删除权限ID
        if(count($del_ids)){
            Db::table("role_rule")->where("role_id",$role_id)->whereIn("rule_id",$del_ids)->delete();
        }

        return apiSuccess("设置成功");
    }
    
    public function test($id){
        if($id == 1 || $id == 2){
            ApiException("系统内置角色，不可操作");
        }
    }
}
