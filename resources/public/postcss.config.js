var path = require('path')
module.exports = ({ file, options, env }) => ({
    map: 'inline',
    plugins: {
        'autoprefixer': { root: file.dirname },
        'postcss-preset-env': options['postcss-preset-env'] ? options['postcss-preset-env'] : false,
        'postcss-import': {},
        'postcss-import-url': {},
        'postcss-url': {
            url: "rebase"
        },
        'postcss-assets':{
            loadPaths: ['../../public/assets/images'],
        },

        'postcss-nested': {},
        'postcss-color-function': {},
        'postcss-css-reset': {},
        'css-mqpacker': {},
        'postcss-browser-reporter': {}
    }
})
