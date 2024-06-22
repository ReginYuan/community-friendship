/** @type Egg.EggPlugin */
module.exports = {
  // had enabled by egg
  nunjucks: {
    enable: true,
    package: 'egg-view-nunjucks',
  },
  cors: {
    enable: true,
    package: 'egg-cors',
  },
  sequelize: {
    enable: true,
    package: "egg-sequelize",
  },
  valparams: {
    enable: true,
    package: 'egg-valparams'
  },
  redis: {
    enable: true,
    package: "egg-redis",
  },
  jwt: {
    enable: true,
    package: "egg-jwt",
  }
};
