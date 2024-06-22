'use strict';

const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/feedback")
class FeedbackController extends Controller {
  // 反馈列表
  async index() {
    const { ctx, service, app } = this;
    ctx.validate(SCENE.index)
    const user_id = ctx.authUser.id;
    const { page = 1 } = ctx.params
    const result = await service.feedback.paginate(app.model.Feedback, {
      page,
      where: {
        user_id
      },
      include: [
        {
          model: app.model.User,
          attributes: ['username', 'avatar', "phone", "email"]
        }
      ],
      attributes: {
        exclude: ["user_id", "update_time"]
      }
    })

    result.data = result.data.map(o => {
      o.name = o.user ? o.user.name : null
      o.avatar = o.user ? o.user.avatar : null
      if (o.type === 'worker') {
        o.name = "官方人员"
      }
      delete o.user
      return o
    })

    return ctx.apiSuccess('ok', result);
  }

  // 用户反馈
  async save() {
    const { ctx, service } = this;
    const { content, images } = ctx.request.body;
    ctx.validate(SCENE.save(ctx))
    const res = await service.feedback.addFeedback(content, images);
    if (res) {
      return ctx.apiSuccess("反馈成功");
    }
    return ctx.apiFail("反馈失败");
  }
}

module.exports = FeedbackController;
