
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');
const CopyWebpackPlugin = require('copy-webpack-plugin');

// Build multiple blocks
const blocks = ['lunar-calendar', 'event-details'];

// Generate entry points for all blocks
const entries = {};
blocks.forEach(blockName => {
  entries[`${blockName}/index`] = `./blocks/${blockName}/index.js`;

  // Check if frontend.js exists for this block
  const frontendPath = path.resolve(__dirname, `blocks/${blockName}/frontend.js`);
  const fs = require('fs');
  if (fs.existsSync(frontendPath)) {
    entries[`${blockName}/frontend`] = `./blocks/${blockName}/frontend.js`;
  }
});

// Generate copy patterns for block.json files
const copyPatterns = blocks.map(blockName => ({
  from: path.resolve(__dirname, `blocks/${blockName}/block.json`),
  to: path.resolve(__dirname, `build/${blockName}/block.json`),
}));

module.exports = {
  ...defaultConfig,
  entry: entries,
  output: {
    ...defaultConfig.output,
    path: path.resolve(__dirname, 'build'),
    filename: '[name].js',
    clean: true,
  },
  plugins: [
    ...(defaultConfig.plugins || []),
    new CopyWebpackPlugin({
      patterns: copyPatterns,
    }),
  ],
};

