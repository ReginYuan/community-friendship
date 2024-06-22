module.exports = app => {
  const DataTypes = app.Sequelize;

  const Model = app.model.define('comment', {
    id: {
      type: DataTypes.INTEGER(11),
      allowNull: false,
      primaryKey: true,
      autoIncrement: true
    },
    article_id: {
      type: DataTypes.INTEGER(11),
      allowNull: true
    },
    user_id: {
      type: DataTypes.INTEGER(20).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    reply_count: {
      type: DataTypes.INTEGER(10).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    content: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    comment_id: {
      type: DataTypes.INTEGER(11),
      allowNull: true
    },
    quote: {
      type: DataTypes.TEXT,
      allowNull: true,
      get() {
        const val = this.getDataValue('quote')
        return val ? JSON.parse(val) : val
      },
      set(val) {
        if (val) {
          this.setDataValue('quote', JSON.stringify(val))
        }
      }
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
    tableName: 'comment'
  });

  // 更新评论回复数
  Model.updateReplyCount = async function (comment_id) {
    if (!comment_id) {
      return
    }
    const count = await app.model.Comment.count({
      where: {
        comment_id
      }
    })
    const comment = await app.model.Comment.findByPk(comment_id)
    if (comment) {
      comment.reply_count = count
      await comment.save()
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

  Model.associate = function () {
    // 关联作者
    Model.belongsTo(app.model.User, {
      foreignKey: 'user_id',
      targetKey: 'id',
      as: 'user'
    })

    // 关联帖子
    Model.belongsTo(app.model.Article, {
      foreignKey: 'article_id',
      targetKey: 'id',
      as: 'article'
    })

    // 关联是否关注该作者
    Model.belongsTo(app.model.Follow, {
      foreignKey: 'user_id',
      targetKey: 'follow_id'
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
    // 更新用户评论数
    app.model.User.updateCommentsCount(model.user_id)
    // 更新评论回复数
    if (model.comment_id) {
      app.model.Comment.updateReplyCount(model.comment_id)
    } else {
      // 更新帖子评论数
      app.model.Article.updateCommentCount(model.article_id)
    }
  });

  // 修改前
  Model.beforeUpdate((model, option) => {
    // 自动写入时间戳
    model.update_time = Math.floor((new Date().getTime() / 1000))
  });

  // 删除后
  Model.afterDestroy((model, option) => {
    // 更新用户评论数
    app.model.User.updateCommentsCount(model.user_id)
    // 更新评论回复数
    if (model.comment_id) {
      app.model.Comment.updateReplyCount(model.comment_id)
    } else {
      // 更新帖子评论数
      app.model.Article.updateCommentCount(model.article_id)
    }
  });

  return Model;
};
