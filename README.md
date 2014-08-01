
## Composer Plugin for installing Assets using native npm/bower

This Composer plugin installs assets using native npm/bower. Not only the root package can
have dependencies.

Npm packages will be installed in package folder, bower will be installed in root - with
all dependencies merged.

# Example usage

    "require": {
        "koala-framework/composer-extra-assets": "dev-master"
    },
    "extra": {
        "require-npm": {
            "grunt": "0.4.*",
        },
        "require-bower": {
            "jquery": "*"
        }
    }
