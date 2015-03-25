
## Composer Plugin for installing Assets using native npm/bower

This Composer plugin installs assets using native npm/bower. Not only the root package can
have dependencies.

Npm packages will be installed in package folder, bower will be installed in root - with
all dependencies merged.

[Introduction](http://www.nikosams.net/blog/17_composer_npm_bower_assets_installation_using_composer-extra-assets)

### Example usage

composer.json

    "require": {
        "koala-framework/composer-extra-assets": "~1.1"
    },
    "extra": {
        "require-npm": {
            "grunt": "0.4.*"
        },
        "require-bower": {
            "jquery": "*"
        },
        "require-dev-bower": {
            "qunit": "*"
        }
    }
