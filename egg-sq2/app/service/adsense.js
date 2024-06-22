'use strict';

const Service = require('egg').Service;

class AdsenseService extends Service {
    // 获取广告列表
    async getList(type) {
        return await this.ctx.model.Adsense.findAll({
            attributes: [ 'src', 'url' ],
            where: {
                type
            }
        });
    }
}

module.exports = AdsenseService;
