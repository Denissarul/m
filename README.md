# Avaris Web

Animated, invite-only PHP/MySQL membership website for Avaris FiveM anticheat. No Node build, Composer dependencies, CDN, paid animation package or external font service is needed.

## XAMPP setup (about five minutes)

1. Copy this entire folder to `C:\xampp\htdocs\Avaris-web`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`, choose **Import**, and import `database.sql`. It creates the `avaris` database and five tables. It does not drop existing tables or records.
4. Edit `config.php` with your database host, port, database name, username and password. Defaults match a typical local XAMPP installation.
5. In a terminal run:

   ```powershell
   cd C:\xampp\htdocs\Avaris-web
   C:\xampp\php\php.exe setup.php
   ```

   Enter your name, email and a unique password of 12–72 bytes. Password input is visible in this local terminal. Setup creates the first administrator and refuses to run again once an admin exists. No default admin password is shipped.
6. Open `http://localhost/Avaris-web/`, click **Client login**, and sign in with the administrator you just created.
7. Open **Invitations**. Enter a label, membership duration and redemption window, then select **Generate key**. Copy the key immediately: it is displayed once, and only its SHA-256 hash is stored.
8. Give the recipient the site URL and key. They choose **Activate your invite**, create an account, and redeem it in one transaction.

## Updating an existing website: guided member server setup

Copy the updated PHP files, assets, `.htaccess` and `server-setup.sql` into your existing website folder. Keep your existing `config.php`. In phpMyAdmin, select your existing Avaris database and import `server-setup.sql` once. This adds only `server_setups` and preserves existing accounts, invitations and history. New installations get this table through `database.sql` automatically.

Normal members now land on **My server**: save a server name, select a framework and optional screenshot evidence, then work through five installation steps. Each account gets its own saved profile and checklist. The generated startup order follows the saved options. Changing framework or evidence options resets confirmations; renaming a server preserves them. Progress saves to MySQL and survives sign-outs and restarts.

The guide includes resource placement, configuration snippets, in-game staff permissions, diagnostic commands, gameplay testing and troubleshooting. Confirmations are manual, not live server health checks. Webhook credentials are entered locally in server.cfg and are not collected by the website. Setup help remains accessible to expired members so they can review their configuration while renewing access.

## What is included

- Landing page with a floating shield, layered 3D orbital animations, scan beam, animated gradients, moving highlights, pointer-reactive feature cards and scroll reveals.
- Mobile layouts and an operating-system reduced-motion fallback. Forms work without JavaScript.
- Invite-only registration, login, logout and password visibility toggle.
- Member dashboard: guided server installation, saved framework profile, per-account setup progress, configuration snippets, troubleshooting, membership status, exact UTC expiry, days remaining, access extension and account activity.
- Admin dashboard: single-use invite generation, configurable access days and redemption deadline, revocation, status-filtered invite ledger, paginated member list and recent audit events.
- Capability descriptions based on the sibling Avaris-AC and Avaris-Guard documentation.
- Installation guide for the website and FiveM resources.

## Invitation and time rules

- **Unused**: unredeemed, unrevoked and before its redemption deadline.
- **Used**: successfully redeemed exactly once. The key stays in the Used ledger with recipient and redemption time; it never becomes unused again.
- **Expired**: unredeemed and past its redemption deadline.
- **Revoked**: disabled by an administrator before redemption.
- Access duration is 1–3650 days. Redemption window is 1–365 days from creation.
- Registration starts access at the database's current UTC time.
- Another invite extends an active member's existing expiry. If membership has expired, its new period starts now.
- All datetimes are persisted in MySQL in UTC. Reloads, sign-outs and server restarts do not reset them. Status is calculated from the current time, so no cron job is required. Day counts round up; the exact timestamp is authoritative.
- Expired members can still sign in and redeem a replacement key. Administrators have unlimited administrative access.
- A SQL transaction and row lock prevent simultaneous redemption of the same invitation. User access extensions are performed atomically.
- Revocation disables an unused key only. It does not cancel a membership already created from that key.

## Scope

These keys manage **website membership**. The existing FiveM anticheat has no license client. This website does not add enforcement to those resources, stream game telemetry, manage servers remotely, or pretend to display live detections. Integrating resource licensing or telemetry requires a separate authenticated server API and changes to the FiveM resources. No downloadable resource files or secrets are copied into the public website.

## Hosting

Requires PHP 8.1+ with PDO MySQL and MariaDB/MySQL with InnoDB. Tested locally with XAMPP PHP 8.2 and MariaDB 10.4.

- Upload the website folder to your PHP hosting document root. Import the SQL through the host's database interface and update `config.php`.
- If your host assigns a database name, omit the first two SQL statements (`CREATE DATABASE` and `USE avaris`) and import the remaining tables into that database.
- Create the initial admin with the host's terminal using `php setup.php`. Without terminal access, run setup locally against the same schema, then securely import your local `users` record (including its password hash) before creating invitations. Do not expose a public admin-creation page.
- Configure HTTPS and set `secure_cookies` to `true` before using real accounts on a public host.
- Use a dedicated database user. Runtime needs SELECT, INSERT and UPDATE on these five tables. Import schema with a separate privileged database account.
- Apache must allow the included `.htaccess` rules (`AllowOverride All` and `mod_authz_core`). They prevent SQL/config/setup/documentation downloads and directory listings. On Nginx, explicitly deny those files and do not enable directory listing.
- Keep PHP `display_errors=Off` on public hosting and route `error_log` outside the public folder. Back up the database regularly; never publish database dumps in the web directory.
- No trusted proxy is assumed. Throttling uses the direct connection IP. With a reverse proxy, configure trusted client-IP handling at the web-server layer; do not trust arbitrary forwarded headers in PHP.

## Authentication and data

Passwords use PHP `password_hash` / `password_verify`. SQL uses PDO prepared statements. Mutations require CSRF tokens. Sessions rotate on login/registration, use HttpOnly and SameSite=Lax cookies, and have a one-hour idle expiry. Login, registration and redemption attempts use database-backed 15-minute per-IP limits. Generated keys contain 128 bits of randomness. HTML output is escaped; a restrictive Content Security Policy prevents external scripts and framing.

Tables: `users`, `invites`, `audit_log`, `rate_limits`, `server_setups`. Audit records include sign-ins, invite creation/redemption/revocation and administrator setup. The app does not send email; account recovery is handled by the administrator. There is no public password-reset endpoint.

To recover an admin password, generate a new hash locally with PHP `password_hash`, then update that administrator's `password_hash` in a trusted database session. Never store a plaintext password in that column. Roles are only assigned during CLI setup or through trusted database administration.

## Verification

PHP syntax checks:

```powershell
C:\xampp\php\php.exe -l index.php
C:\xampp\php\php.exe -l lib.php
C:\xampp\php\php.exe -l setup.php
```

Keep any private QA scripts, test databases and screenshots outside the published folder. The sibling workspace QA scripts are development-only.
