<?php
declare (strict_types = 1);

namespace app\middleware;

class IpLimit
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
        if(config("admin.ip_limit.enable")){
            // ip黑名单验证
            $ip = $request->ip();   
            $ip2 = preg_replace('/\d+$/', '*', $ip);
            $inIpBlacklist = \app\model\IpBlacklist::whereOrIp($ip)->whereOrIp($ip2)->column("ip");
            if($inIpBlacklist){
                ApiException("非法请求");
            }
        }
        return $next($request);
    }
}
