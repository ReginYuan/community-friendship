'use strict';

const Service = require('./base');

class FollowService extends Service {
    // 获取关注用户ID列表
    async getFollowIdListByUserId(user_id) {
        const { app } = this;
        return (await app.model.Follow.findAll({
            where: {
                user_id
            },
            attributes: ['follow_id']
        })).map(o => o.follow_id)
    }

    // 是否在关注列表里
    async isFollow(user_id, follow_id) {
        return await this.app.model.Follow.findOne({
            where: {
                user_id,
                follow_id
            }
        })
    }

    // 关注用户
    async addFollow(follow_id) {
        const { ctx, app, service } = this
        // 获取当前登录用户ID
        const user_id = ctx.authUser.id;
        // 是否已关注过
        if (!(await this.isFollow(user_id, follow_id))) {
            // 被关注用户ID是否存在
            if (!(await service.user.isUserExist("id", follow_id))) {
                ctx.throw(404, '用户不存在');
            }

            // 是否被对方拉黑
            if (await service.blacklist.isBlackedByTarget(follow_id, user_id)) {
                ctx.throw(403, '您已被对方拉黑');
            }

            await app.model.Follow.create({
                user_id,
                follow_id
            });
            return true;
        }
        ctx.throw(400, '已经关注过了');
    }

    // 取消关注用户
    async removeFollow(follow_id) {
        const { ctx } = this;
        // 获取当前登录用户ID
        const user_id = ctx.authUser.id;
        // 是否已关注
        const follow = await this.isFollow(user_id, follow_id);
        if (follow) {
            if (!(await follow.destroy())) {
                ctx.throw(500, '取消关注失败');
            }
            return true
        }
        ctx.throw(500, '你还没有关注该用户');
    }
}

module.exports = FollowService;
