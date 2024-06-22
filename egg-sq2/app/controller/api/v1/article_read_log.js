'use strict';

const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/article_read_log")
class Article_read_logController extends Controller {
  async index() {
    const { ctx, app, service } = this
    ctx.validate(SCENE.index)
    // 获取当前登录用户ID
    const userId = ctx.authUser.id
    const { page = 1 } = ctx.params

    // 获取观看历史列表
    const { data } = await service.articleReadLog.paginate(app.model.ArticleReadLog, {
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
}

module.exports = Article_read_logController;
