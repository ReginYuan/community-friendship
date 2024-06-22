'use strict';

const Service = require('./base');

class TopicService extends Service {
    // 获取话题列表
    async getTopicList(page, where = {}) {
        const model = this.app.model.Topic
        // 话题列表分页
        const result = await this.paginate(model, {
            page,
            where,
            order: ['id', 'DESC'],
        });

        // 获取所有话题ID
        const topicIds = result.data.map(item => item.id)

        // 统计对应话题的今日发帖数
        const todayCounts = await this.getTodayCount(this.app.model.Article, "topic_id", topicIds)

        result.data = result.data.map(o => {
            const item = todayCounts.find(item => item.topic_id === o.id)
            o.today_article_count = item ? item.todayCount : 0
            return o
        })

        return result;
    }

    // 统计和更新话题帖子数
    async updateArticlesCount(topic_id) {
        if (!topic_id) {
            return
        }
        // 统计话题帖子数
        const count = await this.app.model.Article.count({
            topic_id
        })
        // 更新话题帖子数
        await this.app.model.Topic.update({
            article_count: count
        }, {
            where: {
                id: topic_id
            }
        })
    }
}

module.exports = TopicService;
