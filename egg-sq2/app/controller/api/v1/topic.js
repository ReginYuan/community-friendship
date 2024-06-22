'use strict';

const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/topic")
class TopicController extends Controller {
  // 话题列表
  async index() {
    const { ctx, service } = this;
    ctx.validate(SCENE.index);
    const { category_id = 0, page } = ctx.params
    const where = {};
    if (category_id !== 0) {
      where.category_id = category_id
    }
    const result = await service.topic.getTopicList(page, where);
    return ctx.apiSuccess('ok', result);
  }

  // 搜索话题
  async search() {
    const { ctx, service, app } = this;
    ctx.validate(SCENE.search);
    const { page } = ctx.params
    const { keyword } = ctx.query
    const where = {
      title: {
        [app.Sequelize.Op.like]: '%' + keyword + '%'
      }
    }
    const result = await service.topic.getTopicList(page, where);
    return ctx.apiSuccess('ok', result);
  }

  // 查看话题
  async read() {
    const { ctx, app, service } = this;
    ctx.validate(SCENE.read);
    const { id } = ctx.params;
    let result = await app.model.Topic.findByPk(id, {
      include: [
        {
          model: app.model.Category,
          as: 'category',
          attributes: ['title']
        }
      ]
    })

    if (!result) {
      ctx.throw(404, '没有找到数据')
    }

    result = app.toArray(result)
    if (result.category) {
      result.category_title = result.category.title
      delete result.category
    }

    // 获取话题今日发帖数
    result.today_article_count = await service.topic.getTodayCount(app.model.Article, 'topic_id', [id])

    return ctx.apiSuccess('ok', result);
  }
}

module.exports = TopicController;
