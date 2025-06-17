const resolve = require("path").resolve;
const defaultConfig = require("@wordpress/scripts/config/webpack.config");

module.exports = [
    {
        ...defaultConfig[0],
        entry: {
            ...defaultConfig[0].entry(),
            "modules/media-filter": resolve(
                process.cwd(),
                "src",
                "media-filter",
                "index.js"
            ),
            "modules/media-library-notices": resolve(
                process.cwd(),
                "src",
                "media-library-notices",
                "index.js"
            ),
            "modules/frontend-inspector": resolve(
                process.cwd(),
                "src",
                "frontend-inspector",
                "index.js"
            ),
            "settings": resolve(
                process.cwd(),
                "src",
                "settings",
                "index.js"
            ),
        }
    },
    {
        ...defaultConfig[1],
        entry: {
            ...defaultConfig[1].entry(),
            "modules/media-library-quick-edit": resolve(
                process.cwd(),
                "src",
                "media-library-quick-edit",
                "index.js"
            ),
        }
    }
];
