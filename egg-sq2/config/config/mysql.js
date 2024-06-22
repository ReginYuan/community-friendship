module.exports = config => {
    // 数据库
    config.sequelize = {
        dialect: "mysql",
        host: "127.0.0.1",
        username: "root",
        password: "abc123456",
        port: 3306,
        database: "sqjy2",
        // 中国时区
        timezone: "+08:00",
        define: {
            // 取消数据表名复数
            freezeTableName: true,
            // 自动写入时间戳 created_at updated_at
            timestamps: false,
            // 字段生成软删除时间戳 deleted_at
            // paranoid: true,
            createdAt: "create_time",
            updatedAt: "update_time",
            // deletedAt: 'deleted_time',
            // 所有驼峰命名格式化
            underscored: true,
        },
    };
}
