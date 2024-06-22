const rule = {
    type: {
        type: "string",
        required: true,
        desc: '分类类型',
        range: {
            in: ["topic", "article"]
        }
    }
}

module.exports = {
    index: {
        type: rule.type
    }
}
