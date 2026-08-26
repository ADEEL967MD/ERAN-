# MongoDB + Heroku deployment

This version keeps the complete `global-mart-demo` structure and uses MongoDB. The old MySQL `DB_HOST`/`DB_NAME`/`DB_USER`/`DB_PASS` variables must not be used.

## Required Config Vars

```bash
heroku config:set MONGODB_URI="mongodb+srv://USERNAME:PASSWORD@CLUSTER.mongodb.net/?retryWrites=true&w=majority" --app YOUR-APP-NAME
heroku config:set MONGODB_DATABASE="global_mart_demo" --app YOUR-APP-NAME
heroku config:set APP_URL="https://YOUR-ACTUAL-HEROKU-APP-URL" --app YOUR-APP-NAME
heroku config:set APP_DEBUG="false" --app YOUR-APP-NAME
```

URL-encode special characters in the MongoDB username or password. For example, a password containing `@`, `:`, `/`, `?`, or `#` must be encoded before it is placed inside the URI. Never share the URI publicly; rotate any credential that has already been exposed.

## Deploy and seed

```bash
git add .
git commit -m "Convert Global Mart demo to MongoDB"
git push origin main
heroku run php database/seed.php --app YOUR-APP-NAME
heroku open --app YOUR-APP-NAME
```

The `Procfile` starts Apache using `vendor/bin/heroku-php-apache2`. The `composer.lock` file must be committed. `config/database.php` also detects the current HTTPS host when `APP_URL` is absent, but an explicit `APP_URL` Config Var is recommended.

If the site still shows a database error, run `heroku logs --tail --app YOUR-APP-NAME` and verify that `MONGODB_URI` is present, the Atlas database user password is correct, and Atlas Network Access permits the application to connect. Use the MongoDB Atlas connection string, not a MySQL URI.
