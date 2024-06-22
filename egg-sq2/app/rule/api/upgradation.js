const rule = {
    appid: {
        type: 'string',
        required: true,
        desc: "appid"
    },
    appVersion: {
        type: 'string',
        required: true,
        desc: "appVersion"
    },
    wgtVersion: {
        type: 'string',
        required: true,
        desc: "wgtVersion"
    },
    platform: {
        type: 'string',
        required: true,
        desc: "platform",
        range: {
            in: ['android', 'ios']
        }
    },
}

module.exports = {
    index: {
        appid: rule.appid,
        appVersion: rule.appVersion,
        wgtVersion: rule.wgtVersion,
        platform: rule.platform,
    },
}
