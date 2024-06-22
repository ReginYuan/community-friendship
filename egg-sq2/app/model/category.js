/* indent size: 2 */

module.exports = app => {
  const DataTypes = app.Sequelize;

  const Model = app.model.define('category', {
    id: {
      type: DataTypes.INTEGER(11),
      allowNull: false,
      primaryKey: true,
      autoIncrement: true
    },
    title: {
      type: DataTypes.STRING(5),
      allowNull: false
    },
    type: {
      type: DataTypes.ENUM('article','topic'),
      allowNull: false,
      defaultValue: 'article'
    },
    status: {
      type: DataTypes.INTEGER(1).UNSIGNED,
      allowNull: false,
      defaultValue: '1'
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
    tableName: 'category'
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
