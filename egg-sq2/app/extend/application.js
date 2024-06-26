const crypto = require('crypto');
const { v4: uuidv4 } = require('uuid');
module.exports = {
    // 是否是手机号
    isPhoneNumber(phone) {
        return /^1[3456789]\d{9}$/.test(phone);
    },
    // 是否是邮箱格式
    isEmail(email) {
        return /^[a-zA-Z0-9_.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z0-9]{2,6}$/.test(email);
    },
    formatTime(time) {
        if (!time) {
            return ''
        }
        const d = new Date(time * 1000);
        const Month = (d.getMonth() + 1) >= 10 ? (d.getMonth() + 1) : '0' + (d.getMonth() + 1)
        const Day = d.getDate() >= 10 ? d.getDate() : '0' + d.getDate()
        const h = d.getHours() >= 10 ? d.getHours() : '0' + d.getHours()
        const m = d.getMinutes() >= 10 ? d.getMinutes() : '0' + d.getMinutes()
        const s = d.getSeconds() >= 10 ? d.getSeconds() : '0' + d.getSeconds()
        return d.getFullYear() + '-' + Month + '-' + Day + ' ' + h + ':' + m + ':' + s;
    },
    // 加密
    createPassword(password) {
        const hmac = crypto.createHash("sha256", this.config.crypto.secret);
        hmac.update(password);
        return hmac.digest("hex");
    },
    // 验证密码
    checkPassword(password, hash_password) {
        // 先对需要验证的密码进行加密
        password = this.createPassword(password);
        return password === hash_password;
    },
    // 生成token
    createToken(value) {
        return this.jwt.sign(value, this.config.jwt.secret);
    },
    // 验证token
    checkToken(value) {
        return this.jwt.verify(value, this.config.jwt.secret);
    },
    treeArray(arr, id = 0) {
        const res = [];
        for (let i = 0; i < arr.length; i++) {
            if (arr[i].access_id === id) {
                if (arr[i].role_access) {
                    delete arr[i].role_access
                }
                res.push(arr[i]);
                arr[i].children = this.treeArray(arr, arr[i].id);
            }
        }
        return res;
    },
    toArray(d) {
        return JSON.parse(JSON.stringify(d))
    },
    // 删除数组对象中重复项
    uniqueArray(arr, key = 'id') {
        const hash = {};
        return arr.reduce((item, next) => {
            hash[next[key]] ? '' : hash[next[key]] = true && item.push(next);
            return item
        }, []);
    },
    // 隐藏核心手机号
    maskPhone(val) {
        if (val === '' || !val) {
            return val
        }
        return val.replace(/^(\d{3})\d{4}(\d{4})$/, '$1****$2')
    },
    maskEmail(email) {
        if (email === '' || !email) {
            return email;
        }
        if (String(email).indexOf('@') > 0) {
            const str = email.split('@')
            let _s = '';
            if (str[0].length > 3) {
                for (let i = 0; i < str[0].length - 3; i++) {
                    _s += '*';
                }
            }
            email = str[0].substr(0, 3) + _s + '@' + str[1]
        }
        return email
    },
    // 秒 转 时:分:秒
    secondToDate(result) {
        const h = Math.floor(result / 3600) < 10 ? '0' + Math.floor(result / 3600) : Math.floor(result / 3600);
        const m = Math.floor((result / 60 % 60)) < 10 ? '0' + Math.floor((result / 60 % 60)) : Math.floor((result / 60 % 60));
        const s = Math.floor((result % 60)) < 10 ? '0' + Math.floor((result % 60)) : Math.floor((result % 60));
        return h + ":" + m + ":" + s;
    },
    // 校验 时间日期格式
    strDateTime(str) {
        const reg = /^(\d{4})(-|\/)(\d{2})\2(\d{2}) (\d{2}):(\d{2}):(\d{2})$/;
        const r = str.match(reg);
        if (r == null) return false;
        const d = new Date(r[1], r[3] - 1, r[4], r[5], r[6], r[7]);
        return (d.getFullYear() === r[1] && (d.getMonth() + 1) === r[3] && d.getDate() === r[4] && d.getHours() === r[5] && d.getMinutes() === r[6] && d.getSeconds() === r[7]);
    },
    // 生成唯一随机字符串
    generateUniqueUUID() {
        return uuidv4();
    }
}
