const rule = {
    topic_id: {
        type: 'number',
        required: false,
        desc: "话题ID"
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
    keyword: {
        type: 'string',
        required: true,
        desc: "关键字"
    },
    id: {
        type: 'number',
        required: true,
        desc: "ID"
    },
    category_id: {
        type: 'number',
        required: true,
        desc: "分类ID"
    },
    order: {
        type: 'string',
        required: true,
        desc: "排序",
        range: {
            in: ['new', 'hot']
        }
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
    search: {
        keyword: rule.keyword,
        page: rule.page
    },
    read: {
        id: rule.id
    },
    delete: {
        id: rule.id
    },
    index(ctx) {
        if (ctx.url.includes('topic')) {
            return {
                topic_id: rule.topic_id,
                page: rule.page,
                order: rule.order
            }
        } else if (ctx.url.includes("user")) {
            return {
                user_id: rule.user_id,
                page: rule.page
            }
        }
        return {
            category_id: rule.category_id,
            page: rule.page
        }
    },
    save(ctx) {
        const { images } = ctx.request.body
        if (images && !(Array.isArray(images) && images.length)) {
            ctx.throw(422, 'images必须是数组格式')
        }
        return {
            category_id: rule.category_id,
            topic_id: rule.topic_id,
            content: rule.content
        }
    }
}
