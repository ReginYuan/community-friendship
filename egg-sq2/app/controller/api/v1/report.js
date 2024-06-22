'use strict';

const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/report")
class ReportController extends Controller {
  async save() {
    const { ctx, app, service } = this;
    ctx.validate(SCENE.save)
    const user_id = ctx.authUser.id
    const {
      report_uid,
      content
    } = ctx.request.body
    if (!(await service.user.isUserExist("id", report_uid))) {
      ctx.throw(404, "举报对象不存在");
    }
    const where = {
      user_id,
      report_uid,
      state: "pending"
    }
    if (await app.model.Report.findOne({
      where,
      attributes: ["id"]
    })) {
      ctx.throw(400, "您已经举报过该用户了");
    }
    where.content = content;
    const res = await app.model.Report.create(where);
    if (!res) {
      ctx.throw(500, "举报失败");
    }
    return ctx.apiSuccess('提交成功，等待管理员处理');
  }
}

module.exports = ReportController;
