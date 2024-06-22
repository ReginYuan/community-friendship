'use strict';

const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/blacklist")
class BlacklistController extends Controller {
  // 我的黑名单列表
  async index() {
    const { ctx, service, app } = this;
    ctx.validate(SCENE.index);
    const Op = app.Sequelize.Op
    const userId = ctx.authUser.id
    const { page } = ctx.params
    const model = app.model.Blacklist
    const result = await service.blacklist.paginate(model, {
      page,
      where: {
        user_id: userId
      },
      order: [
        ["id", "desc"]
      ],
      include: [
        {
          model: app.model.User,
          as: "blackuser",
          attributes: ["username", 'phone', 'email', "avatar", "desc", "fans_count"]
        }
      ],
      attributes: {
        exclude: ["user_id", "update_time"]
      }
    })

    // 我是否关注了这些用户
    const blackeduIds = result.data.map(o => o.black_id)
    let followeds = []
    if (blackeduIds.length) {
      followeds = (await app.model.Follow.findAll({
        where: {
          follow_id: {
            [Op.in]: blackeduIds
          },
          user_id: userId
        },
        attributes: ["follow_id"]
      })).map(o => o.follow_id)
    }

    result.data = result.data.map(o => {
      o.id = o.black_id
      o.name = o.blackuser ? o.blackuser.name : null
      o.avatar = o.blackuser ? o.blackuser.avatar : null
      o.desc = o.blackuser ? o.blackuser.desc : "暂无描述~"
      o.fans_count = o.blackuser ? o.blackuser.fans_count : 0

      o.isfollow = followeds.includes(o.id)

      delete o.blackuser
      delete o.black_id
      return o
    })

    return ctx.apiSuccess("ok", result)
  }

  // 加入黑名单
  async save() {
    const { ctx, service } = this;
    ctx.validate(SCENE.save);
    const { id } = ctx.params
    console.log(await service.blacklist.isBlacklist(id))
    if (await service.blacklist.isBlacklist(id)) {
      ctx.throw(400, "已在黑名单中")
    }
    await service.blacklist.addBlacklist(id)
    return ctx.apiSuccess("加入黑名单成功")
  }

  // 移除黑名单
  async delete() {
    const { ctx } = this;
    ctx.validate(SCENE.delete);
    const { id } = ctx.params
    if (!(await ctx.service.blacklist.isBlacklist(id))) {
      ctx.throw(400, "未在黑名单中")
    }
    await ctx.service.blacklist.removeBlacklist(id)
    return ctx.apiSuccess("移除黑名单成功")
  }
}

module.exports = BlacklistController;
