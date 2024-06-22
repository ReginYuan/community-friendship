/* eslint valid-jsdoc: "off" */
const path = require('path');
/**
 * @param {Egg.EggAppInfo} appInfo app info
 */
module.exports = appInfo => {
  /**
   * built-in config
   * @type {Egg.EggAppConfig}
   **/
  const config = exports = {};

  // use for cookie sign key, should change to your own and keep security
  config.keys = appInfo.name + '_1713010619355_4319';

  // add your user config here
  const userConfig = {
    // myAppName: 'egg',
  };

  // 模板引擎
  config.view = {
    defaultViewEngine: 'nunjucks',
    root: [
      path.join(appInfo.baseDir, 'app/view'),
    ].join(','),
    mapping: {
      '.html': 'nunjucks',
    },
  };

  // 安全
  config.security = {
    // 关闭 csrf
    csrf: {
      enable: false,
    },
    // 跨域白名单
    // domainWhiteList: ["http://localhost:3000"],
  };
  // 允许跨域的方法
  config.cors = {
    origin: "*",
    allowMethods: "GET, PUT, POST, DELETE, PATCH",
  };

  // 参数验证
  config.valparams = {
    locale: 'zh-cn',
    throwError: true
  };

  require('./config/api')(config)
  require('./config/jwt')(config)
  require('./config/crypto')(config)
  require('./config/middleware')(config)
  require('./config/mysql')(config)
  require('./config/alisms')(config)
  require('./config/redis')(config)
  require('./config/file')(config)
  require('./config/websocket')(config)

  return {
    ...config,
    ...userConfig,
  };
};
