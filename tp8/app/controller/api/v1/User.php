<?php
declare (strict_types = 1);

namespace app\controller\api\v1;

use think\Request;
use app\model\User as UserModel;
use app\model\Comment as CommentModel;
use app\model\Blacklist as BlacklistModel;
use app\controller\api\Base;

class User extends Base
{
    // 不需要验证的方法
    protected $excludeValidateCheck = ["logout","info","sendCode2"];
    /**
     * 搜索资源列表
     *
     * @return \think\Response
     */
    public function search()
    {
        $keyword = request()->param('keyword');
        $page = request()->param('page',1);
        $query = UserModel::page($page,10)
        ->field("id,username,phone,email,avatar,desc,create_time,fans_count")
        ->order("id","desc");
        if($keyword){
            $query = $query->where('username|phone|email', 'like', '%' . $keyword . '%');
        }
        // 判断当前是否已关注这些作者
        $query = UserModel::withIsFollow($query);

        $data = $query->paginate(10)->filter(function($item){
            $name = "";
            if($item->username){
                $name = $item->username;
            } elseif($item->phone){
                $name = maskPhoneNumber($item->phone);
            } elseif($item->email){
                $name = $item->email;
            } else {
                $name = "未知";
            }
            $item->name = $name;
            if(!$item->desc){
                $item->desc = "暂无描述~";
            }
            $item->isfollow = $item->isfollow ? true : false;
            return $item;
        });

        return apiSuccess('ok',$data);
    }

    // 获取用户详情
    public function read($id){
        // 获取当前用户id
        $currentUser_id = getCurrentUserIdByToken();
        // 获取用户信息
        $user = UserModel::with([
            // 判断是否已关注该作者
            "isFollowCurrentUser" => function(\think\Db\Query $query) use($currentUser_id){
                $query->where('follow.user_id', $currentUser_id);
            },
            // 判断是否已拉黑该作者
            "isBlackedCurrentUser" => function(\think\Db\Query $query) use($currentUser_id){
                $query->where('blacklist.user_id', $currentUser_id);
            }
        ])->find($id)
        ->hidden(["password","username","phone","email","wx_openid","wx_unionid","create_time","update_time","isFollowCurrentUser","isBlackedCurrentUser"])
        ->append(["name"]);

        if(!$user){
            ApiException('用户不存在');
        }

        // 用户已被禁用
        if($user->getData("status") == 0) {
            ApiException('该用户已被禁用');
        }

        $user->isfollow = $user->isfollow ? true : false;
        $user->isblacked = $user->isblacked ? true : false;

        return apiSuccess('ok',$user);
    }

    // 发送验证码
    public function sendCode(){
        $phone = request()->param('phone');
        try {
            $res = sendSms($phone);
            if(!config('api.aliSMS.isopen')){
                return apiSuccess($res);
            }
            return apiSuccess('发送成功');
        } catch (\Throwable $th) {
            ApiException($th->getMessage());
        }
    }

    // 手机号验证码登录
    public function phoneLogin(){
        // 获取所有参数
        $phone = input("phone");
        // 验证用户是否存在
        $user = UserModel::isUserExist($phone);
        // 用户不存在，直接注册
        if(!$user){
            // 用户主表
            $user = UserModel::create([
                'phone'=>$phone
            ]);
            $user = UserModel::getUserInfo('id',$user->id);
        }
        // 登录成功
        return apiSuccess("ok",UserModel::loginHandle($user));
    }

    // 发送验证码（无需传手机号，需要登录后操作）
    public function sendCode2(){
        $phone = request()->currentUser->getData("phone");
        if(!$phone){
            ApiException("请先绑定手机号");
        }
        try {
            $res = sendSms($phone);
            if(!config('api.aliSMS.isopen')){
                return apiSuccess($res);
            }
            return apiSuccess('发送成功');
        } catch (\Throwable $th) {
            ApiException($th->getMessage());
        }
    }

    // 修改密码
    public function changepwd(){
        // 获取所有参数
        $params = request()->param();
        // 获取用户id
        $userid = request()->userId;
        $user = request()->currentUser;
        // 修改密码
        $user->password = createPassword($params['password']);
        $res = $user->save();
        if (!$res) ApiException('修改密码失败');
        return apiSuccess('修改密码成功');
    }

    // 用户密码登录
    public function login(){
        // 获取所有参数
        $param = request()->param();
        // 验证用户是否存在
        $user = UserModel::isUserExist($param['username']);
        // 邮箱/手机号错误
        if(!$user) {
            ApiException('邮箱/手机号错误');
        }
        // 验证密码
        if(!checkPassword($param['password'],$user->getData("password"))){
            ApiException('密码错误');
        }
        // 登录成功 生成token，进行缓存，返回客户端
        return apiSuccess("ok",UserModel::loginHandle($user));
    }

    // 退出登录
    public function logout(){
        // 清除token
        $header = request()->header();
        if(array_key_exists("token",$header)){
            $user = cache($header["token"]);
            cache($header["token"],null);
            if($user){
                cache("login_".$user["id"],null);
            }
        }
        return apiSuccess('退出成功');
    }

    // 忘记密码
    public function forget(){
        // 获取所有参数
        $params = request()->param();
        // 用户是否存在
        $user = UserModel::isUserExist($params['phone']);
        if(!$user) ApiException('用户不存在');
        // 修改密码
        $user->password = createPassword($params['password']);
        $res = $user->save();
        if (!$res) ApiException('找回密码失败');
        // 让已登录的用户token失效
        $token = cache("login_".$user->id);
        cache("login_".$user->id,null);
        if($token){
            cache($token,null);
        }
        return apiSuccess('找回密码成功');
    }

    // 获取用户详细信息
    public function info(){
        $user = request()->currentUser;
        $user->append(['name']);
        return apiSuccess('ok',$user);
    }

    // 绑定手机
    public function bindPhone(){
        // 获取所有参数
        $params = request()->param();
        $user = request()->currentUser;

        // 手机号一致无需修改
        if($user->getData("phone") == $params['phone']){
            ApiException('手机号一致无需修改');
        }

        // 查询该手机是否绑定了其他用户
        $binduser = UserModel::isUserExist('phone',$params['phone']);
        if ($binduser && $binduser->getData("phone") == $params['phone']) {
            ApiException('手机号已被绑定');
        }

        if($user->save(['phone' => $params['phone']])){
            return apiSuccess('ok',$user);
        }
        ApiException('修改失败');
    }

    // 修改头像
    public function changeAvatar(){
        $file = request()->file('avatar');
        // 上传到本地服务器
        $savename = \think\facade\Filesystem::disk('public')->putFile( 'avatar', $file, 'uniqid');

        // 头像生成缩略图
        $savename = createAvatarThumb($savename);

        // 记录图片路径和上传用户信息到ip_image表
        \app\model\IpImage::create([
            'url' => $savename,
            'user_id' => request()->userId,
            'ip' => request()->ip()
        ]);

        $user = request()->currentUser;
        $user->avatar = $savename;
        $user->save();

        return apiSuccess('ok',$user->avatar);
    }

    // 修改资料
    public function changeInfo(){
        $data = request()->param();
        $user = request()->currentUser;
        $user->username = $data['name'];
        $user->sex = $data['sex'];
        $user->birthday = $data['birthday'];
        $user->qg = $data['qg'];
        $user->path = $data['path'];
        $user->desc = $data["desc"];
        $user->save();
        return apiSuccess('ok',$user);
    }

    // 指定用户的评论列表
    public function comments()
    {
        $value = request()->param('user_id',0);
        // 是否在黑名单内
        if(BlacklistModel::isBlacklist($value)){
            return apiSuccess('ok',[
                "total"=> 0,
                "per_page"=> 10,
                "current_page"=> 1,
                "last_page"=> 1,
                "data"=>[]
            ]);
        }
        $key = "user_id";
        $hidden = [
            "update_time",
            // "article_id",
            "comment_id",
            // "quote"
        ];
        $page = request()->param('page',1);
        $data = CommentModel::page($page,10)
        ->with(["user","article"])
        ->order("id","desc")
        ->where($key,$value)
        ->hidden($hidden)
        ->paginate(10)
        ->filter(function($item){
            if(!$item->article_title){
                $item->article_title = "帖子已被删除";
            }
            return $item;
        });

        return apiSuccess('ok',$data);
    }

}
