/* indent size: 2 */

module.exports = app => {
  const DataTypes = app.Sequelize;

  const Model = app.model.define('user_action_log', {
    id: {
      type: DataTypes.INTEGER(11),
      allowNull: false,
      primaryKey: true,
      autoIncrement: true
    },
    ip: {
      type: DataTypes.STRING(100),
      allowNull: false,
      defaultValue: '”“'
    },
    user_id: {
      type: DataTypes.INTEGER(11),
      allowNull: true
    },
    url: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    method: {
      type: DataTypes.STRING(10),
      allowNull: true,
      defaultValue: 'GET'
    },
    user_agent: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    param: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    create_time: {
      type: DataTypes.INTEGER(11),
      allowNull: true
    },
    type: {
      type: DataTypes.STRING(10),
      allowNull: true,
      defaultValue: 'info'
    },
    notes: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    update_time: {
      type: DataTypes.INTEGER(10),
      allowNull: true
    }
  }, {
    tableName: 'user_action_log'
  });

  Model.associate = function() {

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
