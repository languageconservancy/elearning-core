# Local server setup (MAMP or XAMPP)

The CakePHP backend must run under Apache with MySQL. The Angular frontend runs separately on port 4200, but it calls the API at `http://localhost/backend/api/` — so Apache must serve a `backend/` folder inside your web document root.

Pick **MAMP** (macOS) or **XAMPP** (Windows, Linux, or macOS). Both bundle Apache, PHP, MySQL, and phpMyAdmin.

## Required settings

These values match what the project is tested against:


| Setting       | Value                           | Notes                                                                                                |
| ------------- | ------------------------------- | ---------------------------------------------------------------------------------------------------- |
| Web server    | Apache                          | Not Nginx alone — use Apache for the backend                                                         |
| PHP           | **7.4.33**                      | Required for CakePHP compatibility                                                                   |
| MySQL         | **5.7.44** (or 5.7.x)           | Demo DB and migrations target MySQL 5.7                                                              |
| Apache port   | **80**                          | API URL becomes `http://localhost/backend/api/`. Admin URL becomes `http://localhost/backend/admin/` |
| MySQL port    | **3306**                        | Default                                                                                              |
| PHP cache     | **OPcache**                     | Leave enabled in MAMP/XAMPP — see [OPcache](#opcache)                                                |
| Document root | **Parent folder of** `backend/` | Not `backend/` itself — see below                                                                    |




### Document root

`npm run core sync-local-backend` copies `core/backend/` to `$ELEARNING_WWW_PATH/backend/`. Apache must serve `$ELEARNING_WWW_PATH` as the document root so the API is reachable at:

```
http://localhost/backend/api/
```

Example layout after sync (MAMP defaults):

```
/Applications/MAMP/htdocs/          ← document root (ELEARNING_WWW_PATH)
└── backend/                        ← synced from core/backend/
    ├── webroot/
    ├── config/
    └── ...
```

If you have an extra folder, like `elearning` in `htdocs`, then it would look like this:

```
/Applications/MAMP/htdocs/elearning/  ← document root (ELEARNING_WWW_PATH)
└── backend/                          ← synced from core/backend/
    ├── webroot/
    ├── config/
    └── ...
```

### Local dev variables

`sync-local-backend` reads **`ELEARNING_WWW_PATH`** from `scripts/local-dev-vars.sh` at the language repo root (same folder as `deploy-vars.sh`). The sync script sources this file automatically — no shell profile setup required.

First-time setup — from your language repo root (`elearning-<app-name>/`):

```bash
cp scripts/local-dev-vars.example.sh scripts/local-dev-vars.sh
```

Edit `scripts/local-dev-vars.sh` and set `ELEARNING_WWW_PATH` to your Apache document root. `local-dev-vars.sh` is gitignored; `local-dev-vars.example.sh` is committed as a template.

**MAMP (macOS):**

```bash
export ELEARNING_WWW_PATH='/Applications/MAMP/htdocs'
```

**XAMPP:**

| OS | Typical value |
|----|---------------|
| Windows | `C:/xampp/htdocs` |
| macOS | `/Applications/XAMPP/xamppfiles/htdocs` |
| Linux | `/opt/lampp/htdocs` |

---

## MAMP (macOS)

[MAMP](https://www.mamp.info/en/mac/) is the stack most of the team uses on Mac. As of early 2026, MAMP **7.3** is a known-good version.

### 1. Install MAMP

Download and install from [mamp.info](https://www.mamp.info/en/mac/).

### 2. Configure MAMP

Open **MAMP → Settings** (or **Preferences** on older versions) and set:


| Setting       | Value                                                         |
| ------------- | ------------------------------------------------------------- |
| PHP version   | **7.4.33**                                                    |
| Web server    | **Apache**                                                    |
| Apache port   | **80**                                                        |
| MySQL port    | **3306**                                                      |
| MySQL server  | **5.7.44**                                                    |
| PHP-Cache     | **OPcache**                                                   |
| Document root | **Application → MAMP → htdocs** (`/Applications/MAMP/htdocs`) |


**PHP 7.4.33 missing from the dropdown?**

1. Go to `/Applications/MAMP/bin/php/`
2. Rename PHP versions you do **not** want by prefixing the folder name with `_` (e.g. `_php8.3.14`), leaving only the versions you need.

**Terminal PHP version**

MAMP’s PHP may differ from your system `php`. To check which version is being used, run this in the terminal:

```bash
php -v
```

To use 7.4.33 in the terminal:

```bash
echo 'export PATH="/Applications/MAMP/bin/php/php7.4.33/bin/:$PATH"' >> ~/.bash_profile   # bash
# or
echo 'export PATH="/Applications/MAMP/bin/php/php7.4.33/bin/:$PATH"' >> ~/.zshenv           # zsh
source ~/.bash_profile   # or source ~/.zshenv
```



### 3. Enable Apache URL rewriting

The API uses clean URLs like `http://localhost/backend/api/users/login.json`. There is no file at that path on disk — Apache would normally return 404. CakePHP's `.htaccess` rules use `mod_rewrite` to send those requests to `webroot/index.php`, which routes them to the right controller. Static files (CSS, JS, images) are served directly and skip this step.

Enable this rewrite module in `/Applications/MAMP/conf/apache/httpd.conf` — ensure this line is **uncommented**:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

Apply the change: in the MAMP window, click **Stop**, then **Start** (this restarts Apache and MySQL).

### 4. Set MySQL `sql_mode`

Without this, you may need to fix SQL mode in phpMyAdmin after every restart of the Apache/MySQL.

Copy the project’s config into MAMP:

```bash
cp core/demo/mamp/my.cnf /Applications/MAMP/conf/my.cnf
```

That file sets:

```ini
[mysqld]
sql_mode="STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"
```

Apply the change: in the MAMP window, click **Stop**, then **Start** so MySQL reloads the config.

### 5. Start MAMP and verify

1. Click **Start** in the MAMP window (Apache and MySQL should both show green).
2. Open [http://localhost](http://localhost) — you should see the MAMP start page or an empty htdocs listing.
3. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin) — phpMyAdmin should load.

After you import the demo DB and run `npm run core sync-local-backend`, check [http://localhost/backend/api/](http://localhost/backend/api/) (exact response depends on routing; a CakePHP page or JSON response means Apache is serving the backend).

---



## XAMPP (Windows, Linux, macOS)

[XAMPP](https://www.apachefriends.org/) is a common alternative, especially on Windows. Paths differ by OS; the settings above are the same.

### 1. Install XAMPP with PHP 7.4

Download an XAMPP release that includes **PHP 7.4** from [apachefriends.org/download.html](https://www.apachefriends.org/download.html). Newer XAMPP bundles may ship PHP 8.x only — if 7.4 is not available, install an older 7.4.x XAMPP build or use MAMP on macOS.

### 2. Default paths


| OS          | Document root (`ELEARNING_WWW_PATH`)    | Apache config                                   | MySQL config                                |
| ----------- | --------------------------------------- | ----------------------------------------------- | ------------------------------------------- |
| **Windows** | `C:\xampp\htdocs`                       | `C:\xampp\apache\conf\httpd.conf`               | `C:\xampp\mysql\bin\my.ini`                 |
| **macOS**   | `/Applications/XAMPP/xamppfiles/htdocs` | `/Applications/XAMPP/xamppfiles/etc/httpd.conf` | `/Applications/XAMPP/xamppfiles/etc/my.cnf` |
| **Linux**   | `/opt/lampp/htdocs`                     | `/opt/lampp/etc/httpd.conf`                     | `/opt/lampp/etc/my.cnf`                     |


Use the **XAMPP Control Panel** to start **Apache** and **MySQL**.

### 3. Configure ports

In the control panel, open **Config** for Apache and MySQL (or edit the config files directly):


| Service | Port     |
| ------- | -------- |
| Apache  | **80**   |
| MySQL   | **3306** |


If port 80 is already in use (IIS, Skype, etc.), stop the conflicting service or change Apache’s port — but then update your API URL in `platform/config/demo/app-config.json` accordingly. Port **80** keeps the default `http://localhost/backend/api/` URL.

### 4. Document root

Leave the document root at the default **htdocs** folder (see table above). Do **not** point it at a `backend/` subfolder — sync will create `htdocs/backend/` for you.

### 5. Enable Apache URL rewriting

In `httpd.conf`, uncomment:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

Also find the `<Directory ".../htdocs">` block and set:

```apache
AllowOverride All
```

Restart Apache from the XAMPP control panel.

### 6. Set MySQL `sql_mode`

Append the `[mysqld]` section from `core/demo/mamp/my.cnf` to your MySQL config file (`my.ini` on Windows, `my.cnf` on Mac/Linux). Restart MySQL.

Set `ELEARNING_WWW_PATH` in `scripts/local-dev-vars.sh` using the document root from the table in [Local dev variables](#local-dev-variables) above.

### 7. PHP 7.4 in terminal (optional)

XAMPP’s PHP binary is usually not on your PATH. Examples:

```bash
# Windows (Command Prompt, current session)
set PATH=C:\xampp\php;%PATH%

# macOS
export PATH="/Applications/XAMPP/xamppfiles/bin:$PATH"
```

Run `php -v` and confirm **7.4.x**.

### 8. Verify

1. Apache and MySQL running in XAMPP Control Panel.
2. [http://localhost](http://localhost) loads.
3. [http://localhost/phpmyadmin](http://localhost/phpmyadmin) loads.

---



## OPcache

**OPcache** caches compiled PHP bytecode in memory so repeat requests are faster. MAMP exposes it as **PHP-Cache: OPcache** in settings.

For local development on this project:

- **Leave OPcache enabled** — it is the default and works fine with CakePHP.
- You do not need to change any OPcache settings unless you are debugging stale PHP file issues. If changes to a `.php` file seem ignored, restart Apache or temporarily disable OPcache in MAMP/XAMPP while debugging.

---



## After the server is running

Continue in [developing.md](developing.md):

1. Import `core/demo/elearning_demo_db.sql` into phpMyAdmin as `elearning_demo_db`
2. Run **eLearning: First-time web setup** (or `npm run core copy-demo-assets` then `npm run core sync-local-backend`)
3. Start the demo dev server — frontend at [http://localhost:4200](http://localhost:4200), API at [http://localhost/backend/api/](http://localhost/backend/api/)

Demo DB details: [demo/README.md](../demo/README.md).

---



## Troubleshooting


| Problem                        | Things to check                                                                                                                   |
| ------------------------------ | --------------------------------------------------------------------------------------------------------------------------------- |
| **Port 80 in use**             | Another web server (IIS, built-in Apache, Skype). Stop it or reconfigure.                                                         |
| **`ELEARNING_WWW_PATH` unset** | Copy `scripts/local-dev-vars.example.sh` to `scripts/local-dev-vars.sh` and set your Apache document root. |
| **API 404**                    | MAMP/XAMPP running? Ran `sync-local-backend`? Document root is **htdocs**, not **htdocs/backend**?                                |
| **Database connection errors** | Demo DB imported? MySQL running on 3306? Check `platform/config/demo/.env` and `app_local.php`.                                   |
| **500 / rewrite errors**       | `mod_rewrite` enabled? `AllowOverride All` on htdocs (XAMPP)?                                                                     |
| **SQL mode errors on import**  | Applied `my.cnf` / `my.ini` from `core/demo/mamp/my.cnf` and restarted MySQL.                                                     |
| **Wrong PHP version**          | MAMP/XAMPP settings UI set to 7.4.33; terminal `php -v` matches if you run Composer scripts manually.                             |


