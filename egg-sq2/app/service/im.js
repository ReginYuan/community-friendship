'use strict';

const Service = require('./base');
const WebSocket = require('ws');
class ImService extends Service {
    // 绑定用户ID到clientId
    bindUid(clientId, uid) {
        const { app } = this;
        if (!app.clients.has(clientId)) {
            return; // clientId 不存在
        }
        if (!app.userClientIds.has(uid)) {
            app.userClientIds.set(uid, new Set()); // 初始化用户ID对应的clientId集合
        }
        app.userClientIds.get(uid).add(clientId); // 将clientId添加到用户ID对应的集合中
    }

    // 根据用户ID发送消息
    sendToUid(uid, message) {
        const { app } = this;
        if (!app.userClientIds.has(uid)) {
            return; // 用户ID不存在
        }
        const clientIds = app.userClientIds.get(uid);
        for (const clientId of clientIds) {
            if (app.clients.has(clientId)) {
                const ws = app.clients.get(clientId);
                if (ws.readyState === WebSocket.OPEN) {
                    if (typeof message !== "string") {
                        message = JSON.stringify(message);
                    }
                    ws.send(message);
                } else {
                    // 如果连接不是OPEN状态，可以选择移除该clientId或者做其他处理
                    app.userClientIds.get(uid).delete(clientId);
                }
            }
        }
    }

    // 判断客户端ID是否在线
    isOnline(clientId) {
        const { app } = this
        const ws = app.clients.get(clientId);
        return ws && ws.readyState === WebSocket.OPEN;
    }

    // 判断用户ID是否在线
    isUidOnline(uid) {
        const { app } = this
        if (!app.userClientIds.has(uid)) {
            return false; // 用户ID不存在
        }
        const clientIds = app.userClientIds.get(uid);
        for (const clientId of clientIds) {
            if (app.clients.has(clientId) && app.clients.get(clientId).readyState === WebSocket.OPEN) {
                return true; // 至少有一个客户端在线
            }
        }
        return false; // 所有客户端都不在线
    }

    // 发送消息给所有在线客户端
    sendToAll(message) {
        const { app } = this;
        app.clients.forEach((ws) => {
            if (ws.readyState === WebSocket.OPEN) {
                if (typeof message !== "string") {
                    message = JSON.stringify(message);
                }
                ws.send(message);
            }
        });
    }

    // 发送消息给指定client_id的客户端  
    sendToClient(client_id, message) {
        const { app } = this;
        const ws = app.clients.get(client_id);
        if (ws && ws.readyState === WebSocket.OPEN) {
            if (typeof message !== "string") {
                message = JSON.stringify(message);
            }
            ws.send(message);
        }
    }

    // 通过client_id获取对应的uid
    getUidByClientId(client_id) {
        const { app } = this;
        // 遍历app.userClientIds Map中的所有条目
        for (const [uid, clientIds] of app.userClientIds.entries()) {
            // 检查client_id是否在对应的clientIds集合中
            if (clientIds.has(client_id)) {
                // 如果找到，返回对应的uid
                return uid;
            }
        }
        // 如果没有找到，返回null或undefined
        return null;
    }

    // 根据用户ID获取客户端ID列表
    getClientIdByUid(uid) {
        const { app } = this;
        return app.userClientIds.get(uid) || [];
    }

    // 推送未推送的消息记录
    async pushUnPushMessage(user_id) {
        const { app } = this;
        // 获取未推送的消息记录
        const msg_list = await app.model.ImMessage.getUnPushMessages(user_id)

        if (msg_list.length > 0 && this.isUidOnline(user_id)) {
            // 记录推送成功的消息ID
            const update_list = []
            msg_list.forEach(msg => {
                this.sendToUid(user_id, {
                    type: "message",
                    data: msg
                })
                update_list.push(msg.id)
            })
            // 更新数据库里的is_push为1
            if (update_list.length > 0) {
                await app.model.ImMessage.update({
                    is_push: 1
                }, {
                    where: {
                        id: update_list
                    }
                })
            }
        }
    }

    //  统计总未读数，并推送
    async pushTotalUnreadCount(user_id) {
        const { ctx, app } = this;
        // 统计当前用户总未读数
        const TotalUnreadCount = await app.model.ImConversation.sum('unread_count', {
            where: {
                user_id
            }
        })

        console.log({
            type: 'total_unread_count',
            data: TotalUnreadCount ?? 0
        })

        // 推送
        this.sendToUid(user_id, {
            type: 'total_unread_count',
            data: TotalUnreadCount ?? 0
        })
    }

    // 创建聊天会话
    async createConversation(user_id, target_id) {
        const { ctx, app, service } = this
        const data = {
            user_id,
            target_id
        }

        // 查询会话是否存在
        let conversation = await app.model.ImConversation.findOne({
            where: data,
        })

        // 存在，则聊天会话置顶（更新最后一次聊天时间）
        if (conversation) {
            conversation.update_time = Math.floor((new Date().getTime() / 1000))
            await conversation.save();
            return await this.getConversationById(conversation.id)
        }

        // 查询对方是否存在
        const target = await service.user.isUserExist('id', target_id)
        if (!target) {
            ctx.throw(400, '对方不存在')
        }

        // 创建聊天会话
        data.unread_count = 0
        data.last_msg_note = "打个招呼吧~"
        data.create_time = Math.floor((new Date().getTime() / 1000))
        data.update_time = Math.floor((new Date().getTime() / 1000))
        conversation = await app.model.ImConversation.create(data)
        return await this.getConversationById(conversation.id)
    }

    // 获取会话信息
    async getConversationById(id, user_id = 0) {
        const { app } = this
        const where = {
            id
        }
        if (user_id) {
            where.user_id = user_id
        }
        let conversation = await app.model.ImConversation.findOne({
            where,
            include: [{
                model: app.model.User,
                as: "targetuser",
                attributes: ["id", "username", "avatar", "phone", "email"]
            }],
            attributes: {
                exclude: ["user_id", "create_time"]
            }
        })

        if (conversation) {
            conversation = app.toArray(conversation)
            conversation.name = app.model.User.getName(conversation.targetuser)
            conversation.avatar = conversation.targetuser ? conversation.targetuser.avatar : null
            delete conversation.targetuser
        }

        return conversation
    }

    // 获取某个会话的聊天记录
    async getMessage(conversation_id, page) {
        const { app } = this;
        const cm = await this.paginate(app.model.ConversationMessage, {
            page,
            where: {
                conversation_id
            },
            attributes: ["message_id"],
            order: [
                ["create_time", "DESC"]
            ]
        })
        const message_ids = cm.data.map(o => o.message_id)
        const result = await this.paginate(app.model.ImMessage, {
            where: {
                id: message_ids,
                state: 100
            },
            include: [{
                model: app.model.User,
                attributes: ["username", "avatar", "phone", "email"]
            }],
            order: [
                ['id', 'desc']
            ]
        })
        result.data = result.data.map(o => {
            o.name = o.user ? o.user.name : "未知"
            o.avatar = o.user ? o.user.avatar : null
            o.conversation_id = conversation_id
            o.state_text = app.model.ImMessage.getStateText(o.state)
            delete o.user
            return o
        })
        return result
    }

    // 获取当前用户聊天会话分页列表
    async getConversationList(page) {
        const { service, app } = this
        // 获取当前用户ID
        const user_id = await service.user.getCurrentUserIdByToken()
        const result = await this.paginate(app.model.ImConversation, {
            page,
            where: {
                user_id
            },
            include: [
                {
                    model: app.model.User,
                    as: 'targetuser',
                    attributes: ['username', 'avatar', 'phone', 'email']
                }
            ],
            order: [
                ['update_time', 'DESC']
            ],
            attributes: {
                exclude: ['user_id', "create_time"]
            }
        })

        result.data = result.data.map(o => {
            o.name = o.targetuser ? o.targetuser.name : "未知"
            o.avatar = o.targetuser ? o.targetuser.avatar : null
            delete o.targetuser
            return o
        })

        return result
    }

    // 查看会话消息
    async readConversation(conversation_id) {
        const { ctx } = this
        // 获取当前登录用户ID
        const user_id = ctx.authUser.id
        // 查找会话
        const conversation = await this.app.model.ImConversation.findOne({
            where: {
                id: conversation_id,
                user_id
            }
        })
        if (!conversation) {
            ctx.throw(404, '会话不存在')
        }
        // 未读数设为0
        if (conversation.unread_count > 0) {
            conversation.unread_count = 0
            await conversation.save()
        }

        return await this.getConversationById(conversation_id)
    }

    // 获取会话
    async getConversation(user_id, target_id) {
        const { app } = this
        const data = {
            user_id,
            target_id
        };
        let conversation = await app.model.ImConversation.findOne({
            where: data
        })

        // 不存在则直接创建
        if (!conversation) {
            conversation = await app.model.ImConversation.create(data)
        }

        return conversation
    }

    // 保存消息
    async saveMessage(conversation, type, body, client_create_time) {
        const { ctx, app, service } = this;
        let state = 100

        const target_id = conversation.target_id
        const user_id = conversation.user_id

        // 对方把你拉黑了
        if (await service.blacklist.isBlackedByTarget(target_id, user_id)) {
            state = 101
        }

        // 你把对方拉黑了
        if (await service.blacklist.isBlackedByTarget(user_id, target_id)) {
            state = 102
        }

        // 对方已被系统封禁
        const target = await app.model.User.findOne({
            where: {
                id: target_id
            },
            attributes: ['status']
        })
        if (!target || target.getDataValue('status') === 0) {
            state = 103
        }

        const data = {
            // 发布人
            user_id,
            // 接收人
            target_id,
            // 消息类型
            type,
            // 消息内容
            body,
            // 客户端发送时间
            client_create_time,
            // 是否撤回
            is_revoke: 0,
            // 状态 消息状态 100发送成功，101对⽅已把你拉⿊，102你把对⽅拉⿊了， 103对⽅已被系统封禁，104 禁⽌发送（内容不合法）
            state,
            // 是否推送
            is_push: 0
        }

        // 创建消息
        let result = await app.model.ImMessage.create(data);

        // 创建消息失败
        if (!result) {
            ctx.throw(500, '创建消息失败');
        }

        result = app.toArray(result)
        const user = ctx.authUser
        result.name = app.model.User.getName(user)
        result.avatar = user.avatar
        return result
    }

    // 更新“我与对方”的会话最后一条消息
    async updateMyConversationLastMsgNote(conversation, message) {
        const { ctx, app } = this;
        conversation.last_msg_note = message.state === 100 ? message.body : app.model.ImMessage.getStateText(message.state);
        const result = await conversation.save();
        if (!result) {
            ctx.throw(500, '更新“我与对方”的会话最后一条消息失败');
        }
        // 创建关联
        await app.model.ConversationMessage.create({
            conversation_id: conversation.id,
            message_id: message.id,
            user_id: conversation.user_id
        });
        // 推送消息
        this.sendToUid(conversation.user_id, {
            type: 'conversation',
            data: await this.getConversationById(conversation.id)
        });

        return result
    }

    // 更新“对方与我”的会话未读数 和 最后一条消息
    async updateTargetConversationLastMsgNote(target_id, message) {
        const { ctx, app } = this;
        // 获取当前登录用户ID
        const user_id = ctx.authUser.id
        // 获取对方与我的会话
        const conversation = await this.getConversation(target_id, user_id)
        // 未读数+1
        conversation.unread_count += 1
        conversation.last_msg_note = message.body
        const result = await conversation.save()
        if (!result) {
            ctx.throw(500, '更新“对方与我”的会话未读数 和 最后一条消息失败')
        }
        // 创建关联
        await app.model.ConversationMessage.create({
            conversation_id: conversation.id,
            message_id: message.id,
            user_id: conversation.user_id
        });
        // 推送消息
        this.sendToUid(conversation.user_id, {
            type: 'conversation',
            data: await this.getConversationById(conversation.id)
        });

        return result
    }
}

module.exports = ImService;
