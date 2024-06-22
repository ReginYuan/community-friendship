/* indent size: 2 */

module.exports = app => {
  const DataTypes = app.Sequelize;

  const Model = app.model.define('im_message', {
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
    target_id: {
      type: DataTypes.INTEGER(20).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    is_revoke: {
      type: DataTypes.INTEGER(1).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    is_push: {
      type: DataTypes.INTEGER(1).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    type: {
      type: DataTypes.STRING(10),
      allowNull: false,
      defaultValue: 'text'
    },
    state: {
      type: DataTypes.INTEGER(4).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    body: {
      type: DataTypes.TEXT,
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
    client_create_time: {
      type: DataTypes.INTEGER(12),
      allowNull: true,
      get() {
        return app.formatTime(this.getDataValue('client_create_time'))
      }
    }
  }, {
    tableName: 'im_message'
  });

  const stateOptions = {
    100: "发送成功",
    101: "对⽅已把你拉⿊",
    102: "你把对⽅拉⿊了",
    103: "对⽅已被系统封禁",
    104: "禁⽌发送（内容不合法）"
  }

  Model.getStateText = function (state) {
    return stateOptions[state]
  }

  // 获取未推送的消息记录
  Model.getUnPushMessages = async function (target_id) {
    let list = await this.findAll({
      where: {
        target_id,
        is_push: 0,
        is_revoke: 0,
        state: 100,
      },
      include: [
        {
          model: app.model.User,
          attributes: ['id', 'username', 'avatar', 'phone', 'email'],
        },
        {
          model: app.model.ConversationMessage,
          attributes: ["conversation_id"],
          where: {
            user_id: target_id
          },
        },
      ],
    })

    list = app.toArray(list)

    return list.map(o => {
      o.name = o.user ? o.user.name : "用户已被删除"
      o.avatar = o.user ? o.user.avatar : null
      o.conversation_id = o.conversation_message.conversation_id
      o.state_text = stateOptions[o.state]
      delete o.conversation_message
      delete o.user
      return o
    })
  }

  Model.associate = function () {
    // 关联用户
    Model.belongsTo(app.model.User, {
      foreignKey: 'user_id',
      targetKey: 'id'
    })

    // 关联会话ID
    Model.hasOne(app.model.ConversationMessage, {
      foreignKey: 'message_id',
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
  // 修改前
  Model.beforeUpdate((model, option) => {
    // 自动写入时间戳
    model.update_time = Math.floor((new Date().getTime() / 1000))
  });

  return Model;
};
