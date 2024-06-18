<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\User as UserModel;
use app\model\Role as RoleModel;
class User extends Base
{
    // 不需要验证的方法
    protected $excludeValidateCheck = ["logout","info"];

    // 登录
    public function login(Request $request)
    {
        // 获取所有参数
        $param = request()->param();
        // 判断是手机还是邮箱
        $value = $param['username'];
        $key = "";
        if(isPhoneNumber($value)){
            $key = "phone";
        } elseif (isEmail($value)) {
            $key = "email";
        } else {
            ApiException('请输入正确的手机号或邮箱');
        }
        $user = UserModel::isUserExist($key,$value);
        // 邮箱/手机号错误
        if(!$user) {
            ApiException('邮箱/手机号错误');
        }
        // 验证密码
        if(!checkPassword($param['password'],$user->getData("password"))){
            ApiException('密码错误');
        }

        // 验证是否有角色权限
        if(count($user->roles->column("id")) == 0){
            ApiException('你没有权限登录后台！');
        }

        // 登录成功 生成token，进行缓存，返回客户端
        if(!is_array($user)){
            $user = $user->toArray();
        }
        $p1 = "admin_";
        $p2 = "admin_login_";
        $user['token'] = createToken($user,$p1);
        // 将用户ID和token进行绑定
        cache($p2.$user["id"],$user["token"],config('admin.token_expire'));
        $rs = RoleModel::getMenusAndRulesByUserId($user["id"]);
        $user["menus"] = $rs["menus"];
        $user["rules"] = $rs["rules"];
        return apiSuccess("ok",$user);
    }

    // 注销登录
    public function logout()
    {
        $p1 = "admin_";
        $p2 = "admin_login_";
        // 清除token
        $header = request()->header();
        if(array_key_exists("token",$header)){
            $user = cache($p1.$header["token"]);
            cache($header["token"],null);
            if($user){
                cache($p2.$user["id"],null);
            }
        }
        return apiSuccess('退出成功');
    }

    /**
     * 显示用户列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $keyword = request()->param('keyword');
        $page = request()->param('page',1);
        $query = UserModel::page($page,10)->order("id","desc");
        if($keyword){
            $query = $query->where('username|phone|email', 'like', '%' . $keyword . '%');
        }

        $data = $query->with(["roles"])->paginate(10)->filter(function($item){
            $name = "";
            if($item->username){
                $name = $item->username;
            } elseif($item->phone){
                $name = $item->phone;
            } elseif($item->email){
                $name = $item->email;
            } else {
                $name = "未知";
            }
            $item->name = $name;

            // 获取隐藏的手机和邮箱
            $item->o_phone = $item->getData("phone");
            $item->o_email = $item->getData("email");
            // 角色
            $item->roles->hidden(["create_time","update_time","desc","pivot"]);

            if(count($item->roles) == 0){
                $item->rolename = ["普通用户"];
            } else {
                $item->rolename = $item->roles->column("name");
            }

            return $item;
        });

        return apiSuccess('ok',$data);
    }


    // 获取当前登录用户详细信息
    public function info(){
        $user = request()->currentUser;
        $user->append(['name']);
        $rs = RoleModel::getMenusAndRulesByUserId($user->id);
        $user->menus = $rs["menus"];
        $user->rules = $rs["rules"];
        return apiSuccess('ok',$user);
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

        // 手机已存在
        if(UserModel::where("phone",$param['phone'])->value("id")){
            ApiException("手机号已存在，换一个吧~");
        }

        // 邮箱已存在
        if(UserModel::where("email",$param['email'])->value("id")){
            ApiException("邮箱已存在，换一个吧~");
        }
        
        $data = [
            'username' => $param['username'],
            'avatar' => $param['avatar'],
            'password' => createPassword($param['password']),
            'phone' => $param['phone'],
            'email' => $param['email'],
            'status' => $param['status'],
            'desc' => $param['desc'],
        ];
        $m = new UserModel();
        $res = $m->save($data);
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
        // 演示数据禁止操作
        isDemoData($id,[7,1531,1807,1808]);

        $param = $request->param();
        $user = UserModel::find($id);
        // 用户不存在
        if(!$user){
            ApiException("用户不存在");
        }
        
        // 手机号是不是别人已经用过
        if(UserModel::where("phone",$param['phone'])->where("id","<>",$id)->value("id")){
            ApiException("手机号已存在，换一个吧~");
        }

        $data = [
            'username' => $param['username'],
            'avatar' => $param['avatar'],
            'phone' => $param['phone'],
            'status' => $param['status'],
            'desc' => $param['desc'],
        ];

        // 密码加密
        if($param["password"]){
            $data['password'] = createPassword($param['password']);
        }

        // 邮箱是不是别人已经用过
        if($param['email']){
            if(UserModel::where("email",$param['email'])->where("id","<>",$id)->value("id")){
                ApiException("邮箱已存在，换一个吧~");
            }
            $data['email'] = $param['email'];
        }

        $res = UserModel::update($data, ['id' => $id]);
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

        // 演示数据禁止操作
        isDemoData($ids[0],[7,1531,1807,1808]);
        
        $res = UserModel::destroy($ids);
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }

    // 给用户设置角色
    public function setRole(){
        $param = request()->param();

        // 演示数据禁止操作
        isDemoData($param['id'],[7,1531,1807,1808]);

        // 用户不存在
        $user = UserModel::find($param['id']);
        if(!$user){
            ApiException("用户不存在");
        }

        // 过滤角色ID
        $roleIds = RoleModel::where('id','in',$param['role_ids'])->column("id");

        // 获取用户已有的角色ID
        $hasRoleIds = $user->roles->column("id");

        // 如果过滤后角色ID是空，则删除用户所有角色
        if(count($roleIds) == 0){
            $user->roles()->detach($hasRoleIds);
            return apiSuccess("设置成功");
        }

        // 需要添加的角色ID
        $addRoleIds = array_diff($roleIds,$hasRoleIds);
        // 需要删除的角色ID
        $delRoleIds = array_diff($hasRoleIds,$roleIds);
        // 添加角色
        if(count($addRoleIds) > 0){
            $user->roles()->attach($addRoleIds);
        }
        // 删除角色
        if(count($delRoleIds) > 0){
            $user->roles()->detach($delRoleIds);
        }
        return apiSuccess("设置成功");
    }
}
