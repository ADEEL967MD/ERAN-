# MongoDB + Heroku deployment

This version keeps the complete `global-mart-demo` structure and uses MongoDB. The MongoDB URI and database name are embedded in `config/database.php`; no database Config Vars are required. The old MySQL `DB_HOST`/`DB_NAME`/`DB_USER`/`DB_PASS` variables must not be used.

## Configuration

No MongoDB Config Vars are required. The connection is already inside `config/database.php`. If you ever need to change the database, edit only these two constants in that file:

```php
const EMBEDDED_MONGODB_URI = 'mongodb+srv://USERNAME:PASSWORD@CLUSTER.mongodb.net/?retryWrites=true&w=majority';
const EMBEDDED_MONGODB_DATABASE = 'global_mart_demo';
```

The optional `MONGODB_URI` environment variable can override the embedded value, but it is not required. URL-encode special characters in the MongoDB username or password. Never share the URI publicly; rotate any credential that has already been exposed.

## Deploy and seed

```bash
git add .
git commit -m "Use embedded MongoDB configuration"
git push origin main
heroku run php database/seed.php --app YOUR-APP-NAME
heroku open --app YOUR-APP-NAME
```

The `Procfile` starts Apache using `vendor/bin/heroku-php-apache2`. The `composer.lock` file must be committed. `config/database.php` also detects the current HTTPS host when `APP_URL` is absent, but an explicit `APP_URL` Config Var is recommended.

If the site still shows a database error, run `heroku logs --tail --app YOUR-APP-NAME` and verify that `MONGODB_URI` is present, the Atlas database user password is correct, and Atlas Network Access permits the application to connect. Use the MongoDB Atlas connection string, not a MySQL URI.
