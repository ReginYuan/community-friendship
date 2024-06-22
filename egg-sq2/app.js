const WebSocket = require('ws');
const { v4: uuidv4 } = require('uuid');
class AppBootHook {
    constructor(app) {
        this.app = app;
    }

    configWillLoad() {
        // 此时 config 文件已经被读取并合并，但还并未生效
        // 这是应用层修改配置的最后机会
        // 注意：此函数只支持同步调用
    }

    async didLoad() {
        // 所有配置已经加载完毕
        // 可以用来加载应用自定义的文件，启动自定义服务
        const { port } = this.app.config.websocket
        const wss = new WebSocket.Server({ port });
        const blue = "\x1b[34m";
        const reset = "\x1b[0m";
        console.log(`${blue}[websocket]${reset} websocket server start at port:ws://127.0.0.1:${port}`)
        this.app.wss = wss
        // 用于存储客户端 ID 和对应的 WebSocket 连接
        this.app.clients = new Map()
        // 用于存储用户 ID 和对应的 clientId 列表
        this.app.userClientIds = new Map();
    }

    async willReady() {
        // 所有插件已启动完毕，但应用整体尚未 ready
        // 可进行数据初始化等操作，这些操作成功后才启动应用

        // 例如：从数据库加载数据到内存缓存
    }

    async didReady() {
        // 应用已启动完毕
    }

    async serverDidReady() {
        // http/https 服务器已启动，开始接收外部请求

        this.app.wss.on('connection', (ws) => {
            // 为客户端生成唯一 ID
            const clientId = uuidv4();
            // 将客户端 ID 和 WebSocket 连接存储在 Map 中
            this.app.clients.set(clientId, ws);

            // 也可以在 WebSocket 连接对象上直接设置 clientId 属性，方便后续使用
            ws.clientId = clientId;

            // 发送客户端 ID
            ws.send(JSON.stringify({ type: 'bind', data: clientId }));

            ws.on('message', (message) => {
                console.log(`Client ${clientId} received: ${message}`);
                // 处理消息...
            });

            ws.on('close', () => {
                this.app.logger.error(`ws.onClose：断开连接 ${clientId}`)
                // 客户端断开连接时，从 Map 中移除对应的 clientId
                this.app.clients.delete(clientId);
                // 同时从用户ID对应的clientId集合中移除clientId
                for (const [uid, clientIds] of this.app.userClientIds.entries()) {
                    if (clientIds.has(clientId)) {
                        clientIds.delete(clientId);
                        // 如果用户没有绑定的clientId了，则删除用户ID对应的记录
                        if (clientIds.size === 0) {
                            this.app.userClientIds.delete(uid);
                        }
                    }
                }
            });

            ws.on('error', (error) => {
                console.error(`Client ${clientId} error:`, error);
                this.app.logger.error(`ws.onError：Client ${clientId} error:${error}`)
                // 处理错误...
            });
        });
    }
}

module.exports = AppBootHook;
