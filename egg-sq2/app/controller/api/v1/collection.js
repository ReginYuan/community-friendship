'use strict';

const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/collection")
class CollectionController extends Controller {
  // 收藏列表
  async index() {
    const { ctx, service, app } = this
    ctx.validate(SCENE.index)
    const userId = ctx.authUser.id
    const { page = 1 } = ctx.params

    // 获取收藏ID
    const { data } = await service.collection.paginate(app.model.Collection, {
      page,
      where: {
        user_id: userId
      },
      attributes: ["article_id"]
    })

    const articleIds = data.map(o => o.article_id)

    const result = await service.article.getArticleList(page, {
      id: {
        [app.Sequelize.Op.in]: articleIds
      }
    })

    return ctx.apiSuccess("ok", result)
  }

  // 收藏
  async save() {
    const { ctx, service } = this
    ctx.validate(SCENE.save)
    const { article_id } = ctx.params
    const count = await service.collection.addCollection(article_id)
    return ctx.apiSuccess("收藏成功", count)
  }

  // 取消收藏
  async delete() {
    const { ctx, service } = this
    ctx.validate(SCENE.delete)
    const { article_id } = ctx.params
    const count = await service.collection.removeCollection(article_id)
    return ctx.apiSuccess("取消收藏成功", count)
  }
}

module.exports = CollectionController;
