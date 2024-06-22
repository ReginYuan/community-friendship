'use strict';

const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/user")
class UserController extends Controller {
  // 发送验证码
  async sendCode() {
    const { ctx, service, app } = this

    // 参数验证
    ctx.validate(SCENE.sendCode)

    // 发送验证码
    const res = await service.alisms.send(ctx.request.body.phone)

    // 关闭验证码发送
    if (!app.config.aliSMS.isopen) {
      return ctx.apiSuccess(res)
    }

    if (res.Code !== 'OK') {
      ctx.throw(400, '发送失败')
    }

    ctx.apiSuccess('ok')
  }

  // 发送验证码（无需传手机号，需要登录后操作）
  async sendCode2() {
    const { ctx, service, app } = this

    // 获取当前登录用户
    const authUser = ctx.authUser

    // 发送验证码
    const res = await service.alisms.send(authUser.getDataValue("phone"))

    // 关闭验证码发送
    if (!app.config.aliSMS.isopen) {
      return ctx.apiSuccess(res)
    }

    if (res.Code !== 'OK') {
      ctx.throw(400, '发送失败')
    }

    ctx.apiSuccess('ok')
  }

  // 手机号登录
  async phoneLogin() {
    const { ctx, service, app } = this
    // 参数验证
    ctx.validate(SCENE.phoneLogin)
    // 获取参数
    const { phone, code } = ctx.request.body
    // 验证码验证
    await service.alisms.verifyCode(phone, code)

    // 验证用户是否存在
    let user = await service.user.isUserExist(phone);

    // 用户不存在，直接注册
    if (!user) {
      user = await app.model.User.create({ phone })
      user = await service.user.getUserInfo('id', user.id)
    }

    return ctx.apiSuccess('ok', await service.user.loginHandle(user))
  }

  // 用户密码登录
  async login() {
    const { ctx, service, app } = this
    // 参数验证
    ctx.validate(SCENE.login)
    // 获取参数
    const { username, password } = ctx.request.body

    // 验证用户是否存在
    const user = await service.user.isUserExist(username);

    // 邮箱/手机号错误
    if (!user) {
      ctx.throw(400, '邮箱/手机号错误')
    }

    // 验证密码
    if (!app.checkPassword(password, user.getDataValue("password"))) {
      ctx.throw(400, '密码错误')
    }

    // 登录成功 生成token，进行缓存，返回客户端
    return ctx.apiSuccess('ok', await service.user.loginHandle(user))
  }

  // 退出登录
  async logout() {
    const { ctx, service } = this
    const header = ctx.request.header
    if (header.token) {
      const user = await service.cache.get(header.token);
      if (user) {
        await service.cache.remove(header.token);
        await service.cache.remove(`login_${user.id}`);
      }
    }
    return ctx.apiSuccess('退出成功')
  }

  // 获取用户详细信息
  async info() {
    const { ctx } = this;
    return ctx.apiSuccess("ok", ctx.authUser)
  }

  // 修改密码
  async changepwd() {
    const { ctx, service } = this
    // 参数验证
    ctx.validate(SCENE.changepwd)
    // 获取当前登录用户
    const authUser = ctx.authUser
    // 获取参数
    const { code, password } = ctx.request.body
    // 验证码验证
    await service.alisms.verifyCode(authUser.getDataValue("phone"), code)

    // 修改密码
    authUser.password = password
    if (!(await authUser.save())) {
      ctx.throw(400, "修改失败")
    }
    ctx.apiSuccess("修改成功")
  }

  // 忘记密码
  async forget() {
    const { ctx, service } = this;
    // 参数验证
    ctx.validate(SCENE.forget)
    const { phone, code, password } = ctx.request.body;

    // 验证验证码
    await service.alisms.verifyCode(phone, code)

    // 用户是否存在
    const user = await service.user.isUserExist(phone);
    if (!user) {
      ctx.throw(400, '用户不存在');
    }
    // 修改密码
    user.password = password
    const res = await user.save()

    if (!res) {
      ctx.throw(400, '找回密码失败');
    }

    // 让已登录的用户token失效
    const token = await service.cache.get(`login_${user.id}`);
    if (token) {
      await service.cache.remove(token);
      await service.cache.remove(`login_${user.id}`);
    }

    return ctx.apiSuccess("找回密码成功")

  }

  // 绑定手机号
  async bindPhone() {
    const { ctx, service } = this;
    // 参数验证
    ctx.validate(SCENE.bindPhone)
    // 获取当前登录用户
    const authUser = ctx.authUser

    const { phone, code } = ctx.request.body;

    // 验证验证码
    await service.alisms.verifyCode(phone, code)

    // 手机号一致无需修改
    if (authUser.getDataValue("phone") === phone) {
      ctx.throw(400, "手机号一致无需修改")
    }

    // 查询该手机是否绑定了其他用户
    const binduser = await service.user.isUserExist('phone', phone);
    if (binduser && binduser.getDataValue("phone") === phone) {
      ctx.throw(400, "手机号已被绑定")
    }

    authUser.phone = phone;
    if (await authUser.save()) {
      return ctx.apiSuccess('ok', authUser);
    }
    ctx.throw(400, '修改失败');
  }

  // 修改资料
  async changeInfo() {
    const { ctx } = this;
    ctx.validate(SCENE.changeInfo)
    const authUser = ctx.authUser
    const data = ctx.request.body;
    authUser.username = data.name
    authUser.sex = data.sex
    authUser.birthday = data.birthday
    authUser.qg = data.qg
    authUser.path = data.path
    authUser.desc = data.desc
    await authUser.save()
    return ctx.apiSuccess('ok', authUser);
  }

  // 搜索用户
  async search() {
    const { ctx, app, service } = this;
    // 参数验证
    ctx.validate(SCENE.search)
    const { keyword } = ctx.query;
    const { page } = ctx.params;
    // 获取当前用户ID
    const userId = await service.user.getCurrentUserIdByToken();

    const model = app.model.User.scope({
      method: ['search', keyword]
    }, {
      method: ['isfollow', userId]
    })
    // 分页
    const result = await service.user.paginate(model, {
      page,
      // 排序
      order: [
        ['id', 'DESC']
      ],
      attributes: ["id", "username", "phone", "email", "avatar", "desc", "create_time", "fans_count"]
    });

    result.data.forEach(e => {
      e.isfollow = !!e.isfollow
      if (!e.desc) {
        e.desc = "暂无描述~"
      }
    });

    return ctx.apiSuccess("ok", result)
  }

  // 获取用户详情
  async read() {
    const { ctx, app, service } = this;
    ctx.validate(SCENE.read)
    const { id } = ctx.params
    // 获取当前用户ID
    const userId = await service.user.getCurrentUserIdByToken();
    // 根据ID查询用户信息
    const result = await app.model.User.scope({
      method: ['isfollow', userId]
    }, {
      method: ['isblacked', userId]
    }).findByPk(id);

    result.setDataValue("isfollow", !!result.isfollow)
    result.setDataValue("isblacked", !!result.isblacked)
    if (!result.desc) {
      result.setDataValue("desc", "暂无描述~")
    }

    return ctx.apiSuccess("ok", result)
  }

  // 指定用户评论列表
  async comments() {
    const { ctx, app, service } = this;
    ctx.validate(SCENE.comments)
    const { user_id, page } = ctx.params;
    if (await service.blacklist.isBlacklist(user_id)) {
      return ctx.apiSuccess('ok', {
        total: 0,
        per_page: 10,
        current_page: 1,
        last_page: 1,
        data: []
      })
    }

    // 分页
    const model = app.model.Comment
    const result = await service.comment.paginate(model, {
      page,
      // 排序
      order: [
        ['id', 'DESC']
      ],
      where: {
        user_id
      },
      include: [{
        model: app.model.User,
        as: "user",
        attributes: ["username", "avatar", "phone", "email"]
      }, {
        model: app.model.Article,
        as: "article",
        attributes: ["title", "images"]
      }],
      attributes: {
        exclude: ["update_time", "comment_id"]
      }
    });
    result.data = result.data.map(e => {
      e.name = null
      e.avatar = null
      if (e.user) {
        e.name = e.user.name
        e.avatar = e.user.avatar
      }
      e.article_title = "帖子已被删除"
      e.article_cover = ""
      if (e.article) {
        e.article_title = e.article.title
        e.article_cover = e.article.cover
      }
      delete e.user
      delete e.article
      return e
    });
    return ctx.apiSuccess('ok', result)
  }

  // 修改头像
  async changeAvatar() {
    const { ctx, service } = this;
    const authUser = ctx.authUser;
    const filepath = await service.file.uploadFile("avatar")
    // 构造文件访问URL
    const url = ctx.getUploadPath(filepath);
    authUser.avatar = url
    await authUser.save();
    ctx.apiSuccess('ok', authUser.avatar);
  }
}

module.exports = UserController;
