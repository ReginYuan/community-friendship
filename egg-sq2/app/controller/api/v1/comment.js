'use strict';

const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/comment")
class CommentController extends Controller {
  // 评论/回复列表
  async index() {
    const { ctx, service } = this;
    ctx.validate(SCENE.index(ctx))
    let hidden = ["update_time"];
    const isreply = ctx.params.hasOwnProperty("comment_id")
    const where = {}
    if (isreply) {
      where.comment_id = ctx.params.comment_id
    } else {
      where.comment_id = null
      where.article_id = ctx.params.article_id
      hidden = ["quote"]
    }

    const page = ctx.params.page || 1;
    const result = await service.comment.getCommentList(page, where, hidden)

    return ctx.apiSuccess('ok', result)
  }

  // 评论/回复
  async save() {
    const { ctx, service } = this;
    ctx.validate(SCENE.save(ctx))
    const { article_id, content, reply_id } = ctx.request.body;
    // 回复
    let res = null
    if (reply_id) {
      res = await service.comment.addReply(reply_id, content);
    } else {
      // 评论
      res = await service.comment.addComment(article_id, content);
    }
    if (res) {
      return ctx.apiSuccess('发布成功', res);
    }
    return ctx.apiFail('发布失败');
  }

  // 查看评论
  async read() {
    const { ctx } = this;
    ctx.validate(SCENE.read)
    const { id } = ctx.params;
    const data = await ctx.service.comment.getCommentById(id);
    if (!data) {
      return ctx.apiFail("没有找到数据", 404);
    }
    return ctx.apiSuccess('ok', data);
  }

  // 删除评论/回复
  async delete() {
    const { ctx, app } = this;
    ctx.validate(SCENE.delete)
    const { id } = ctx.params;
    const authUser = ctx.authUser
    const comment = await app.model.Comment.findByPk(id)
    if (!comment) {
      ctx.throw(404, '评论不存在')
    }
    if (comment.user_id !== authUser.id) {
      ctx.throw(403, '没有权限删除')
    }
    if (await comment.destroy()) {
      return ctx.apiSuccess('删除成功')
    }
    ctx.throw(500, '删除失败')
  }
}

module.exports = CommentController;
