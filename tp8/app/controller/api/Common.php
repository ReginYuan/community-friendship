<?php
declare (strict_types = 1);

namespace app\controller\api;

use think\Request;

class Common
{
    // 用户协议
    public function agreement(Request $request)
    {
        return view();
    }

    // 隐私政策
    public function privacy(Request $request)
    {
        return view();
    }
}
