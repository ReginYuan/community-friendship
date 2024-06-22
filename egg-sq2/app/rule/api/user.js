const rule = {
    phone: {
        type: "phone",
        required: true,
        desc: '手机号'
    },
    code: {
        type: "string",
        required: true,
        desc: '验证码'
    },
    username: {
        type: "string",
        required: true,
        desc: '用户名'
    },
    password: {
        type: "string",
        required: true,
        desc: '密码'
    },
    name: {
        type: "string",
        required: true,
        desc: '昵称'
    },
    sex: {
        type: "number",
        required: true,
        desc: '性别',
        range: {
            in: [0, 1, 2]
        }
    },
    birthday: {
        type: "string",
        required: true,
        desc: '生日'
    },
    qg: {
        type: "number",
        required: true,
        desc: '情感',
        range: {
            in: [0, 1, 2]
        }
    },
    path: {
        type: "string",
        required: true,
        desc: '地址'
    },
    desc: {
        type: "string",
        required: false,
        desc: '个性签名'
    },
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
        desc: '用户id'
    },
}

module.exports = {
    sendCode: {
        phone: rule.phone
    },
    phoneLogin: {
        phone: rule.phone,
        code: rule.code
    },
    login: {
        username: rule.username,
        password: rule.password
    },
    changepwd: {
        code: rule.code,
        password: {
            ...rule.password,
            range: {
                min: 6,
                max: 20
            }
        }
    },
    forget: {
        phone: rule.phone,
        code: rule.code,
        password: {
            ...rule.password,
            range: {
                min: 6,
                max: 20
            }
        }
    },
    bindPhone: {
        phone: rule.phone,
        code: rule.code
    },
    changeInfo: {
        name: rule.name,
        sex: rule.sex,
        birthday: rule.birthday,
        qg: rule.qg,
        path: rule.path,
        desc: rule.desc
    },
    search: {
        page: rule.page,
        keyword: rule.keyword
    },
    read: {
        id: rule.id
    },
    comments: {
        page: rule.page,
        user_id: rule.id
    }
}
