<?php  
namespace app\middleware;  
  
use think\facade\Cache;  
use think\Response;  
  
class RequestLimit  
{  
    public function handle($request, \Closure $next)  
    {  
        // 请求次数限制
        if(config("admin.request_limit.enable")){
            $limit = config("admin.request_limit.count");
            $time = config("admin.request_limit.time");

            $ip = $request->ip(); 
            $key = 'request_limit:' . $ip; 
    
            // 检查缓存中的请求次数  
            $count = Cache::get($key);  
            if ($count >= $limit) {  
                // 记录日志
                \app\model\UserActionLog::addLog("超过请求次数限制","error");
                // 如果请求次数超过限制，返回错误信息  
                ApiException("请求次数过多，请稍后再试");
            }  
    
            // 如果请求次数未超过限制，增加计数器并设置过期时间  
            $count = $count ? $count + 1 : 1;  
            Cache::set($key, $count, $time);
        } 

        // 限制聊天发送次数
        if(config("admin.chat_limit.enable") && $request->isPost() && strpos($request->url(), "/im/send") !== false){
            $limit = config("admin.chat_limit.count");
            $time = config("admin.chat_limit.time");

            $ip = $request->ip(); 
            $key = 'chat_limit:' . $ip; 
    
            // 检查缓存中的请求次数  
            $count = Cache::get($key);  
            if ($count >= $limit) {  
                // 记录日志
                \app\model\UserActionLog::addLog("超过聊天次数限制","error");
                ApiException("你发得太快了，请稍后再试");
            }
            // 如果请求次数未超过限制，增加计数器并设置过期时间  
            $count = $count ? $count + 1 : 1;  
            Cache::set($key, $count, $time);
        }
  
        // 继续执行下一个中间件或控制器  
        return $next($request);  
    }  
}