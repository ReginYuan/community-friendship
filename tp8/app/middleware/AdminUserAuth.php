<?php
declare (strict_types = 1);

namespace app\middleware;
use think\facade\Db;
use app\model\Role;
class AdminUserAuth
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        // 获取头部信息
        $param = $request->header();
        // 不含token
        if (!array_key_exists('token',$param)) {
            ApiException("登录已失效，请重新登录");
        }
        // 当前用户token是否存在（是否登录）
        $user = cache("admin_".$param['token']);
        // 验证失败（未登录或已过期）
        if(!$user) {
            ApiException("登录已失效，请重新登录");
        }
        // 将token和userid这类常用参数放在request中
        $request->userToken = $param['token'];
        $request->userId = $user['id'];

        $u = \app\model\User::isUserExist("id",$user['id']);
        if(!$u){
            ApiException("当前用户不存在");
        } 
        // 当前用户信息
        $request->currentUser = $u;

        // 获取当前用户角色ID
        $roleIds = Db::table("user_role")->where("user_id",$user['id'])->column("role_id");
        
        // 没有角色
        if(!count($roleIds)){
            ApiException("没有权限，禁止操作");
        }

        // 不需要验证的路由
        $noAuthRoute = [
            // 登录
            "admin.User/login,POST",
            // 退出登录
            "admin.User/logout,POST",
            // 当前登录用户信息
            "admin.User/info,GET"
        ];

        $method = $request->method();
        $route = $request->rule()->getRoute();
        
        // 直接通过
        if(in_array($route.','.$method,$noAuthRoute)){
            return $next($request);
        }

        // 验证权限
        if(!Role::hasAuthByRoleIds($roleIds,$route,$method)){
            ApiException("没有权限，禁止操作");
        }

        return $next($request);
    }
}
