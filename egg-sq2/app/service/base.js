'use strict';

const Service = require('egg').Service;

class BaseService extends Service {
    async paginate(model, options = {}) {
        const { page = 1, limit = 10, ...searchOptions } = options;
        const offset = (page - 1) * limit;

        const result = await model.findAndCountAll({
            offset,
            limit,
            ...searchOptions,
        });

        const { count, rows } = result;
        const last_page = Math.ceil(count / limit);

        return {
            current_page: page,
            per_page: limit,
            total: count,
            last_page,
            data: this.app.toArray(rows),
        };
    }

    // 获取今天的时间戳范围
    getTodayRange() {
        // 获取今日0点的时间戳
        const todayStart = new Date();
        todayStart.setHours(0, 0, 0, 0);
        const today = Math.round(todayStart.getTime() / 1000);

        // 获取明日0点的时间戳
        const tomorrowStart = new Date(todayStart);
        tomorrowStart.setDate(todayStart.getDate() + 1);
        tomorrowStart.setHours(0, 0, 0, 0);
        const tomorrow = Math.round(tomorrowStart.getTime() / 1000);

        return {
            today,
            tomorrow
        }
    }

    // 关联统计今日
    async getTodayCount(model, key, value = []) {
        const Sequelize = this.app.Sequelize;
        const { today, tomorrow } = this.getTodayRange();

        // 查询今日发帖数
        const todayCounts = await model.findAll({
            attributes: [
                key,
                [Sequelize.fn('COUNT', Sequelize.col('id')), 'todayCount']
            ],
            where: {
                topic_id: {
                    [Sequelize.Op.in]: value,
                },
                create_time: {
                    [Sequelize.Op.between]: [today, tomorrow],
                },
            },
            group: [key],
            raw: true, // 返回原始数据
        });

        if (value.length === 1) {
            return todayCounts[0] ? todayCounts[0].todayCount : 0;
        }

        return todayCounts;
    }
}

module.exports = BaseService;
