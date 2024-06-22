'use strict';
const Service = require('./base');

class CommentService extends Service {
    async getCommentList(page = 1, where = {}, exclude = []) {
        const { app } = this
        const model = app.model.Comment;
        const result = await this.paginate(model, {
            page,
            where,
            order: [
                ['id', 'desc']
            ],
            include: [
                {
                    model: app.model.User,
                    attributes: ['username', 'avatar', "phone", "email"],
                    as: 'user'
                }
            ],
            attributes: {
                exclude
            }
        });

        result.data = result.data.map(o => this.formatArticleItem(o))

        return result
    }

    // 格式化返回结果
    formatArticleItem(o) {
        o.name = o.user ? o.user.name : null
        o.avatar = o.user ? o.user.avatar : null
        delete o.user

        if (o.hasOwnProperty("follow")) {
            o.isfollow = !!o.follow
            delete o.follow
        }

        return o
    }

    async getCommentById(id) {
        const { app, service } = this
        // 获取当前用户ID
        const userId = await service.user.getCurrentUserIdByToken();

        let data = await app.model.Comment.scope({
            method: ['isfollow', userId]
        }).findByPk(id, {
            include: [
                {
                    model: app.model.User,
                    attributes: ['username', 'avatar', "phone", "email"],
                    as: 'user'
                }
            ],
            attributes: {
                exclude: ["update_time", "quote"]
            }
        })

        if (data) {
            data = this.formatArticleItem(app.toArray(data))
        }

        return data
    }

    // 回复帖子评论
    async addReply(reply_id, content) {
        const { app, ctx, service } = this
        const authUser = ctx.authUser

        // 评论是否存在
        const reply = await app.model.Comment.findByPk(reply_id, {
            include: [
                {
                    model: app.model.User,
                    attributes: ['username', 'avatar', "phone", "email"],
                    as: 'user'
                }
            ]
        });
        if (!reply) {
            ctx.throw(404, '你要回复的评论不存在');
        }

        if (!(await service.article.isExist(reply.article_id))) {
            ctx.throw(404, '帖子不存在')
        }

        const data = {
            article_id: reply.article_id,
            content,
            user_id: authUser.id,
            comment_id: reply_id
        }

        // 引用评论
        if (reply.comment_id) {
            data.comment_id = reply.comment_id;
            data.quote = {
                content: reply.content,
                user_id: reply.user_id,
                name: reply.user ? app.model.User.getName(reply.user) : null,
                avatar: reply.user ? reply.user.avatar : null
            };
        }

        const comment = await app.model.Comment.create(data);
        return {
            id: comment.id,
            article_id: reply.article_id,
            comment_id: reply_id,
            user_id: authUser.id,
            reply_count: 0,
            content,
            create_time: comment.create_time,
            avatar: authUser.avatar,
            name: app.model.User.getName(authUser),
            quote: data.quote || null
        }
    }

    // 发表帖子评论
    async addComment(article_id, content) {
        const { app, ctx, service } = this
        const authUser = ctx.authUser

        const data = {
            article_id,
            content,
            user_id: authUser.id
        }

        if (!(await service.article.isExist(article_id))) {
            ctx.throw(404, '帖子不存在')
        }

        const comment = await app.model.Comment.create(data);

        return {
            id: comment.id,
            article_id,
            comment_id: null,
            user_id: authUser.id,
            reply_count: 0,
            content,
            create_time: comment.create_time,
            avatar: authUser.avatar,
            name: app.model.User.getName(authUser)
        }
    }
}

module.exports = CommentService;
