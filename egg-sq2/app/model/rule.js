/* indent size: 2 */

module.exports = app => {
  const DataTypes = app.Sequelize;

  const Model = app.model.define('rule', {
    id: {
      type: DataTypes.INTEGER(11),
      allowNull: false,
      primaryKey: true,
      autoIncrement: true
    },
    rule_id: {
      type: DataTypes.INTEGER(11),
      allowNull: true,
      defaultValue: '0'
    },
    name: {
      type: DataTypes.STRING(50),
      allowNull: true
    },
    status: {
      type: DataTypes.INTEGER(1),
      allowNull: true,
      defaultValue: '1'
    },
    order: {
      type: DataTypes.INTEGER(11),
      allowNull: true,
      defaultValue: '50'
    },
    ismenu: {
      type: DataTypes.INTEGER(1),
      allowNull: true,
      defaultValue: '1'
    },
    icon: {
      type: DataTypes.STRING(100),
      allowNull: true
    },
    method: {
      type: DataTypes.STRING(10),
      allowNull: false,
      defaultValue: 'GET'
    },
    condition: {
      type: DataTypes.STRING(100),
      allowNull: true
    },
    create_time: {
      type: DataTypes.INTEGER(11),
      allowNull: true
    },
    update_time: {
      type: DataTypes.INTEGER(11),
      allowNull: true
    }
  }, {
    tableName: 'rule'
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
