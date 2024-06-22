'use strict';
const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/follow")
class FollowController extends Controller {

  // 指定用户的粉丝列表
  async fans() {
    const { ctx, service, app } = this;
    ctx.validate(SCENE.fans)
    const {
      user_id,
      page
    } = ctx.params
    const result = await service.follow.paginate(app.model.Follow, {
      page,
      where: {
        follow_id: user_id
      },
      include: [
        {
          model: app.model.User,
          as: 'fan',
          attributes: ['id', 'username', 'avatar', "phone", "email", "desc", "fans_count"]
        }
      ],
      attributes: {
        exclude: ["follow_id", "update_time"]
      }
    })

    // 获取当前登录用户ID
    const currentUserId = await service.user.getCurrentUserIdByToken()

    // 获取所有粉丝ID
    const fansIds = result.data.map(o => o.user_id)

    // 找出当前用户关注了这些粉丝的ID
    const ds = (await app.model.Follow.findAll({
      where: {
        user_id: currentUserId,
        follow_id: fansIds
      },
      attributes: ["follow_id"]
    })).map(o => o.follow_id)

    result.data = result.data.map(o => {
      o.is_follow = ds.includes(o.user_id)
      o.id = o.user_id
      o.name = o.fan ? o.fan.name : null
      o.avatar = o.fan ? o.fan.avatar : null
      o.desc = o.fan ? o.fan.desc : null
      o.fans_count = o.fan ? o.fan.fans_count : 0
      if (!o.desc) {
        o.desc = "暂无描述~"
      }
      delete o.fan
      return o
    })

    return ctx.apiSuccess('ok', result)
  }

  // 指定用户的关注列表
  async follows() {
    const { ctx, service, app } = this;
    ctx.validate(SCENE.follows)
    const {
      user_id,
      page
    } = ctx.params
    const result = await service.follow.paginate(app.model.Follow, {
      page,
      where: {
        user_id
      },
      include: [
        {
          model: app.model.User,
          as: 'followuser',
          attributes: ['id', 'username', 'avatar', "phone", "email", "desc", "fans_count"]
        }
      ],
      attributes: {
        exclude: ["update_time"]
      }
    })

    // 获取当前登录用户ID
    const currentUserId = await service.user.getCurrentUserIdByToken()

    // 获取所有关注ID
    const followsIds = result.data.map(o => o.follow_id)

    // 找出当前用户关注了这些关注人的ID
    const ds = (await app.model.Follow.findAll({
      where: {
        user_id: currentUserId,
        follow_id: followsIds
      },
      attributes: ["follow_id"]
    })).map(o => o.follow_id)

    result.data = result.data.map(o => {
      // 登录者本人
      if (currentUserId === user_id) {
        o.is_follow = true
      } else {
        o.is_follow = ds.includes(o.follow_id)
      }
      o.id = o.user_id
      o.name = o.followuser ? o.followuser.name : null
      o.avatar = o.followuser ? o.followuser.avatar : null
      o.desc = o.followuser ? o.followuser.desc : null
      o.fans_count = o.followuser ? o.followuser.fans_count : 0
      if (!o.desc) {
        o.desc = "暂无描述~"
      }
      delete o.followuser
      return o
    })

    return ctx.apiSuccess('ok', result)
  }

  // 关注用户
  async save() {
    const { ctx, service } = this;
    ctx.validate(SCENE.save)
    const { id } = ctx.params
    if (await service.follow.addFollow(id)) {
      return ctx.apiSuccess("关注成功");
    }
    return ctx.apiFail("关注失败");
  }

  // 取消关注用户
  async delete() {
    const { ctx, service } = this;
    ctx.validate(SCENE.delete)
    const { id } = ctx.params
    if (await service.follow.removeFollow(id)) {
      return ctx.apiSuccess("取消关注成功");
    }
    return ctx.apiFail("取消关注失败");
  }
}

module.exports = FollowController;
