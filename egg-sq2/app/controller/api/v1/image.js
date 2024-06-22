'use strict';

const Controller = require('egg').Controller;

class ImageController extends Controller {
  async upload() {
    const { ctx, service } = this;

    const filepath = await service.file.uploadFile()

    // 构造文件访问URL
    const url = ctx.getUploadPath(filepath);

    // 发送成功响应
    ctx.apiSuccess('ok', url);
  }
}

module.exports = ImageController;
