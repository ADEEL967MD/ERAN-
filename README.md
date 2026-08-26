# Global Mart Demo — MongoDB edition

This is a demo/sandbox PHP 8 application. Deposits, withdrawals, investments and earnings are simulated records only. It does not process real money.

The original project structure is preserved, but the data layer uses MongoDB through the official PHP library. Set `MONGODB_URI` and `MONGODB_DATABASE` in Heroku Config Vars; do not place credentials in source code.

## Deploy setup

1. Import this project into GitHub with `Procfile`, `composer.json`, and `composer.lock` at the repository root.
2. Create a MongoDB Atlas database and allow the Heroku application's outbound access according to your Atlas network access policy.
3. Set `MONGODB_URI`, `MONGODB_DATABASE`, `APP_URL`, and `APP_DEBUG=false` in Heroku Config Vars.
4. Deploy the branch, then run `heroku run php database/seed.php --app YOUR-APP-NAME`.
5. Open the HTTPS URL shown by Heroku, not `localhost`.

Demo accounts after seeding: `demo / demo123` and `admin / admin123` at `admin/login.php`.
