'use strict';

const Controller = require('egg').Controller;

class CommonController extends Controller {
    async agreement() {
        await this.ctx.render("api/common/agreement.html");
    }

    async privacy() {
        await this.ctx.render("api/common/privacy.html");
    }
}

module.exports = CommonController;
