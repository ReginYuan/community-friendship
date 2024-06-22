const rule = {
    client_id: {
        type: 'string',
        required: true,
        desc: "客户端ID"
    },
    target_id: {
        type: 'number',
        required: true,
        desc: "对方ID"
    },
    conversation_id: {
        type: 'number',
        required: true,
        desc: "会话ID"
    },
    page: {
        type: 'number',
        required: true,
        desc: "页码"
    },
    type: {
        type: 'string',
        required: true,
        desc: "消息类型",
        range: {
            in: ['text']
        }
    },
    body: {
        type: 'string',
        required: true,
        desc: "消息内容"
    },
    client_create_time: {
        type: 'number',
        required: true,
        desc: "客⼾端创建消息的时间戳"
    },
}

module.exports = {
    bindOnline: {
        client_id: rule.client_id
    },
    createConversation: {
        target_id: rule.target_id
    },
    getMessage: {
        conversation_id: rule.conversation_id,
        page: rule.page
    },
    getConversationList: {
        page: rule.page
    },
    readConversation: {
        conversation_id: rule.conversation_id
    },
    send: {
        target_id: rule.target_id,
        type: rule.type,
        body: rule.body,
        client_create_time: rule.client_create_time
    }
}
