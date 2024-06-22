const rule = {
    report_uid: {
        type: 'number',
        required: true,
        desc: "举报对象ID"
    },
    content: {
        type: 'string',
        required: true,
        desc: "内容"
    },
}

module.exports = {
    save: {
        report_uid: rule.report_uid,
        content: rule.content
    },
}
