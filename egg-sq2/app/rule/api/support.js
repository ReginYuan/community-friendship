const rule = {
    article_id: {
        type: 'number',
        required: false,
        desc: "帖子ID"
    },
    type: {
        type: 'string',
        required: true,
        desc: "操作类型",
        range: {
            in: ['ding', 'cai']
        }
    }
}

module.exports = {
    action: {
        article_id: rule.article_id,
        type: rule.type
    },
}
