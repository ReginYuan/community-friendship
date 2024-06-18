<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use app\model\Upgradation as UpgradationModel;
use think\facade\Filesystem;  
class Upgradation extends Base
{
    protected $excludeValidateCheck = ['upload'];
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $page = request()->param("page");
        $data = UpgradationModel::page($page,10)->order("id","desc")->paginate(10);
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
        $user = request()->currentUser;
        $param = $request->param();

        $data = [
            'appid' => $param['appid'],
            'name' => $param['name'],
            'title' => $param['title'],
            'contents' => $param['contents'],
            "platform" => $param['platform'],
            "version" => $param['version'],
            "url" => $param['url'],
            "stable_publish" => $param['stable_publish'],
            "is_mandatory" => $param['is_mandatory'],
            'uni_platform'=>$param["platform"],
            "type" => "native_app",
            "min_uni_version" => '0.0.1',
            "create_env"=>"upgrade-center"
        ];
        $m = new UpgradationModel();
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
        $param = $request->param();
        $data = [
            'appid' => $param['appid'],
            'name' => $param['name'],
            'title' => $param['title'],
            'contents' => $param['contents'],
            "platform" => $param['platform'],
            "version" => $param['version'],
            "url" => $param['url'],
            "stable_publish" => $param['stable_publish'],
            "is_mandatory" => $param['is_mandatory'],
            'uni_platform'=>$param["platform"],
        ];
        $m = UpgradationModel::update($data, ['id' => $id]);
        $res = $m->save($data);
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
        $u = UpgradationModel::find($ids[0]);
        if(!$u){
            ApiException("不存在该版本");
        }
        if($u->stable_publish == 1){
            ApiException("该版本已发布，无法删除");
        }
        $res = $u->destroy();
        if(!$res){
            ApiException("删除失败");
        }
        return apiSuccess("删除成功",);
    }

    // 上传apk
    public function upload(Request $request)
    {
        $file = $request->file('file');  
  
        if (empty($file)) {  
            ApiException('请选择上传文件');  
        }  
        // 检查文件大小 
        $fileSize = $file->getSize();  
        if ($fileSize > 100 * 1024 * 1024) {  
            ApiException('文件大小不能超过100M');
        }  

         // 获取文件的扩展名  
         $ext = $file->getOriginalExtension();  
  
         // 检查文件扩展名是否为apk  
         if ($ext !== 'apk') {  
             ApiException('只允许上传APK文件');
         }  

        // 保存文件  
        $saveName = Filesystem::disk('public')->putFile('apk', $file);  
  
        if ($saveName) {  
            return apiSuccess("上传成功",getUploadPath($saveName));    
        }
        
        ApiException("上传失败");
    }
}
