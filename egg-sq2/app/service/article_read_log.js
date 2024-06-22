'use strict';

const Service = require('./base');

class Article_read_logService extends Service {
    // 更新阅读记录
    async updateReadLog(article_id, article = null) {
        const { ctx, service, app } = this;
        // 获取当前用户ID
        const user_id = await service.user.getCurrentUserIdByToken();
        // 获取帖子
        if (!article) {
            article = await service.article.getArticleById(article_id);
        }
        // 获取IP
        const ip = ctx.ip;
        const where = {
            ip,
            article_id,
        }
        if (user_id) {
            where.user_id = user_id;
        }
        const log = await app.model.ArticleReadLog.findOne({
            where
        })
        // 更新最后一次阅读时间
        if (log) {
            // 获取11位当前时间戳
            log.update_time = Math.round(Date.now() / 1000);
            await log.save();
            return article.read_count;
        }

        // 阅读数+1
        article.read_count += 1;
        (await app.model.Article.findByPk(article_id)).update({
            read_count: article.read_count
        })

        // 添加阅读记录
        await app.model.ArticleReadLog.create({
            user_id,
            article_id,
            ip,
        });
        return article.read_count;
    }
}

module.exports = Article_read_logService;
