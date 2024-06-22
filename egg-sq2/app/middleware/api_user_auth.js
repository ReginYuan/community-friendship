module.exports = (option, app) => {
    return async (ctx, next) => {
        // 获取 header 头token
        const { token } = ctx.header;
        if (!token) {
            ctx.throw(400, '登录已失效，请重新登录');
        }
        // 根据token解密，换取用户信息
        let user = {};
        try {
            user = app.checkToken(token);
        } catch (error) {
            // const fail = error.name === 'TokenExpiredError' ? 'token 已过期! 请重新获取令牌' : 'Token 令牌不合法!';
            const fail = '登录已失效，请重新登录'
            ctx.throw(400, fail);
        }

        // 登录失效
        const t = await ctx.service.cache.get('login_' + user.id);
        if (!t || t !== token) {
            ctx.throw(400, '登录已失效，请重新登录');
        }

        // 获取当前用户，验证当前用户是否存在
        user = await ctx.service.user.isUserExist("id", user.id)
        if (!user) {
            ctx.throw(400, '用户不存在');
        }

        // 把 user 信息挂载到全局ctx上
        ctx.authUser = user;

        await next();
    }
}
