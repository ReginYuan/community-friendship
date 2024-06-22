'use strict';
const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/upgradation")
class UpgradationController extends Controller {
  // 检查更新
  async index() {
    const { ctx, app } = this;
    ctx.validate(SCENE.index)
    const {
      appVersion,
      appid,
      platform,
    } = ctx.request.body;
    const data = await app.model.Upgradation.findOne({
      where: {
        appid,
        platform,
        stable_publish: 1
      },
      order: [
        ['id', 'DESC']
      ]
    })
    if (!data || data.version === appVersion) {
      // 不是强制更新
      if (!data.is_mandatory) {
        return ctx.apiSuccess("暂无更新", null)
      }
    }

    return ctx.apiSuccess("app", {
      code: 102,
      message: "app更新",
      ...(app.toArray(data))
    })
  }
}

module.exports = UpgradationController;
