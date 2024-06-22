'use strict';

const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/category")
class CategoryController extends Controller {
  // 获取分类列表
  async index() {
    const { ctx, app } = this;
    ctx.validate(SCENE.index)
    const { type } = ctx.params;
    const result = await app.model.Category.findAll({
      where: {
        type
      },
      attributes: ["id", "title"]
    })
    return ctx.apiSuccess("ok", result)
  }
}

module.exports = CategoryController;
