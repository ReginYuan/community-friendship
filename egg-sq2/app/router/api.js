module.exports = ({ router, controller, ws }) => {
    router.get('/agreement', controller.api.common.agreement);
    router.get('/privacy', controller.api.common.privacy);

    // 广告位列表
    router.get("/api/v1/adsense/:type", controller.api.v1.adsense.index)

    // 话题列表
    router.get("/api/v1/category/:category_id/topic/:page", controller.api.v1.topic.index)
    // 指定分类下帖子列表
    router.get("/api/v1/category/:category_id/article/:page", controller.api.v1.article.index)
    // 指定话题下帖子列表
    router.get("/api/v1/topic/:topic_id/article/:page", controller.api.v1.article.index)
    // 指定用户下帖子列表
    router.get("/api/v1/user/:user_id/article/:page", controller.api.v1.article.index)
    // 搜索帖子
    router.get("/api/v1/search/article/:page", controller.api.v1.article.search)

    // 分类列表
    router.get("/api/v1/category/:type", controller.api.v1.category.index)

    // 发送验证码
    router.post("/api/v1/user/sendcode", controller.api.v1.user.sendCode)
    // 手机验证码登录
    router.post("/api/v1/user/phonelogin", controller.api.v1.user.phoneLogin)
    // 用户密码登录
    router.post("/api/v1/user/login", controller.api.v1.user.login)
    // 退出登录
    router.post("/api/v1/user/logout", controller.api.v1.user.logout)
    // 获取用户详细信息
    router.get("/api/v1/user/info", controller.api.v1.user.info)
    // 修改密码
    router.post("/api/v1/user/changepwd", controller.api.v1.user.changepwd)

    // 发送验证码（用于改绑手机号，修改密码）
    router.post("/api/v1/user/sendcode2", controller.api.v1.user.sendCode2)
    // 忘记密码
    router.post("/api/v1/user/forget", controller.api.v1.user.forget)
    // 绑定手机号
    router.post("/api/v1/user/bindphone", controller.api.v1.user.bindPhone)
    // 修改资料
    router.post("/api/v1/user/changeinfo", controller.api.v1.user.changeInfo)

    // 搜索用户
    router.get("/api/v1/search/user/:page", controller.api.v1.user.search)
    // 获取用户详情
    router.get("/api/v1/user_info/:id", controller.api.v1.user.read)

    // 指定用户的评论列表
    router.get("/api/v1/user/:user_id/comment/:page", controller.api.v1.user.comments)

    // 搜索话题
    router.get("/api/v1/search/topic/:page", controller.api.v1.topic.search)
    // 查看话题
    router.get("/api/v1/topic/:id", controller.api.v1.topic.read)

    // 评论列表
    router.get("/api/v1/article/:article_id/comment/:page", controller.api.v1.comment.index)

    // 帖子详情
    router.get("/api/v1/article/:id", controller.api.v1.article.read)

    // 回复列表
    router.get("/api/v1/comment/:comment_id/replies/:page", controller.api.v1.comment.index)

    // 评论详情
    router.get("/api/v1/comment/:id", controller.api.v1.comment.read)

    // 我的黑名单列表
    router.get("/api/v1/myblacklist/:page", controller.api.v1.blacklist.index)
    // 加入黑名单
    router.post("/api/v1/add_blacklist/:id", controller.api.v1.blacklist.save)
    // 移除黑名单
    router.post("/api/v1/remove_blacklist/:id", controller.api.v1.blacklist.delete)

    // 上传图片
    router.post("/api/v1/upload", controller.api.v1.image.upload)

    // 发布帖子
    router.post("/api/v1/article/save", controller.api.v1.article.save)
    // 删除帖子
    router.post("/api/v1/article/delete/:id", controller.api.v1.article.delete)

    // 发布评论
    router.post("/api/v1/comment/save", controller.api.v1.comment.save)

    // 回复评论
    router.post("/api/v1/comment/reply", controller.api.v1.comment.save)

    // 删除评论
    router.post("/api/v1/comment/delete/:id", controller.api.v1.comment.delete)

    // 修改头像
    router.post("/api/v1/user/changeavatar", controller.api.v1.user.changeAvatar)

    // 收藏
    router.post("/api/v1/add_collection/:article_id", controller.api.v1.collection.save)
    // 取消收藏
    router.post("/api/v1/remove_collection/:article_id", controller.api.v1.collection.delete)
    // 获取收藏列表
    router.get("/api/v1/mycollections/:page", controller.api.v1.collection.index)
    // 获取帖⼦观看记录列表
    router.get("/api/v1/article_read_log/:page", controller.api.v1.articleReadLog.index)

    // 关注
    router.post("/api/v1/add_follow/:id", controller.api.v1.follow.save)
    // 取消关注
    router.post("/api/v1/remove_follow/:id", controller.api.v1.follow.delete)

    // 顶踩帖子
    router.post("/api/v1/support/:type/:article_id", controller.api.v1.support.action)

    // 用户反馈
    router.post("/api/v1/feedback/save", controller.api.v1.feedback.save)
    // 获取用户反馈列表
    router.get("/api/v1/feedback/:page", controller.api.v1.feedback.index)

    // 检查更新
    router.post("/api/v1/upgradation", controller.api.v1.upgradation.index)

    // 举报用户
    router.post("/api/v1/report/save", controller.api.v1.report.save)

    // 指定用户粉丝列表
    router.get("/api/v1/user/:user_id/fans/:page", controller.api.v1.follow.fans)
    // 指定用户关注列表
    router.get("/api/v1/user/:user_id/follows/:page", controller.api.v1.follow.follows)

    // 绑定上线
    router.post("/api/v1/im/bind_online", controller.api.v1.im.bindOnline)
    // 创建聊天会话
    router.post("/api/v1/im/create_conversation", controller.api.v1.im.createConversation)
    // 获取某个会话聊天记录
    router.get("/api/v1/im/:conversation_id/message/:page", controller.api.v1.im.getMessage)
    // 获取会话列表
    router.get("/api/v1/im/conversation/:page", controller.api.v1.im.getConversationList)
    // 查看聊天会话
    router.post("/api/v1/im/read_conversation/:conversation_id", controller.api.v1.im.readConversation)
    // 发送消息
    router.post("/api/v1/im/send", controller.api.v1.im.send)
};
