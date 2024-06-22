/* indent size: 2 */

module.exports = app => {
  const DataTypes = app.Sequelize;

  const Model = app.model.define('blacklist', {
    id: {
      type: DataTypes.INTEGER(11),
      allowNull: false,
      primaryKey: true,
      autoIncrement: true
    },
    black_id: {
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
    tableName: 'blacklist'
  });

  Model.associate = function() {
    // 关联用户
    Model.belongsTo(app.model.User, {
      foreignKey: 'black_id',
      targetKey: 'id',
      as: 'blackuser'
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
