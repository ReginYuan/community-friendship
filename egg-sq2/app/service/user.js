'use strict';

const Service = require('./base');

class UserService extends Service {
    // 用户是否存在
    async isUserExist(...args) {
        const { ctx, app } = this;
        let key = "";
        let value = "";
        if (args.length === 2) {
            key = args[0];
            value = args[1];
        } else {
            value = args[0];
            key = "";
            if (app.isPhoneNumber(value)) {
                key = "phone";
            } else if (app.isEmail(value)) {
                key = "email";
            } else {
                return false;
            }
        }

        // 获取用户详细信息
        const user = await this.getUserInfo(key, value);
        if (user) {
            // 用户已被禁用
            if (user.getDataValue("status") === 0) {
                ctx.throw(400, '该用户已被禁用');
            }
            return user;
        }
        return false;
    }

    // 获取用户信息
    async getUserInfo(key, value) {
        const { ctx } = this;
        const user = await ctx.model.User.findOne({
            where: {
                [key]: value
            }
        });
        return user;
    }

    // 登录处理
    async loginHandle(user) {
        const { ctx, app } = this;
        user = app.toArray(user);
        user.token = app.createToken(user);
        // 存储token
        await ctx.service.cache.set(user.token, user);
        // 将用户ID和token进行绑定
        await ctx.service.cache.set("login_" + user.id, user.token);
        return user;
    }
    // 根据header获取当前登录用户ID
    async getCurrentUserIdByToken(prefix = '') {
        const { ctx, service } = this;
        const token = ctx.header.token;
        let userId = 0
        if (token) {
            let key = token
            if (prefix !== '') {
                key = prefix + token
            }
            const currentUser = await service.cache.get(key)
            if (currentUser) {
                userId = currentUser.id
            }
        }
        return userId
    }

    // 更新用户帖子数
    async updateArticlesCount(user_id) {
        if (!user_id) {
            return
        }
        const { app } = this;
        // 统计用户帖子数
        const count = await app.model.Article.count({
            where: {
                user_id
            }
        });
        await app.model.User.update({
            articles_count: count
        }, {
            where: {
                id: user_id
            }
        });
    }

    // // 是否关注用户
    // async isFollowTarget(user_id, follow_id) {
    //     const { app } = this;
    //     const data = await app.model.Follow.findOne({
    //         where: {
    //             user_id,
    //             follow_id
    //         },
    //         attributes: ['id']
    //     })
    //     console.log(data)
    //     return !!data
    // }
}

module.exports = UserService;
