'use strict';

const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/article")
class ArticleController extends Controller {
  // 显示话题/分类下的帖子列表
  async index() {
    const { ctx, service } = this;
    ctx.validate(SCENE.index(ctx))
    // 排序
    const order = ctx.request.query.order === 'hot' ? [
      ['ding_count', 'desc'],
      ['id', 'desc']
    ] : [
      ["create_time", "desc"]
    ];
    // 分类
    let value = ctx.params.category_id || 0
    let key = "category_id"
    // 话题
    if (ctx.params.topic_id) {
      value = ctx.params.topic_id || 0
      key = "topic_id"
    }
    // 用户
    if (ctx.params.user_id) {
      value = ctx.params.user_id || 0
      key = "user_id"
    }
    // 分页页码
    const page = ctx.params.page || 1
    const where = {}
    if (value !== 0) {
      where[key] = value
    }

    let result;
    if (key === 'category_id' && value === 0) {
      // 获取关注用户的帖子列表
      result = await service.article.getMyFollowArticleList(page, order)
    } else {
      result = await service.article.getArticleList(page, where, order)
    }

    return ctx.apiSuccess('ok', result);

  }

  // 搜索帖子
  async search() {
    const { ctx, service, app } = this;
    const Op = app.Sequelize.Op;
    ctx.validate(SCENE.search)
    const { keyword } = ctx.request.query
    const { page } = ctx.params
    const result = await service.article.getArticleList(page, {
      content: {
        [Op.like]: `%${keyword}%`
      }
    })
    return ctx.apiSuccess('ok', result);
  }

  // 发布帖子
  async save() {
    const { ctx, app } = this;
    const images = ctx.request.body.images || []
    ctx.validate(SCENE.save(ctx))
    const authUser = ctx.authUser
    const param = ctx.request.body
    const data = {
      category_id: param.category_id,
      user_id: authUser.id,
      content: param.content,
      images,
      topic_id: param.topic_id || 0,
      title: param.content.substr(0, 100) + '...'
    }

    // 话题是否存在
    if (data.topic_id) {
      if (!(await app.model.Topic.findByPk(data.topic_id))) {
        ctx.throw(404, '话题不存在')
      }
    }

    // 创建帖子
    const result = await app.model.Article.create(data)
    if (!result) {
      ctx.throw(500, '发布失败')
    }
    return ctx.apiSuccess('发布成功');
  }

  // 查看帖子详情
  async read() {
    const { ctx, service } = this;
    ctx.validate(SCENE.read)
    const { id } = ctx.params
    const data = await service.article.getArticleById(id)
    if (!data) {
      ctx.throw(404, '帖子不存在')
    }
    // 更新阅读记录
    data.read_count = await service.articleReadLog.updateReadLog(id, data)
    // 判断当前用户是否收藏该帖子
    data.isCollect = await service.collection.isCurrentUserCollectArticle(id)
    return ctx.apiSuccess('ok', data);
  }

  // 删除帖子
  async delete() {
    const { ctx, app } = this;
    const authUser = ctx.authUser
    ctx.validate(SCENE.delete)
    const { id } = ctx.params
    const article = await app.model.Article.findByPk(id)
    if (!article) {
      ctx.throw(404, '帖子不存在')
    }
    if (article.user_id !== authUser.id) {
      ctx.throw(403, '没有权限删除')
    }
    if (await article.destroy()) {
      return ctx.apiSuccess('删除成功');
    }
    ctx.throw(500, '删除失败')
  }
}

module.exports = ArticleController;
