/* indent size: 2 */

module.exports = app => {
  const DataTypes = app.Sequelize;

  const Model = app.model.define('follow', {
    id: {
      type: DataTypes.INTEGER(11),
      allowNull: false,
      primaryKey: true,
      autoIncrement: true
    },
    follow_id: {
      type: DataTypes.INTEGER(10).UNSIGNED,
      allowNull: false,
      defaultValue: '0'
    },
    user_id: {
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
    tableName: 'follow'
  });

  Model.associate = function() {
    // 关联粉丝信息
    Model.belongsTo(app.model.User, {
      foreignKey: 'user_id',
      targetKey: 'id',
      as: 'fan'
    })

    // 关联关注信息
    Model.belongsTo(app.model.User, {
      foreignKey: 'follow_id',
      targetKey: 'id',
      as: 'followuser'
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

  // 新增后
  Model.afterCreate((model, option) => {
    // 对⽅粉丝数+1
    app.model.User.updateFansCount(model.follow_id)
    // 我的关注数+1
    app.model.User.updateFollowsCount(model.user_id)
  });

  // 删除后
  Model.afterDestroy((model, option) => {
    // 对⽅粉丝数-1
    app.model.User.updateFansCount(model.follow_id)
    // 我的关注数-1
    app.model.User.updateFollowsCount(model.user_id)
  });

  return Model;
};
