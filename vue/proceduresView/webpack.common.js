// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

const path = require('path')
const { VueLoaderPlugin } = require('vue-loader')

const ESLintPlugin = require('eslint-webpack-plugin');
const myEslintOptions = {
  extensions: ['js', 'jsx', 'ts'],
  exclude: ['node_modules'],
}

// Enable Istanbul only when explicitly requested (e.g. COVERAGE=1)
const enableCoverage =
  process.env.COVERAGE === '1' ||
  process.env.COVERAGE === 'true'

module.exports = {
  entry: {
    proceduresView: path.join(__dirname, 'src', 'main.js'),
  },
  output: {
    path: path.resolve(__dirname, '../../js'),
    publicPath: '/js/',
    filename: '[name].js',
    chunkFilename: 'chunks/[name]-[hash].js',
  },
  module: {
    rules: [
      {
        test: /\.s(c|a)ss$/,
        use: [
          'vue-style-loader',
          'css-loader',
          {
            loader: 'sass-loader',
            // Requires >= sass-loader@^8.0.0
            options: {
              implementation: require('sass'),
              // sassOptions: {
              //   indentedSyntax: true, // optional
              // },
            },
          },
        ],
      },
      // Additional configuration to handle *.css files
      {
        test: /\.css$/i,
        use: ['style-loader', 'css-loader'],
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: /node_modules/,
        options: {
          // Only add Istanbul when COVERAGE is enabled
          plugins: [
            ...(enableCoverage ? ['istanbul'] : []),
          ],
        },
      },
      {
        test: /\.(png|jpg|gif|svg)$/,
        type: 'asset/resource',
        generator: {
          filename: 'assets/images/[name][ext]?[hash]',
        },
      },
    ],
  },
  plugins: [
    new VueLoaderPlugin(),
    new ESLintPlugin(myEslintOptions),
  ],
  resolve: {
    extensions: ['*', '.js', '.vue'],
    symlinks: false,
  },
}
