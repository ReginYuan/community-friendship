module.exports = config => {
    config.aliSMS = {
        isopen: false, // 是否开启短信发送
        expire: 60, // 短信验证码有效期
        accessKeyId: 'LTAI5d1a1dS2E3hmCR',
        accessSecret: 'wOnijFCdt2231uEHzPi0Arj8io6DmZN',
        regionId: 'cn-hangzhou',
        endpoint: "https://dysmsapi.aliyuncs.com",
        // product: '',
        version: '2017-05-25',
        SignName: '短信签名',
        TemplateCode: 'SMS_162438417'
    }
}