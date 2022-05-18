const path = require('path');
const merge = require('webpack-merge');
const common = require('./webpack.common.js');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const webpack = require('webpack');

module.exports = merge(common, {
    mode: 'development',
    entry: './src/dev/dev.jsx',
    devServer: {
        port: 8080,
        contentBase: path.join(__dirname, 'build'),
        hot: true
    },
    plugins: [
        new webpack.HotModuleReplacementPlugin(),
        new HtmlWebpackPlugin({
            template: './src/dev/dev.html'
        })
    ]
});
