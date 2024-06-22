/* indent size: 2 */

module.exports = app => {
  const DataTypes = app.Sequelize;

  const Model = app.model.define('article', {
    id: {
      type: DataTypes.INTEGER(11),
      allowNull: false,
      primaryKey: true,
      autoIncrement: true
    },
    user_id: {
      type: DataTypes.INTEGER(20).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    title: {
      type: DataTypes.STRING(255),
      allowNull: false,
      defaultValue: '”“',
    },
    content: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    images: {
      type: DataTypes.TEXT,
      allowNull: true,
      get() {
        let images = this.getDataValue('images');
        if (!images) {
          images = []
        } else {
          images = images.split(",")
        }
        let cover = ""
        if (images[0]) {
          cover = images[0]
        }
        this.setDataValue('cover', cover)
        return images
      },
      set(val) {
        this.setDataValue('images', val.join(","))
      }
    },
    url: {
      type: DataTypes.STRING(50),
      allowNull: false,
      defaultValue: ''
    },
    category_id: {
      type: DataTypes.INTEGER(11),
      allowNull: true
    },
    topic_id: {
      type: DataTypes.INTEGER(11).UNSIGNED,
      allowNull: true,
      defaultValue: '0'
    },
    share_count: {
      type: DataTypes.INTEGER(10).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    ding_count: {
      type: DataTypes.INTEGER(10).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    cai_count: {
      type: DataTypes.INTEGER(10).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    comment_count: {
      type: DataTypes.INTEGER(10).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    read_count: {
      type: DataTypes.INTEGER(10).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    collect_count: {
      type: DataTypes.INTEGER(10).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
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
  }, {
    tableName: 'article'
  });

  // 统计和更新帖子评论数（不包括回复）
  Model.updateCommentCount = async function (article_id) {
    if (!article_id) {
      return
    }
    const count = await app.model.Comment.count({
      where: {
        article_id,
        comment_id: null
      }
    })

    const article = await app.model.Article.findByPk(article_id)
    if (article) {
      article.comment_count = count
      await article.save()
    }
  }

  // 统计和更新顶踩数
  Model.updateSupportCount = async function (article_id) {
    if (!article_id) {
      return
    }

    // 统计顶数
    const ding_count = await app.model.Support.count({
      where: {
        article_id,
        type: 1
      }
    })

    // 统计踩数
    const cai_count = await app.model.Support.count({
      where: {
        article_id,
        type: 0
      }
    })

    const article = await app.model.Article.findByPk(article_id)
    if (article) {
      article.ding_count = ding_count
      article.cai_count = cai_count
      await article.save()
    }
  }


  // 是否关注用户
  Model.addScope("isfollow", function (user_id) {
    return {
      include: [
        {
          model: app.model.Follow,
          attributes: ["id"],
          where: {
            user_id
          },
          required: false
        }
      ]
    }
  })

  // 判断是否顶踩了该帖子
  Model.addScope("isSupport", function (user_id) {
    return {
      include: [
        {
          model: app.model.Support,
          attributes: ["id", "type"],
          where: {
            user_id
          },
          required: false
        }
      ]
    }
  })

  Model.associate = function () {
    // 关联用户
    Model.belongsTo(app.model.User, {
      foreignKey: 'user_id',
      targetKey: 'id'
    })

    // 关联话题
    Model.belongsTo(app.model.Topic, {
      foreignKey: 'topic_id',
      targetKey: 'id'
    })

    // 关联是否关注该作者
    Model.belongsTo(app.model.Follow, {
      foreignKey: 'user_id',
      targetKey: 'follow_id'
    })

    // 关联是否顶踩
    Model.hasOne(app.model.Support, {
      foreignKey: 'article_id',
      targetKey: 'id'
    })
  }

  // 新增前
  Model.beforeCreate((model, option) => {
    // 自动写入时间戳
    const time = Math.floor((new Date().getTime() / 1000))
    model.create_time = time
    model.update_time = time
  });
  // 新增后
  Model.afterCreate((model, option) => {
    // 更新用户帖子数
    app.model.User.updateArticlesCount(model.user_id)
    // 更新话题帖子数
    app.model.Topic.updateArticlesCount(model.topic_id)
  })
  // 修改前
  Model.beforeUpdate((model, option) => {
    // 自动写入时间戳
    model.update_time = Math.floor((new Date().getTime() / 1000))
  });

  // 删除之后
  Model.afterDestroy((model, option) => {
    // 更新用户帖子数
    app.model.User.updateArticlesCount(model.user_id)
    // 更新话题帖子数
    app.model.Topic.updateArticlesCount(model.topic_id)
    // 删除观看记录
    app.model.ArticleReadLog.destroy({
      where: {
        article_id: model.id
      }
    })
    // 删除收藏记录
    app.model.Collection.destroy({
      where: {
        article_id: model.id
      }
    })
    // 删除顶踩记录
    app.model.Support.destroy({
      where: {
        article_id: model.id
      }
    })
    // 删除评论记录
    app.model.Comment.destroy({
      where: {
        article_id: model.id
      }
    })
  })

  return Model;
};
