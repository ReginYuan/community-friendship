<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;
Route::get("/",function () {
    return '<div style="width: 100%;height: 100%;display: flex;align-items: center;justify-content: center;"><div style="max-width:600px;padding: 20px;border: 1px solid #dddddd;border-radius: 5px;color: #333333;font-size:16px;">《uni-app x + uts + vue3 实战社区交友》接口文档请参考：<a href="https://www.dishaxy.com/doc/uniappx_community/">https://www.dishaxy.com/doc/uniappx_community/</a></div></div>';
});
