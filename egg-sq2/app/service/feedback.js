'use strict';

const Service = require('./base');

class FeedbackService extends Service {
    // 添加反馈
    async addFeedback(content, images) {
        const { ctx, app } = this;
        const data = {
            content,
            images,
            user_id: ctx.authUser.id,
            type: 'user'
        };
        return await app.model.Feedback.create(data);
    }
}

module.exports = FeedbackService;
