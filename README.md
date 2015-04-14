
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

### Bower dependencies

Bower dependencies will be installed in the `vendor/bower_components` directory.

### NPM dependencies

NPM dependencies will be installed in the `node_modules` directory of the package that requires the dependency.
Some NPM packages provide binary files (for instance `gulp` and `grunt`).

NPM binaries will be exposed in the `vendor/bin` directory if the NPM dependency is declared in the **root Composer 
package**.

If you are writing a package and want a NPM binary to be exposed in `vendor/bin`, you can add the `expose-npm-binaries`
attribute to the composer `extra` session:
 
     "require": {
         "koala-framework/composer-extra-assets": "~1.1"
     },
     "extra": {
         "require-npm": {
             "gulp": "*"
         },
         "expose-npm-binaries": true
     }

