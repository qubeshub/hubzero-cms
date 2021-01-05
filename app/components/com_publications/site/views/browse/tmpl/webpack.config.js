const path = require('path')

module.exports = {
    // mode: 'development',
    entry: './src/index.js',
    output: {
        path: path.resolve(__dirname, '../../../assets/js'),
        filename: 'app.js'
    },
    devServer: {
        // https: true,
        host: '192.168.33.10',
        port: 3000,
        contentBase: './dist',
        hot: true
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: '/node_modules / '
            },
            {
                test: /\.jsx$/,
                loader: 'babel-loader',
                exclude: '/node_modules/'
            },
            {
                test: /\.scss$/,
                use: [{
                    loader: 'style-loader'
                }, {
                    loader: 'css-loader'
                }, {
                    loader: 'sass-loader'
                }]
            }
        ]
    },  
    resolve: {
        extensions: ['.js']
    }
}