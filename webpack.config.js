const path = require('path');

module.exports = {
  entry: {
    admin: './assets/js/admin.js',
    frontend: './assets/js/frontend.js',
    flashcard: './assets/js/flashcard.js',
    export: './assets/js/export.js',
    reporting: './assets/js/reporting.js'
  },
  output: {
    path: path.resolve(__dirname, 'assets/js/dist'),
    filename: '[name].min.js',
    clean: true
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      }
    ]
  },
  optimization: {
    minimize: true
  },
  externals: {
    jquery: 'jQuery',
    wp: 'wp'
  }
};