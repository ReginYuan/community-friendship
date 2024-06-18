<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use ImageUploader\ImageUploader;
use app\controller\api\Base;
class Image extends Base
{
    // 上传图片
    public function upload(Request $request)
    {
        // ApiException('图片上传功能目前仅限购买过课程的学员使用。详情可观看课程视频演示了解。');
        
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
