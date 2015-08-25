
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

If you are writing a package and want a NPM package to be available in the `node_modules` directory of Composer's root
 (instead of the `node_modules` directory of your package), you can add the `expose-npm-packages`
attribute to the composer `extra` session of your package:
 
     "require": {
         "koala-framework/composer-extra-assets": "~1.1"
     },
     "extra": {
         "require-npm": {
             "gulp": "*"
         },
         "expose-npm-packages": true
     }


### Generated files

This plugin will automatically generate 3 files: `.bowerrc`, `bower.json`, `package.json`/

Unless you have special requirements, you can ignore those 3 files in your VCS. If you are using GIT, 
add this to your `.gitignore`:

.gitignore

    vendor/
    .bowerrc
    bower.json
    package.json

### Lock

This plugin will generate a file named `composer-extra-assets.lock` which can be used just like `composer.lock`. Put it
under version control if you want to be able to install the exact same dependencies.

