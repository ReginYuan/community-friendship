'use strict';

const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/support")
class SupportController extends Controller {
  // 顶踩操作
  async action() {
    const { ctx, service } = this;
    ctx.validate(SCENE.action);
    const { type, article_id } = ctx.params;
    const t = type === "ding" ? 1 : 0;
    const result = await service.support.handleAction(article_id, t);
    ctx.apiSuccess(result);
  }
}

module.exports = SupportController;
