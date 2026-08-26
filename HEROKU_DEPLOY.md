# Global Mart Demo — Heroku deployment

یہ files موجودہ `global-mart-demo` project کے root folder میں copy کریں۔ **موجودہ project کی files overwrite نہ کریں**؛ صرف یہ نئی files add کریں:

| File | کہاں رکھنی ہے |
|---|---|
| `Procfile` | project root، یعنی `index.php` کے ساتھ |
| `composer.json` | project root، یعنی `index.php` کے ساتھ |
| `.env.example` | project root، صرف variable names کے reference کے لیے |
| `HEROKU_DEPLOY.md` | project root، deployment guide کے طور پر |

## 1. Files copy کریں

`Procfile` اور `composer.json` لازماً project کے root میں ہوں۔ Folder structure تقریباً اس طرح نظر آنا چاہیے:

```text
global-mart-demo/
├── Procfile
├── composer.json
├── index.php
├── config/
├── includes/
├── admin/
├── assets/
└── database/
```

## 2. Heroku app بنائیں

اپنے terminal میں project root کھول کر Git repository بنائیں اور Heroku app create کریں:

```bash
git init
git add .
git commit -m "Prepare Global Mart demo for Heroku"
heroku login
heroku create YOUR-APP-NAME
git push heroku main
```

اگر branch کا نام `master` ہو تو آخری command یہ استعمال کریں:

```bash
git push heroku master
```

## 3. MySQL database configure کریں

Heroku app کے لیے ایک external MySQL/MariaDB provider استعمال کریں۔ Provider سے یہ values حاصل کر کے Heroku Config Vars میں add کریں:

```bash
heroku config:set DB_HOST="your-mysql-host"
heroku config:set DB_NAME="global_mart_demo"
heroku config:set DB_USER="your-mysql-user"
heroku config:set DB_PASS="your-mysql-password"
heroku config:set APP_URL="https://YOUR-APP-NAME.herokuapp.com"
heroku config:set APP_NAME="Global Mart Demo"
heroku config:set APP_DEBUG="false"
```

Database provider کے SQL console یا MySQL client سے project کی `database/database.sql` file import کریں۔ پھر demo users بنانے کے لیے Heroku environment کے ساتھ seed script چلائیں:

```bash
heroku run php database/seed.php
```

## 4. Open اور verify کریں

```bash
heroku open
heroku logs --tail
```

User login page:

```text
https://YOUR-APP-NAME.herokuapp.com/login.php
```

Admin login page:

```text
https://YOUR-APP-NAME.herokuapp.com/admin/login.php
```

Demo credentials:

```text
User:  demo / demo123
Admin: admin / admin123
```

## Important notes

`config/database.php` پہلے ہی environment variables پڑھتا ہے، اس لیے Heroku پر database password کو source code میں لکھنے کی ضرورت نہیں ہے۔ `APP_DEBUG=false` production deployment کے لیے رکھیں۔ یہ application demo/sandbox ہے؛ حقیقی payments، investments یا guaranteed returns process نہیں کرتی۔

Heroku filesystem persistent storage نہیں ہے۔ اگر بعد میں receipt یا image upload feature شامل کیا جائے تو local `assets/images` کے بجائے cloud object storage استعمال کریں۔
