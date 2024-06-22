const rule = {
    id: {
        type: 'number',
        required: true,
        desc: "用户ID"
    },
    user_id: {
        type: 'number',
        required: true,
        desc: "用户ID"
    },
    page: {
        type: 'number',
        required: true,
        desc: "分页"
    },
}

module.exports = {
    save: {
        id: rule.id
    },
    delete: {
        id: rule.id
    },
    fans: {
        user_id: rule.user_id,
        page: rule.page
    },
    follows: {
        user_id: rule.user_id,
        page: rule.page
    }
}
