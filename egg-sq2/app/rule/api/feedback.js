const rule = {
    page: {
        type: 'number',
        required: true,
        desc: "分页"
    },
    content: {
        type: 'string',
        required: true,
        desc: "内容"
    },
    images: {
        type: 'all',
        required: false,
        desc: "图片"
    },
}

module.exports = {
    save(ctx) {
        const { images } = ctx.request.body
        if (images && !(Array.isArray(images) && images.length)) {
            ctx.throw(422, 'images必须是数组格式')
        }
        return {
            content: rule.content
        }
    },
    index: {
        page: rule.page
    },
}
