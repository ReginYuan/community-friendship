'use strict';

const Service = require('./base');

class SupportService extends Service {
    // 顶踩操作
    async handleAction(article_id, type) {
        const { ctx, app } = this
        const action = type === 1 ? '顶' : '踩';
        // 获取当前登录用户
        const user = ctx.authUser;
        // 查询之前是否操作过
        const support = await app.model.Support.findOne({
            where: {
                article_id,
                user_id: user.id
            }
        });
        // 之前操作过
        if (support) {
            // 如果是一样的操作，则取消
            if (support.type === type) {
                if (await support.destroy()) {
                    return `取消${action}成功`
                }
                ctx.throw(500, '取消失败');
            }
            // 如果操作不一样，则修改
            support.type = type;
            if (await support.save()) {
                return `${action}成功`
            }
        }

        // 之前没有操作过
        if (await ctx.model.Support.create({
            user_id: user.id,
            article_id,
            type
        })) {
            return `${action}成功`
        }
        ctx.throw(500, action + '失败')
    }
}

module.exports = SupportService;
