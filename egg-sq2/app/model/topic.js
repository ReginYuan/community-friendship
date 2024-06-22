/* indent size: 2 */

module.exports = app => {
  const DataTypes = app.Sequelize;

  const Model = app.model.define('topic', {
    id: {
      type: DataTypes.INTEGER(11),
      allowNull: false,
      primaryKey: true,
      autoIncrement: true
    },
    title: {
      type: DataTypes.STRING(80),
      allowNull: false
    },
    cover: {
      type: DataTypes.STRING(255),
      allowNull: false
    },
    desc: {
      type: DataTypes.STRING(255),
      allowNull: false
    },
    create_time: {
      type: DataTypes.INTEGER(10),
      allowNull: true,
      get() {
        return app.formatTime(this.getDataValue('create_time'))
      }
    },
    update_time: {
      type: DataTypes.INTEGER(10),
      allowNull: true,
      get() {
        return app.formatTime(this.getDataValue('update_time'))
      }
    },
    category_id: {
      type: DataTypes.INTEGER(11),
      allowNull: true
    },
    article_count: {
      type: DataTypes.INTEGER(11),
      allowNull: true,
      defaultValue: '0'
    }
  }, {
    tableName: 'topic'
  });

  // 统计和更新话题帖子数
  Model.updateArticlesCount = async function (topic_id) {
    if (!topic_id) {
      return
    }
    // 统计话题帖子数
    const count = await app.model.Article.count({
      where: {
        topic_id
      }
    })
    // 更新话题帖子数
    await app.model.Topic.update({
      article_count: count
    }, {
      where: {
        id: topic_id
      }
    })
  }

  Model.associate = function () {
    // 关联帖子
    Model.hasMany(app.model.Article, {
      foreignKey: 'topic_id',
      targetKey: 'id',
      as: 'articles'
    })

    // 关联分类
    Model.belongsTo(app.model.Category, {
      foreignKey: 'category_id',
      targetKey: 'id',
      as: 'category'
    })
  }

  // 新增前
  Model.beforeCreate((model, option) => {
    // 自动写入时间戳
    const time = Math.floor((new Date().getTime() / 1000))
    model.create_time = time
    model.update_time = time
  });
  // 修改前
  Model.beforeUpdate((model, option) => {
    // 自动写入时间戳
    model.update_time = Math.floor((new Date().getTime() / 1000))
  });

  return Model;
};
