const rule = {
    page: {
        type: 'number',
        required: true,
        desc: "分页"
    },
}

module.exports = {
    index: {
        page: rule.page
    }
}
