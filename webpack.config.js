const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
  ...defaultConfig,
  entry: {
    index: './blocks/lunar-calendar/index.js',
    frontend: './blocks/lunar-calendar/frontend.js',
  },
};

