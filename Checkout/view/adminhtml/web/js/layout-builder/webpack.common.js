const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');

module.exports = {
    output: {
        path: path.join(__dirname, 'build/'),
        filename: 'layout-builder.bundle.js'
    },
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: 'babel-loader'
            },
            {
                test: /\.css$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader']
            },
            {
                test: /\.less$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'less-loader']
            },
            {
                test: /\.svg$/,
                loader: 'svg-inline-loader'
            }
        ]
    },
    optimization: {
        minimize: true,
        minimizer: [new TerserPlugin(), new OptimizeCSSAssetsPlugin({})]
    },
    resolve: {
        extensions: ['.json', '.js', '.jsx', '.less', '.html'],
        alias: {
            '@layoutBuilder': path.resolve(__dirname, 'src/App/LayoutBuilder'),
            '@checkoutResolver': path.resolve(__dirname, 'src/App/CheckoutResolver'),
            '@checkoutItem': path.resolve(__dirname, 'src/App/CheckoutItem'),
            '@images': path.resolve(__dirname, 'public/images')
        }
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'layout-builder.bundle.css'
        }),
        new CleanWebpackPlugin()
    ]
};
