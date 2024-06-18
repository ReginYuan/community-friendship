<?php
declare (strict_types = 1);

namespace app\middleware;
use app\model\UserActionLog;
class ContentLimit
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
        // 是否开启
        if(!config("admin.content_limit.enable")){
            return $next($request);
        }

        $url = $request->url();
        // 发送消息限制
        if((strpos($url, "im/send") !== false || strpos($url,"devtool/send") !== false ) && $request->isPost()){
            $content = $request->param("body","");
            $testMsgs = [
                "你好",
                "哈哈哈",
                "早上好",
                "下午好",
                "晚上好"
            ];
            if (!in_array($content, $testMsgs)) {
                ApiException("当前接口仅用于uniappx课程学习调试，仅支持发送内容：".implode(",",$testMsgs));
            }
        }

        // 发布帖子/反馈内容限制
        if((strpos($url, "/article/save") !== false || strpos($url, "/feedback/save") !== false || strpos($url, "/comment/reply") !== false || strpos($url, "/comment/save") !== false ) && $request->isPost()){
            $content = $request->param("content","");
            $testContents = [
                "你好",
                "测试帖子",
                "哈哈哈",
                "学习前端",
                "学习编程"
            ];
            if (!in_array($content, $testContents)) {
                ApiException("当前接口仅用于uniappx课程学习调试，仅支持发送以下内容：".implode(",",$testContents));
            }
        }

        // 修改资料过滤违禁词
        if(strpos($url, "/user/changeinfo") !== false && $request->isPost()){
            // 加载违禁词库
            $s = sensitive_custom( config_path() . '/sensitive/SensitiveWord.txt');

            // 昵称
            $name = $request->param("name","");
            $name_sensitives =  $s->get($name);
            if(count($name_sensitives) > 0){
                UserActionLog::addLog("name包含违禁词：".implode(",",$name_sensitives),"error");
                ApiException("name包含违禁词");
            }

            // 地址
            $path =  $request->param("path","");
            $path_sensitives =  $s->get($path);
            if(count($path_sensitives) > 0){
                UserActionLog::addLog("path包含违禁词：".implode(",",$path_sensitives),"error");
                ApiException("path包含违禁词");
            }

            // 个性签名
            $desc =  $request->param("desc","");
            $desc_sensitives =  $s->get($desc);
            if(count($desc_sensitives) > 0){
                UserActionLog::addLog("desc包含违禁词：".implode(",",$desc_sensitives),"error");
                ApiException("desc包含违禁词");
            }

            // 检测是否包含链接
            $pattern = '/\b((https?:\/\/|www\.)[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\/[a-zA-Z0-9-._~%?&=+,*!:;]+)*)|[a-zA-Z0-9-]+\.[a-zA-Z]{2,}\b/i';
            if (preg_match($pattern, $name)) {  
                UserActionLog::addLog("name包含链接","error");
                ApiException("name包含链接");
            }
            if (preg_match($pattern, $path)) {  
                UserActionLog::addLog("path包含链接","error");
                ApiException("path包含链接");
            }
            if (preg_match($pattern, $desc)) {  
                UserActionLog::addLog("desc包含链接","error");
                ApiException("desc包含链接");
            }
        }

        return $next($request);
    }
}
