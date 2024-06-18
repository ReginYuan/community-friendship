<?php
declare (strict_types = 1);

namespace app\controller\admin;

use think\Request;
use ImageUploader\ImageUploader;
class Image extends Base
{
    // 上传图片
    public function upload(Request $request)
    {
        $file = $request->file('image');  
        if(!$file){
            ApiException('请选择上传图片');
        }
        // 实例化上传类并调用方法上传图片  
        $uploader = new ImageUploader();  
        try {  
            $savedPath = $uploader->uploadAndCompress($file);  
            return apiSuccess("上传成功",getUploadPath($savedPath));  
        } catch (\Exception $e) {  
            ApiException($e->getMessage());
        }  
    }
}
