'use strict';

const Service = require('./base');

class BlacklistService extends Service {
    // 是否在黑名单中
    async isBlacklist(id) {
        const { app, service } = this;
        const user_id = await service.user.getCurrentUserIdByToken();
        if (!user_id) {
            return false;
        }
        const data = await app.model.Blacklist.findOne({
            where: {
                user_id,
                black_id: id,
            },
            attributes: ['id'],
        });
        if (data) {
            return true;
        }
        return false;
    }

    // 获取所有我拉黑/被我拉黑的用户ID
    async getBlackUsers() {
        const { service, app } = this;
        // 获取当前登录用户ID
        const user_id = await service.user.getCurrentUserIdByToken();
        if (!user_id) {
            return [];
        }
        const v1 = (await app.model.Blacklist.findAll({
            attributes: ['black_id'],
            where: {
                user_id,
            },
        })).map(item => item.black_id);
        const v2 = (await app.model.Blacklist.findAll({
            attributes: ['user_id'],
            where: {
                black_id: user_id,
            },
        })).map(item => item.user_id);
        return [
            ...v1,
            ...v2
        ]
    }

    // 加入黑名单
    async addBlacklist(id) {
        const { ctx, service, app } = this;
        const authUser = ctx.authUser
        if (authUser.id === id) {
            ctx.throw(400, '不能操作自己');
        }
        // 用户是否存在
        if (!(await service.user.isUserExist('id', id))) {
            ctx.throw(400, '用户不存在');
        }
        return app.model.Blacklist.create({
            user_id: authUser.id,
            black_id: id,
        });
    }

    // 删除黑名单
    async removeBlacklist(id) {
        const { ctx, app } = this;
        const authUser = ctx.authUser
        if (authUser.id === id) {
            ctx.throw(400, '不能操作自己');
        }
        return app.model.Blacklist.destroy({
            where: {
                user_id: authUser.id,
                black_id: id,
            },
        });
    }

    // 对方是否拉黑了我
    async isBlackedByTarget(target_id, my_id = 0) {
        if (my_id === 0) {
            my_id = this.ctx.authUser.id;
        }

        const data = await this.app.model.Blacklist.findOne({
            where: {
                user_id: target_id,
                black_id: my_id
            },
            attributes: ['id']
        });
        return !!data
    }
}

module.exports = BlacklistService;
