
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const blockName = 'lunar-calendar';
const blockDir = `blocks/${blockName}`;
const buildDir = `build/${blockName}`;

module.exports = {
  ...defaultConfig,
  entry: {
    index: `./${blockDir}/index.js`,
    frontend: `./${blockDir}/frontend.js`,
  },
  output: {
    ...defaultConfig.output,
    path: path.resolve(__dirname, buildDir),
    filename: '[name].js',
    clean: true,
  },
  plugins: [
    ...(defaultConfig.plugins || []),
    new CopyWebpackPlugin({
      patterns: [
        {
          from: path.resolve(__dirname, `${blockDir}/block.json`),
          to: path.resolve(__dirname, buildDir),
        },
      ],
    }),
  ],
};

