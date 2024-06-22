const rule = {
    id: {
        type: 'number',
        required: true,
        desc: "评论ID"
    },
    comment_id: {
        type: 'number',
        required: true,
        desc: "评论ID"
    },
    reply_id: {
        type: 'number',
        required: true,
        desc: "回复ID"
    },
    article_id: {
        type: 'number',
        required: true,
        desc: "帖子ID"
    },
    page: {
        type: 'number',
        required: true,
        desc: "分页"
    },
    content: {
        type: 'string',
        required: true,
        desc: "评论内容"
    },
}

module.exports = {
    read: {
        id: rule.id
    },
    delete: {
        id: rule.id
    },
    index(ctx) {
        if (ctx.url.includes('replies')) {
            return {
                comment_id: rule.comment_id,
                page: rule.page,
            }
        }
        return {
            article_id: rule.article_id,
            page: rule.page
        }
    },
    save(ctx) {
        if (ctx.url.includes('reply')) {
            return {
                reply_id: rule.reply_id,
                content: rule.content,
            }
        }
        return {
            article_id: rule.article_id,
            content: rule.content
        }
    },
}
