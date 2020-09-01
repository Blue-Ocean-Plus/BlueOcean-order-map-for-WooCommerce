const path = require('path')/*,
    CopyPlugin = require('copy-webpack-plugin')*/;

module.exports = {
    entry: {
        admin: './resources/js/admin.js'
    },

    output: {
        path: path.resolve(__dirname, 'assets/js')
    },
  /*  plugins: [
        new CopyPlugin([
            {
                from: './node_modules/bootstrap/dist/css/bootstrap.min.css',
                to: path.resolve(__dirname, './assets/css/lib/bootstrap/bootstrap.min.css')
            }
        ])
    ]*/
};
