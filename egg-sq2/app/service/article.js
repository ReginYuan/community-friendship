'use strict';

const Service = require('./base');

class ArticleService extends Service {
    // 获取帖子列表
    async getArticleList(page = 1, where = {}, order = [
        ['id', 'desc']
    ]) {
        const { service, app } = this;
        const Op = app.Sequelize.Op;
        // 获取所有我拉黑/被我拉黑的用户ID
        const BlackUserIds = await service.blacklist.getBlackUsers();
        // 列表分页数据
        if (BlackUserIds.length > 0) {
            if (where.user_id) {
                where[Op.and] = [
                    {
                        user_id: where.user_id
                    },
                    {
                        user_id: {
                            [Op.notIn]: BlackUserIds
                        }
                    }
                ]
                delete where.user_id
            } else {
                where = {
                    ...where,
                    user_id: {
                        [Op.notIn]: BlackUserIds
                    }
                }
            }
        }
        return await this.getList(page, where, order)
    }

    // 获取关注用户的帖子列表
    async getMyFollowArticleList(page = 1, order = [
        ['id', 'desc']
    ]) {
        const { app, service } = this;
        const Op = app.Sequelize.Op;
        // 获取当前用户ID
        const userId = await service.user.getCurrentUserIdByToken();

        // 当前用户没登陆，返回空
        if (userId === 0) {
            return {
                total: 0,
                per_page: 10,
                current_page: 1,
                last_page: 0,
                data: []
            }
        }

        // 获取关注用户ID列表
        const uids = await service.follow.getFollowIdListByUserId(userId);
        const where = {
            user_id: {
                [Op.in]: uids
            }
        }
        return await this.getList(page, where, order)
    }

    // 获取列表分页数据
    async getList(page = 1, where = {}, order = [
        ['id', 'desc']
    ]) {
        const { app, service } = this;
        // 获取当前用户ID
        const userId = await service.user.getCurrentUserIdByToken();
        const model = app.model.Article.scope({
            method: ['isfollow', userId]
        }, {
            method: ['isSupport', userId]
        })
        const result = await this.paginate(model, {
            page,
            where,
            order,
            attributes: {
                exclude: ["content"]
            },
            include: [
                {
                    model: app.model.User,
                    attributes: ["username", "avatar", "phone", "email"]
                },
                {
                    model: app.model.Topic,
                    attributes: ["title"]
                }
            ]
        })

        // 格式化结果
        result.data = result.data.map(o => this.formatArticleItem(o))

        return result
    }

    // 格式化返回结果
    formatArticleItem(o) {
        o.name = o.user ? o.user.name : null
        o.avatar = o.user ? o.user.avatar : null
        o.topic_name = o.topic ? o.topic.title : null
        o.isfollow = !!o.follow
        o.user_support_action = ""
        if (o.support) {
            o.user_support_action = o.support.type === 1 ? 'ding' : 'cai'
        }
        delete o.support
        delete o.user
        delete o.topic
        delete o.follow
        return o
    }

    // 根据id获取帖子
    async getArticleById(id) {
        const { app, service } = this;
        // 获取当前用户ID
        const userId = await service.user.getCurrentUserIdByToken();
        let data = await app.model.Article.scope({
            method: ['isfollow', userId]
        }, {
            method: ['isSupport', userId]
        }).findByPk(id, {
            include: [
                {
                    model: app.model.User,
                    attributes: ["username", "avatar", "phone", "email", "status"]
                },
                {
                    model: app.model.Topic,
                    attributes: ["title"]
                }
            ]
        })

        if (data) {
            data = app.toArray(data)
            data.user_status = data.user ? data.user.status : 1
            data = this.formatArticleItem(data)
        }

        return data
    }

    // 帖子是否存在
    async isExist(id) {
        const data = await this.app.model.Article.findOne({
            where: {
                id
            },
            attributes: ["id"]
        })
        return !!data
    }

    // 更新帖子收藏数
    async updateCollectCount(article) {
        const { app, ctx } = this
        // 判断article是否是number
        if (typeof article === 'number') {
            article = await app.model.Article.findByPk(article)
        }
        if (!article) {
            ctx.throw(404, '帖子不存在')
        }
        // 统计收藏数
        const count = await app.model.Collection.count({
            where: {
                article_id: article.id
            }
        })
        // 更新收藏数
        article.collect_count = count
        await article.save()
        // 返回收藏数
        return count
    }
}

module.exports = ArticleService;
