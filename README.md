# Restore API
Restore is a RESTful service for handling order transaction in general online store.

### Requirements
- `composer`
- `mysql ^5.6`

### Local Installation
- `clone` or download and extract zip from this source to your web root folder.
- run `composer install` to install dependencies.
- configure database in `db.php`.
- execute migration with `vendor/bin/yii migrate --appconfig=config-console.php`.
- enable `prettyUrl` in server configuration as well as point document root to `/web`. You can also run built in server by executing `vendor/bin/yii serve --docroot=./web`. It will run on port `8080` by default.

