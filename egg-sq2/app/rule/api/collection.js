const rule = {
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
}

module.exports = {
    save: {
        article_id: rule.article_id
    },
    delete: {
        article_id: rule.article_id
    },
    index: {
        page: rule.page
    }
}
