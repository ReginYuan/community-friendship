<?php
declare (strict_types = 1);

namespace app\controller\api;

use think\Request;

class Base
{

    // 是否开启自动验证
    protected $autoValidateCheck = true;

    // 不需要验证的方法
    protected $excludeValidateCheck = [];

    // 验证场景配置
    protected $autoValidateScenes = [];
    
    // 当前控制器信息
    protected $Cinfo = [];

    /**
     * 1. 自动实例化当前model
     */
    public function __construct(Request $request){
        // 初始化控制器相关信息
        $this->initControllerInfo($request);
        // 自动验证
        $this->autoValidateAction();
    }

    // 初始化控制器相关信息
    public function initControllerInfo($request){
         $str = $request->controller();
         // 获取真实控制器名称
        //  $arr = explode('.',$str);
        //  $Cname = $arr[count($arr)-1];
         $this->Cinfo = [
             'Cname' => class_basename($this),
             'Cpath' => str_replace('.','\\',$str),
             // 当前方法
             'action' => $request->action()
         ];
    }
    
    // 自动化验证
    public function autoValidateAction(){
        // app地址
        define('__APP_PATH__',__DIR__.'/../../');
        $action = $this->Cinfo['action'];
        // 判断是否需要验证
        if ($this->autoValidateCheck && !in_array($action,$this->excludeValidateCheck)) {
            // 获取验证实例
            $validateName = file_exists(__APP_PATH__.'validate/'.$this->Cinfo['Cpath'].'.php') ? $this->Cinfo['Cpath'] : $this->Cinfo['Cname'];
            $validate = app('app\validate\api\\'.$validateName);
            // 获取验证场景
            $scene = $action;
            if (array_key_exists($action,$this->autoValidateScenes)) {
                $scene = $this->autoValidateScenes[$action];
            }
            // 开始验证
            $params = request()->param();
            if (!$validate->scene($scene)->check($params)) {
                // 抛出异常
                ApiException($validate->getError());
            }
        }
    }

    // 演示数据禁止操作
    public function TestException(){
        ApiException('当前是《<a href="https://study.163.com/course/courseMain.htm?courseId=1212775807&share=2&shareId=480000001892585" target="_blank" style="color: #409eff;text-decoration-line: underline;">Vue3实战商城后台管理系统</a>》课程的演示站点，仅供演示，禁止操作',40000);
    }
}
