<?php
// api分页数据返回
function apiPage($msg = '未知',$data = [])
{
    if(!is_array($data)){
        $data = $data->toArray();
    }
    $res = [
        'code' => 1,
        'msg'=>$msg,
        'data'=>$data["data"],
        "total"=> $data["total"],
		"per_page"=> $data["per_page"],
		"current_page"=> $data["current_page"],
		"last_page"=> $data["last_page"],
    ];
    return json($res,200);
}
// api成功返回
function apiSuccess($msg = '未知',$data = [])
{
    $res = [
        'code' => 1,
        'msg'=>$msg,
        'data'=>$data
    ];
    return json($res,200);
}

// api成功返回
function apiFail($msg = '未知', $statusCode = 400)
{
    $res = [
        'code' => 0,
        'msg'=>$msg,
        'data' => null
    ];
    return json($res,$statusCode);
}

// api异常返回
function ApiException($msg = '未知', $statusCode = 403)
{
    abort($statusCode,$msg);
}


// 发送短信
function sendSms($phone){
    $config = config('api.aliSMS');

    // 判断是否已经发送过
    if(cache($phone)) ApiException("你操作得太快了！");
    // 生成4位验证码
    $code = random_int(100000,999999);
    // 判断是否开启验证码功能
    if(!$config["isopen"]){
        cache($phone,$code,$config["expire"]);
        return '验证码：'.$code.'，（提示：学完上线后是真实发送短信到你的手机，目前是演示阶段）';
    }
    // 发送验证码
    \AlibabaCloud\Client\AlibabaCloud::accessKeyClient($config["accessKeyId"],$config["accessSecret"])
    ->regionId($config["regionId"])
    ->asGlobalClient();

    try {
        $option=[
            'query' => [
                'RegionId' => $config["regionId"],
                'PhoneNumbers' => $phone,
                'SignName' =>$config["SignName"],
                'TemplateCode' =>$config["TemplateCode"],
                'TemplateParam' =>'{"code":"'.$code.'"}',
            ]
        ];
        $result = \AlibabaCloud\Client\AlibabaCloud::rpcRequest()
                ->product($config["product"])
                // ->scheme('https') // https | http
                ->version($config["version"])
                ->action('SendSms')
                ->method('GET')
                ->options($option)->request();
        $res = $result->toArray();
        //发送成功 写入缓存
        if($res['Code']=='OK') {
            return cache($phone,$code,$config["expire"]);
        }
        // 无效号码
        if($res['Code']=='isv.MOBILE_NUMBER_ILLEGAL') {
            ApiException('无效号码');
        }
        // 触发日限制
        if($res['Code']=='isv.DAY_LIMIT_CONTROL') {
            ApiException('今日你已经发送超过限制，改日再来');
        }
        // 发送失败
        ApiException('发送失败');
    } catch (\AlibabaCloud\Client\Exception\ClientException $e) {
        ApiException($e->getErrorMessage());
    } catch (\AlibabaCloud\Client\Exception\ServerException $e) {
        ApiException($e->getErrorMessage());
    }
}

// 验证手机短信
function checkSms($phone,$code){
    // 获取缓存中的验证码
    $beforeCode = cache($phone);
    // 删除缓存
    cache($phone, NULL);
    // 验证码失效
    if(!$beforeCode) return "请重新获取验证码";
    // 验证验证码
    if($code != $beforeCode) return "验证码错误";

    return true;
}

// 生成Token
function createToken($data = [],$prefix = ""){
    // 生成token
    $token = sha1(md5(uniqid(md5(microtime(true)),true)));
    $data['token'] = $token;
    // 登录过期时间
    $expire = config('api.token_expire');
    // 保存到缓存中
    $key = $token;
    if($prefix != ""){
        $key = $prefix.$token;
    }
    cache($key,$data,$expire);
    // 返回token
    return $token;
}

// 隐藏手机号中间四位
function maskPhoneNumber($phoneNumber) {  
    // 验证手机号格式  
    // if (!preg_match('/^1[3-9]\d{9}$/', $phoneNumber)) {  
    //     return 'Invalid phone number';  
    // }  
  
    // 替换中间四位为*  
    $maskedNumber = substr($phoneNumber, 0, 3) . '****' . substr($phoneNumber, 7);  
    return $maskedNumber;  
}  

// 是否是手机号格式
function isPhoneNumber($number) {  
    $pattern = '/^1[3-9]\d{9}$/'; // 中国手机号的正则表达式  
    return preg_match($pattern, $number) > 0;  
}

// 是否是邮箱格式
function isEmail($email) {  
    $pattern = '/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/'; // 邮箱的正则表达式  
    return preg_match($pattern, $email) > 0;  
}

// 创建密码
function createPassword($password){
    return password_hash($password,PASSWORD_DEFAULT);
}

// 验证密码是否正确
function checkPassword($password,$hash){
    if (!$hash) return false;
    return password_verify($password,$hash);
}

// 生成头像缩略图并压缩
function createAvatarThumb($savename){
    $imgPath = app()->getRootPath() . 'public/storage/'. $savename;
    $image = \think\Image::open($imgPath);
    if($image->width() > 150 || $image->height() > 150){
        $image->thumb(150,150)->save($imgPath);
    }
    return $savename;
}

// 获取本地文件上传路径
function getUploadPath($path = ''){
    if(!$path){
        return $path;
    }
    $path = str_replace("\\", "/", $path);
    if (strpos($path, "http") !== false) {  
        // 将本地测试的地址替换成线上地址
        $path = str_replace("http://127.0.0.1:8000", (request()->root(true)), $path);
        // $path = str_replace("http://192.168.3.248:8000", (request()->root(true)), $path);
        return $path;
    } 
    return (request()->root(true)).'/storage/'.$path;
}

// 根据header获取当前登录用户ID
function getCurrentUserIdByToken($prefix = ""){
    // 获取登录用户ID
    $token = request()->header("token");
    $currentUser_id = 0;
    if($token){
        $key = $token;
        if($prefix != ""){
            $key = $prefix.$token;
        }
        $currentUser = cache($key);
        if($currentUser){
            $currentUser_id = $currentUser["id"];
        } 
    }
    return $currentUser_id;
}

// 获取所有我拉黑/被我拉黑的用户ID
function getBlackUsers(){
    return \app\model\Blacklist::getBlackUsers();
}

// 用户是否在线
function isUidOnline($target_id){
    \GatewayWorker\Lib\Gateway::$registerAddress = config('gateway_worker.registerAddress');
    return \GatewayWorker\Lib\Gateway::isUidOnline($target_id);
}
// 推送消息给指定用户
function pushMessageToUid($target_id,$message){
    \GatewayWorker\Lib\Gateway::$registerAddress = config('gateway_worker.registerAddress');
    // 对方不在线
    if (!\GatewayWorker\Lib\Gateway::isUidOnline($target_id)) return false;
    // 直接发送
    \GatewayWorker\Lib\Gateway::sendToUid($target_id,json_encode($message));
    return true;
}

// 本数据是演示数据，为了保证学员学习中数据不被破坏，该数据不允许操作，请操作其他数据
function isDemoData($oid,$ids = []){
    if(in_array($oid,$ids)){
        ApiException('本数据是演示数据，为了保证学员学习中数据不被破坏，该数据不允许操作，请操作其他数据！');
    }
}