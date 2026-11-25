# Ticketing — Portainer Deployment

The app is built by GitHub Actions into a GHCR image (`ghcr.io/mfelix22/ticketing:latest`)
on every push to `main`. The Portainer stack pulls that image.

## How it works

- `Dockerfile` builds the app under `/var/www/html/ticketing` with Apache document
  root at `/var/www/html`, so the app is reachable at `http://<host>:6100/ticketing`
  — the same subpath layout as `https://sdp.hartonomotor-group.com/ticketing`.
- `entrypoint.sh` waits for MySQL, runs `php artisan migrate --force` and
  `php artisan storage:link`, then starts Apache.
- `.github/workflows/docker.yml` builds and pushes the image, then (optionally)
  calls the Portainer stack webhook to redeploy.

## 1. Portainer stack

1. Portainer → **Stacks** → **Add stack** → name: `ticketing`.
2. Paste `docker-compose.yml` (or use **Git repository** build method pointing at this repo).
3. Set the environment variables:

| Variable | Example | Notes |
|---|---|---|
| `APP_KEY` | `base64:...` | Copy from the existing `.env`, or generate with `php artisan key:generate --show` |
| `APP_URL` | `https://sdp.hartonomotor-group.com/ticketing` | Default already set in compose |
| `ASSET_URL` | same as `APP_URL` | Default already set in compose |
| `DB_DATABASE` | `ticketing` | Default `ticketing` |
| `DB_USERNAME` | `laravel` | Default `laravel` |
| `DB_PASSWORD` | `<strong password>` | Required |
| `MYSQL_ROOT_PASSWORD` | `<strong password>` | Required |
| `APP_PORT` | `6100` | Default `6100` |
| `PMA_PORT` | `6101` | Default `6101` |
| `MAIL_HOST` / `MAIL_USERNAME` / `MAIL_PASSWORD` / `MAIL_FROM_ADDRESS` | your mail settings | Optional; defaults to `log` mailer |

4. **Deploy the stack.**

## 2. Reverse proxy

Point `sdp.hartonomotor-group.com/ticketing` → `http://<docker-host>:6100`
**preserving the URI path** (the app expects requests to arrive with the
`/ticketing` prefix, just like the current `/var/www/html/ticketing` deployment).

## 3. Migrating existing data

The stack creates a **new empty** MySQL database. To move the existing data from
`10.101.24.113`:

```bash
# on the old server
mysqldump -h 10.101.24.113 -u sdp -p ticketing > ticketing.sql

# import into the container (or use phpMyAdmin on port 6101)
docker exec -i ticketing_db mysql -u root -p<root_pw> ticketing < ticketing.sql
```

Alternatively set `DB_HOST` etc. in the stack env to the external server and
remove the `mysql`/`phpmyadmin` services.

## 4. Auto-redeploy on push

1. In Portainer, open the `ticketing` stack → **Webhooks** → create a webhook, copy the URL.
2. In GitHub repo → **Settings → Secrets and variables → Actions → Variables** →
   add `PORTAINER_WEBHOOK_URL` with that URL.

Every push to `main` will then rebuild the image and restart the stack with the
new image automatically.

## 5. Useful commands (Portainer console on `ticketing_app`)

```bash
php artisan tinker          # create users, inspect data
php artisan migrate         # run pending migrations
tail -f storage/logs/laravel.log
```
