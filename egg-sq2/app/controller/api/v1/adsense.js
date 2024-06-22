'use strict';

const Controller = require('egg').Controller;

class AdsenseController extends Controller {
    // 列表
    async index() {
        const { ctx } = this;

        ctx.validate({
            type: {
                type: "string",
                required: true,
                desc: "类型",
                range: {
                    in: [ "my" ]
                }
            }
        });

        const { type = 'my' } = ctx.params;
        const result = await ctx.service.adsense.getList(type);
        ctx.apiSuccess("ok", result)
    }
}

module.exports = AdsenseController;
