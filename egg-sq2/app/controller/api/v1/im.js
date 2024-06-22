'use strict';

const Controller = require('egg').Controller;
const SCENE = require("../../../rule/api/im")
class ImController extends Controller {
    // 绑定上线
    async bindOnline() {
        const { ctx, app, service } = this;
        ctx.validate(SCENE.bindOnline)
        // 当前登录用户ID
        const user_id = ctx.authUser.id;
        const { client_id } = ctx.request.body;

        // 验证client_id的合法性
        if (!client_id || !(service.im.isOnline(client_id))) {
            ctx.throw(400, "client_id不合法");
        }

        // 验证当前客户端是否已经被绑定
        const binduid = service.im.getUidByClientId(client_id);
        if (binduid && binduid !== user_id) {
            ctx.throw(400, "当前客户端已经被绑定");
        }

        // 绑定上线
        service.im.bindUid(client_id, user_id);

        // 推送未推送的消息记录
        await service.im.pushUnPushMessage(user_id);

        // 推送总未读数
        await service.im.pushTotalUnreadCount(user_id);

        return ctx.apiSuccess("绑定成功");
    }

    // 创建聊天会话
    async createConversation() {
        const { ctx, service } = this;
        ctx.validate(SCENE.createConversation)
        const { target_id } = ctx.request.body
        const user_id = ctx.authUser.id

        // 不能和自己聊天
        if (user_id === target_id) {
            ctx.throw(400, "不能和自己聊天")
        }

        // 创建会话
        const result = await service.im.createConversation(user_id, target_id)

        ctx.apiSuccess("ok", result)
    }

    // 获取某个会话的聊天记录分页列表
    async getMessage() {
        const { ctx, service, app } = this;
        ctx.validate(SCENE.getMessage)
        const { page = 1, conversation_id } = ctx.params;
        // 验证当前会话是否存在
        const user_id = ctx.authUser.id
        const conversation = await app.model.ImConversation.findOne({
            where: {
                id: conversation_id,
                user_id,
            },
            attributes: ["id"]
        });
        if (!conversation) {
            ctx.throw(400, "会话不存在")
        }
        const data = await service.im.getMessage(conversation_id, page)
        return ctx.apiSuccess('ok', data)
    }

    // 获取聊天会话列表
    async getConversationList() {
        const { ctx, service } = this;
        ctx.validate(SCENE.getConversationList)
        const { page = 1 } = ctx.params
        const data = await service.im.getConversationList(page)
        return ctx.apiSuccess("ok", data)
    }

    // 查看会话信息
    async readConversation() {
        const { ctx, service } = this
        ctx.validate(SCENE.readConversation)
        const { conversation_id } = ctx.params
        // 更新未读数
        const data = await service.im.readConversation(conversation_id);

        // 推送总未读数
        await service.im.pushTotalUnreadCount(ctx.authUser.id);

        return ctx.apiSuccess("ok", data);
    }

    // 发送消息
    async send() {
        const { ctx, service } = this;
        ctx.validate(SCENE.send)
        const {
            target_id,
            type,
            body,
        } = ctx.request.body;
        const client_create_time = Math.floor((new Date().getTime() / 1000))
        const user_id = ctx.authUser.id
        // 当前我与对方的会话
        const conversation = await service.im.getConversation(user_id, target_id);

        // 保存信息
        const result = await service.im.saveMessage(conversation, type, body, client_create_time);

        // 更新“我与对方”的会话最后一条消息
        await service.im.updateMyConversationLastMsgNote(conversation, result)

        // 只有发送成功时才推送消息
        if (result.state === 100) {
            // 更新“对方与我”的会话未读数 和 最后一条消息
            await service.im.updateTargetConversationLastMsgNote(target_id, result)

            // 推送消息给对方
            await service.im.sendToUid(target_id, result)

            // 推送总未读数给对方
            await service.im.pushTotalUnreadCount(target_id)
        }

        // 返回数据
        return ctx.apiSuccess('ok', result)
    }
}

module.exports = ImController;
