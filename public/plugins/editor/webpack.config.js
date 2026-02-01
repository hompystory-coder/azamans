'use strict';

const path = require('path');
const { styles } = require('@ckeditor/ckeditor5-dev-utils');
const TerserWebpackPlugin = require('terser-webpack-plugin');

module.exports = {
    entry: './src/ckeditor.js',

    output: {
        path: path.resolve(__dirname, 'build'),
        filename: 'ckeditor.js',
        library: 'ClassicEditor',
        libraryTarget: 'umd',
        libraryExport: 'default'
    },

    optimization: {
        minimizer: [
            new TerserWebpackPlugin({
                terserOptions: {
                    output: {
                        comments: false
                    }
                },
                extractComments: false
            })
        ]
    },

    plugins: [],

    module: {
        rules: [
            {
                test: /\.svg$/,
                use: ['raw-loader']
            },
            {
                test: /\.css$/,
                use: [
                    {
                        loader: 'style-loader',
                        options: {
                            injectType: 'singletonStyleTag',
                            attributes: {
                                'data-cke': true
                            }
                        }
                    },
                    'css-loader',
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: styles.getPostCssConfig({
                                themeImporter: {
                                    themePath: require.resolve('@ckeditor/ckeditor5-theme-lark')
                                },
                                minify: true
                            })
                        }
                    }
                ]
            }
        ]
    }
};
