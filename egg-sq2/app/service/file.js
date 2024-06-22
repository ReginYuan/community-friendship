const { Service } = require('egg');
const fs = require('fs').promises;
const path = require('path');

class FileService extends Service {
    async moveFile(filePath, targetDir, newFileName) {
        const targetPath = path.join(targetDir, newFileName);

        // 确保目标目录存在
        await fs.mkdir(targetDir, { recursive: true });

        try {
            // 复制文件
            await fs.copyFile(filePath, targetPath);

            // 删除原始文件
            await fs.unlink(filePath);

            return targetPath;
        } catch (err) {
            throw err;
        }
    }

    // 上传文件
    async uploadFile(dir = "uploads") {
        const { ctx, service } = this;
        if (!ctx.request.files || !ctx.request.files[0]) {
            ctx.throw(400, '请先选择要上传的图片');
        }

        const uploadedFile = ctx.request.files[0];
        // 判断是否是图片
        if (!/^image\//.test(uploadedFile.mime)) {
            ctx.throw(400, '请上传图片');
        }

        // 限制图片大小
        if (uploadedFile.size > 1024 * 1024 * 10) {
            ctx.throw(400, '图片大小不能超过10M');
        }

        // 定义文件保存路径
        const uploadDir = path.join(this.app.baseDir, 'app/public/' + dir);
        const filename = `${Date.now()}${path.extname(uploadedFile.filename)}`;

        try {
            // 使用file服务移动文件
            await service.file.moveFile(uploadedFile.filepath, uploadDir, filename);
            return `/${dir}/${filename}`
        } catch (err) {
            // 抛出异常
            ctx.throw(500, err.message);
        }
    }
}

module.exports = FileService;
