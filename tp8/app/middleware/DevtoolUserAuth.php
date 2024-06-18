<?php
declare (strict_types = 1);

namespace app\middleware;

class DevtoolUserAuth
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
            ApiException("已失效，请重新切换用户");
        }
        // 当前用户token是否存在（是否登录）
        $user = cache("devtool_".$param['token']);
        // 验证失败（未登录或已过期）
        if(!$user) {
            ApiException("已失效，请重新切换用户");
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

        return $next($request);
    }
}
