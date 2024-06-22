const rule = {
    page: {
        type: "number",
        required: true,
        desc: '页码'
    },
    keyword: {
        type: "string",
        required: true,
        desc: '搜索关键字'
    },
    id: {
        type: "number",
        required: true,
        desc: '话题id'
    },
    category_id: {
        type: "number",
        required: true,
        desc: '分类id'
    },
}

module.exports = {
    index: {
        page: rule.page,
        category_id: rule.category_id
    },
    search: {
        page: rule.page,
        keyword: rule.keyword
    },
    read: {
        id: rule.id
    }
}
