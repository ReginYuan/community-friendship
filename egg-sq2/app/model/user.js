module.exports = app => {
  const DataTypes = app.Sequelize;
  const Op = app.Sequelize.Op;
  const Model = app.model.define('user', {
    id: {
      type: DataTypes.INTEGER(11),
      allowNull: false,
      primaryKey: true,
      autoIncrement: true
    },
    username: {
      type: DataTypes.STRING(80),
      allowNull: true,
      get() {
        const username = this.getDataValue('username');
        const phone = this.getDataValue('phone');
        const email = this.getDataValue('email');

        if (username) {
          this.setDataValue('name', username);
        } else if (phone) {
          this.setDataValue('name', app.maskPhone(phone));
        } else if (email) {
          this.setDataValue('name', app.maskEmail(email));
        } else {
          this.setDataValue('name', "未知");
        }

        return username;
      }
    },
    avatar: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    password: {
      type: DataTypes.STRING(255),
      allowNull: true,
      comment: "密码",
      set(val) {
        // 密码加密
        this.setDataValue('password', app.createPassword(val))
      },
      get() {
        const password = this.getDataValue('password');
        return !!password;
      }
    },
    phone: {
      type: DataTypes.STRING(11),
      allowNull: true,
      unique: true,
      get() {
        const phone = this.getDataValue('phone');
        return app.maskPhone(phone)
      }
    },
    email: {
      type: DataTypes.STRING(255),
      allowNull: true,
      unique: true,
      get() {
        const email = this.getDataValue('email');
        return app.maskEmail(email)
      }
    },
    status: {
      type: DataTypes.INTEGER(1).UNSIGNED,
      allowNull: false,
      defaultValue: '1'
    },
    age: {
      type: DataTypes.INTEGER(3).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    sex: {
      type: DataTypes.INTEGER(1).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    qg: {
      type: DataTypes.INTEGER(1).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    job: {
      type: DataTypes.STRING(10),
      allowNull: true
    },
    path: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    birthday: {
      type: DataTypes.STRING(20),
      allowNull: true
    },
    desc: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    wx_openid: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    wx_unionid: {
      type: DataTypes.STRING(255),
      allowNull: true
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
    fans_count: {
      type: DataTypes.INTEGER(10),
      allowNull: true,
      defaultValue: '0'
    },
    follows_count: {
      type: DataTypes.INTEGER(10),
      allowNull: true,
      defaultValue: '0'
    },
    articles_count: {
      type: DataTypes.INTEGER(10),
      allowNull: true,
      defaultValue: '0'
    },
    comments_count: {
      type: DataTypes.INTEGER(10),
      allowNull: true,
      defaultValue: '0'
    }
  }, {
    tableName: 'user'
  });

  // 根据 昵称/手机/邮箱 搜索
  Model.addScope("search", function (keyword) {
    return {
      where: {
        [Op.or]: {
          username: {
            [Op.like]: `%${keyword}%`
          },
          phone: {
            [Op.like]: `%${keyword}%`
          },
          email: {
            [Op.like]: `%${keyword}%`
          }
        }
      }
    }
  })

  // 是否关注用户
  Model.addScope("isfollow", function (user_id) {
    return {
      include: [
        {
          model: app.model.Follow,
          as: 'isfollow',
          attributes: ["id"],
          where: {
            user_id
          },
          required: false
        }
      ]
    }
  })

  // 是否拉黑用户
  Model.addScope("isblacked", function (user_id) {
    return {
      include: [
        {
          model: app.model.Blacklist,
          as: 'isblacked',
          attributes: ["id"],
          where: {
            user_id
          },
          required: false
        }
      ]
    }
  })

  // 更新用户帖子数
  Model.updateArticlesCount = async function (user_id) {
    if (!user_id) {
      return
    }
    // 统计用户帖子数
    const count = await app.model.Article.count({
      where: {
        user_id
      }
    });

    const user = await app.model.User.findByPk(user_id)
    if (user) {
      user.articles_count = count
      await user.save()
    }
  }

  // 统计和更新用户评论数
  Model.updateCommentsCount = async function (user_id) {
    if (!user_id) {
      return
    }
    const count = await app.model.Comment.count({
      where: {
        user_id
      }
    })
    const user = await app.model.User.findByPk(user_id)
    if (user) {
      user.comments_count = count
      await user.save()
    }
  }

  // 统计和更新用户粉丝数
  Model.updateFansCount = async function (user_id) {
    if (!user_id) {
      return
    }
    // 统计用户粉丝数
    const count = await app.model.Follow.count({
      where: {
        follow_id: user_id
      }
    });

    const user = await app.model.User.findByPk(user_id)
    if (user) {
      user.fans_count = count
      await user.save()
    }
  }

  // 统计和更新用户关注数
  Model.updateFollowsCount = async function (user_id) {
    if (!user_id) {
      return
    }
    // 统计用户关注数
    const count = await app.model.Follow.count({
      where: {
        user_id
      }
    });

    const user = await app.model.User.findByPk(user_id)
    if (user) {
      user.follows_count = count
      await user.save()
    }
  }

  // 获取昵称
  Model.getName = function (user) {
    const username = user.getDataValue('username');
    const phone = user.getDataValue('phone');
    const email = user.getDataValue('email');
    let name = null
    if (username) {
      name = username
    } else if (phone) {
      name = app.maskPhone(phone)
    } else if (email) {
      name = app.maskEmail(email)
    } else {
      name = "未知"
    }

    return name;
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

  Model.associate = function () {
    // 关联是否关注该作者
    Model.hasOne(app.model.Follow, {
      foreignKey: 'follow_id',
      targetKey: 'id',
      as: 'isfollow'
    })

    // 关联是否拉黑该用户
    Model.hasOne(app.model.Blacklist, {
      foreignKey: 'black_id',
      targetKey: 'id',
      as: 'isblacked'
    })
  }

  return Model;
};
