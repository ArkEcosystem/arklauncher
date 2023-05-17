const defaultConfig = require('./vendor/arkecosystem/foundation/resources/tailwind.config.js');

/** @type {import('tailwindcss').Config} */
module.exports = {
    ...defaultConfig,
    theme: {
        ...defaultConfig.theme,
        extend: {
            ...defaultConfig.theme.extend,
            screens: {
                xxl: '1550px',
            },
            opacity: {
                '90': '.9',
                '40': '.4',
            },
            width: {
                ...defaultConfig.theme.extend.width,
                '120': '30rem',
                '160': '40rem',
            },
            maxWidth: {
                ...defaultConfig.theme.extend.maxWidth,
                '134': '33.5rem',
            },
            padding: {
                '28': '7rem',
                '30': '7.5rem',
            },
        }
    },
}
