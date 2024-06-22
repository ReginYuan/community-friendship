module.exports = config => {
    // 中间件
    config.middleware = [
        "errorHandler",
        "apiUserAuth"
    ];

    // 验证是否登录
    // 需要登录才能操作的路由
    const needLoginRoutes = [
        '/api/v1/user/info,GET',
        '/api/v1/user/changepwd,POST',
        '/api/v1/user/sendcode2,POST',
        '/api/v1/user/bindphone,POST',
        '/api/v1/user/changeinfo,POST',
        '/api/v1/upload,POST',
        '/api/v1/article/save,POST',
        '/api/v1/comment/save,POST',
        '/api/v1/comment/reply,POST',
        '/api/v1/user/changeavatar,POST',
        '/api/v1/feedback/save,POST',
        '/api/v1/report/save,POST',
        '/api/v1/im/bind_online,POST',
        '/api/v1/im/create_conversation,POST',
        '/api/v1/im/send,POST',
        { pattern: '^/api/v1/im/\\d+/message/\\d+$', method: 'GET' },
        { pattern: '^/api/v1/im/conversation/\\d+$', method: 'GET' },
        { pattern: '^/api/v1/im/read_conversation/\\d+$', method: 'POST' },
        { pattern: '^/api/v1/myblacklist/\\d+$', method: 'GET' },
        { pattern: '^/api/v1/feedback/\\d+$', method: 'GET' },
        { pattern: '^/api/v1/add_blacklist/\\d+$', method: 'POST' },
        { pattern: '^/api/v1/remove_blacklist/\\d+$', method: 'POST' },
        { pattern: '^/api/v1/mycollections/\\d+$', method: 'GET' },
        { pattern: '^/api/v1/add_collection/\\d+$', method: 'POST' },
        { pattern: '^/api/v1/remove_collection/\\d+$', method: 'POST' },
        { pattern: '^/api/v1/article/delete/\\d+$', method: 'POST' },
        { pattern: '^/api/v1/comment/delete/\\d+$', method: 'POST' },
        { pattern: '^/api/v1/article_read_log/\\d+$', method: 'GET' },
        { pattern: '^/api/v1/add_follow/\\d+$', method: 'POST' },
        { pattern: '^/api/v1/remove_follow/\\d+$', method: 'POST' },
        { pattern: '^/api/v1/support/(ding|cai)/\\d+$', method: 'POST' },
    ];

    config.apiUserAuth = {
        match(ctx) {
            // 获取当前路由
            const rule = `${ctx.url},${ctx.method}`;

            // 尝试直接匹配字符串
            if (needLoginRoutes.some(item => typeof item === 'string' && item === rule)) {
                return true;
            }

            // 尝试匹配正则表达式
            return needLoginRoutes.some(item => {
                if (typeof item === 'object' && item.method === ctx.method) {
                    const regex = new RegExp(item.pattern);
                    return regex.test(ctx.url);
                }
                return false;
            });
        },
    };
}
