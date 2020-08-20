const webpack = require('webpack');
const path = require('path');

const config = {
    mode: 'production',
    stats: {
        colors: true,
        hash: true,
        timings: true,
        assets: true,
        chunks: true,
        chunkModules: true,
        modules: true,
        children: true
    },

    entry: {
        'app': './app.js',
    },

    // Tell webpack where to put output file
    output: {
        filename: 'js/[name].js',
        path: path.resolve(__dirname, 'webroot'),
    },
};

module.exports = config;
