// Copyright (C) 2020-2026 VIDEOATENCION
// SPDX-License-Identifier: AGPL-3.0-or-later

const path = require('path')
const { VueLoaderPlugin } = require('vue-loader')
const VuetifyLoaderPlugin = require('vuetify-loader/lib/plugin')

const ESLintPlugin = require('eslint-webpack-plugin')
const myEslintOptions = {
  extensions: ['js', 'jsx', 'ts'],
  exclude: ['node_modules'],
}

// Enable Istanbul only when explicitly requested (COVERAGE=1 / COVERAGE=true)
const enableCoverage =
  process.env.COVERAGE === '1' ||
  process.env.COVERAGE === 'true'

module.exports = {
  entry: {
    publicPortal: path.join(__dirname, 'src', 'main.js'),
  },
  output: {
    path: path.resolve(__dirname, '../../js'),
    publicPath: '/js/',
    filename: '[name].js',
    chunkFilename: 'chunks/[name].js',
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
    new VuetifyLoaderPlugin(),
  ],
  resolve: {
    extensions: ['*', '.js', '.vue'],
    symlinks: false,
  },
}
