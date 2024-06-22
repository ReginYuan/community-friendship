module.exports = {
    // 成功提示
    apiSuccess(msg = "ok", data = []) {
        this.body = {
            code: 1,
            msg,
            data
        };
        this.status = 200;
    },
    // 失败提示
    apiFail(msg = "未知", code = 400) {
        this.body = {
            code: 0,
            msg,
            data: null
        };
        this.status = code;
    },

    // 获取完整文件地址
    getUploadPath(path = '') {
        if (!path) {
            return path;
        }
        path = path.replace("\\", "/");
        const { protocol, host } = this.request;
        const baseURL = protocol + "://" + host
        if (path.indexOf("http") !== -1) {
            // 将本地测试的地址替换成线上地址
            return path.replace("http://127.0.0.1:8000", baseURL);
        }
        return baseURL + '/public' + path;
    }
};
