const rule = {
    id: {
        type: 'number',
        required: true,
        desc: "ID"
    },
    page: {
        type: 'number',
        required: true,
        desc: "分页"
    },
}

module.exports = {
    index: {
        page: rule.page
    },
    save: {
        id: rule.id
    },
    delete: {
        id: rule.id
    },
}
