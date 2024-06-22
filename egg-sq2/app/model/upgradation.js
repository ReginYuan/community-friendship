/* indent size: 2 */

module.exports = app => {
  const DataTypes = app.Sequelize;

  const Model = app.model.define('upgradation', {
    id: {
      type: DataTypes.INTEGER(11),
      allowNull: false,
      primaryKey: true,
      autoIncrement: true
    },
    appid: {
      type: DataTypes.STRING(100),
      allowNull: true
    },
    name: {
      type: DataTypes.STRING(100),
      allowNull: true
    },
    title: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    contents: {
      type: DataTypes.TEXT,
      allowNull: true
    },
    platform: {
      type: DataTypes.STRING(255),
      allowNull: true,
      get() {
        let value = this.getDataValue('platform');
        if (!value) {
          value = []
        } else {
          value = value.split(",")
        }
        return value
      },
    },
    type: {
      type: DataTypes.STRING(30),
      allowNull: true
    },
    version: {
      type: DataTypes.STRING(10),
      allowNull: true
    },
    min_uni_version: {
      type: DataTypes.STRING(10),
      allowNull: true
    },
    url: {
      type: DataTypes.STRING(255),
      allowNull: true
    },
    stable_publish: {
      type: DataTypes.INTEGER(1),
      allowNull: true,
      defaultValue: '0',
      get() {
        const value = this.getDataValue('stable_publish');
        return !!value;
      }
    },
    is_silently: {
      type: DataTypes.INTEGER(1),
      allowNull: true,
      defaultValue: '0',
      get() {
        const value = this.getDataValue('is_silently');
        return !!value;
      }
    },
    is_mandatory: {
      type: DataTypes.INTEGER(1),
      allowNull: true,
      defaultValue: '0',
      get() {
        const value = this.getDataValue('is_mandatory');
        return !!value;
      }
    },
    uni_platform: {
      type: DataTypes.STRING(10),
      allowNull: true,
      defaultValue: 'android'
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
    create_env: {
      type: DataTypes.STRING(15),
      allowNull: true,
      defaultValue: 'upgrade-center'
    },
    store_list: {
      type: DataTypes.TEXT,
      allowNull: true
    }
  }, {
    tableName: 'upgradation'
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
