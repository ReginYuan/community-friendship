'use strict';

const Service = require('./base');

class CollectionService extends Service {
    // 判断当前用户是否收藏该帖子
    async isCurrentUserCollectArticle(article_id) {
        const { service, app } = this
        // 获取当前登录用户ID
        const user_id = await service.user.getCurrentUserIdByToken()
        if (!user_id) {
            return false
        }
        const collection = await app.model.Collection.findOne({
            where: {
                user_id,
                article_id
            },
            attributes: ['id']
        })

        return !!collection
    }

    // 是否在收藏列表里
    async isCollection(user_id, article_id) {
        return await this.app.model.Collection.findOne({
            where: {
                user_id,
                article_id
            }
        })
    }

    // 收藏帖子
    async addCollection(article_id) {
        const { app, ctx, service } = this
        // 获取当前登录用户ID
        const user_id = await ctx.authUser.id
        // 是否已经收藏过了
        if (await this.isCollection(user_id, article_id)) {
            this.ctx.throw(400, '已经收藏过了')
        }
        const article = await app.model.Article.findOne({
            where: {
                id: article_id
            },
        })
        if (!article) {
            this.ctx.throw(400, '帖子不存在')
        }
        const collection = await app.model.Collection.create({
            user_id,
            article_id
        })
        if (!collection) {
            this.ctx.throw(400, '收藏失败')
        }
        return await service.article.updateCollectCount(article)
    }

    // 取消收藏帖子
    async removeCollection(article_id) {
        const { ctx, service } = this
        // 获取当前登录用户ID
        const user_id = await ctx.authUser.id
        // 是否已经收藏过了
        const collection = await this.isCollection(user_id, article_id)
        if (!collection) {
            ctx.throw(400, '你还没有收藏过')
        }
        if (!(await collection.destroy())) {
            ctx.throw(400, '取消收藏失败')
        }
        // 取消收藏成功，更新收藏数
        return await service.article.updateCollectCount(article_id)
    }
}

module.exports = CollectionService;
