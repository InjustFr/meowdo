# MossyDew — running on a server

This folder is all a server needs: `compose.yaml` runs the app image (FrankenPHP, assets built in) and a PostgreSQL 18 database. No source checkout, no PHP or Node on the host.

## Prerequisites

- Docker Engine with the Compose plugin (`docker compose version`).
- An SMTP account to send invitation and password emails.
- A reverse proxy (Caddy, nginx, Traefik…) that terminates HTTPS and forwards to the port you choose below.

## First install

```bash
mkdir -p ~/mossydew && cd ~/mossydew
# copy compose.yaml, .env.dist and README.md here (`make deploy-files DEPLOY_HOST=user@server` from a dev machine does it)
cp .env.dist .env
chmod 600 .env
```

Fill `.env`:

| Variable | Value |
|---|---|
| `IMAGE` / `TAG` | Image to run, e.g. `docker.io/injust/mossydew` / `latest` or a commit tag |
| `APP_PORT` | Host port the app listens on (the proxy targets it) |
| `APP_BIND` | `0.0.0.0` when the proxy is on another machine, `127.0.0.1` when it runs on this server |
| `TRUSTED_PROXIES` | Who may set `X-Forwarded-*`: the proxy IP/CIDR (e.g. `203.0.113.10`), `private_ranges`, or `REMOTE_ADDR` (trust whoever connects — only when a firewall lets nothing but the proxy reach `APP_PORT`) |
| `DEFAULT_URI` | Public URL, e.g. `https://mossydew.example.com` (used in email links) |
| `APP_SECRET` | `openssl rand -hex 32` |
| `POSTGRES_PASSWORD` | `openssl rand -hex 24` (`POSTGRES_DB` / `POSTGRES_USER` can stay `app`) |
| `MAILER_DSN` / `MAILER_FROM` | SMTP DSN (`smtp://user:pass@smtp.example.com:587`) and sender address |

Never change `APP_SECRET` or `POSTGRES_PASSWORD` after the first start: sessions, remember-me cookies and the database depend on them. Keep a copy of `.env` with your backups.

## Start

```bash
docker compose pull
docker compose up -d
docker compose logs -f app
```

On every start the app waits for the database, runs pending migrations, warms the cache, then serves HTTP on `APP_PORT`.

## First user

There is no sign-up page. Create an account (it gets its player profile and cat); it receives an email to choose its password:

```bash
docker compose exec app php bin/console app:user:create you@example.com --name=You --cat=Mochi --coat=ginger --timezone=Europe/Paris
docker compose exec app php bin/console app:user:invite you@example.com   # resend a fresh invitation (earlier link stops working)
```

## Reverse proxy

The app speaks plain HTTP. The proxy must forward `Host` and the `X-Forwarded-For`, `X-Forwarded-Proto` and `X-Forwarded-Host` headers, and its IP must match `TRUSTED_PROXIES`; otherwise the app thinks it is served over HTTP and rejects form and API submissions from the HTTPS origin.

Caddy:

```caddyfile
mossydew.example.com {
    reverse_proxy app-server:8080
}
```

nginx:

```nginx
server {
    listen 443 ssl;
    server_name mossydew.example.com;

    location / {
        proxy_pass http://app-server:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
    }
}
```

## Update / rollback

Every push to `main` runs the full test suite (`make ci`) and, only if it passes, publishes `docker.io/injust/mossydew:<short sha>` and `:latest` (GitHub Actions, [`.github/workflows/ci.yml`](../.github/workflows/ci.yml)). `make deploy` refuses a commit whose image is not published, so only tested commits reach the server. The image is public: the server pulls it without `docker login`.

From a dev machine, once the Actions run for the commit is green:

```bash
make deploy      # defaults to DEPLOY_HOST=debian@duprat.cloud DEPLOY_DIR=/mnt/mossydew REMOTE_DOCKER="sudo -n docker"; override them for another server
```

It writes `IMAGE` and `TAG` (the current commit, override with `TAG=<sha>`) into the server `.env`, pulls the image and restarts the app. By hand on the server:

```bash
TAG=<tag> docker compose pull app && TAG=<tag> docker compose up -d
```

`latest` is the most recent push; rolling back means starting a previous tag. Migrations only move forward: restore a backup before rolling back across a schema change.

## Operations

```bash
docker compose ps
docker compose logs -f app
docker compose exec -T database pg_dump -U app app | gzip > backup-$(date +%F).sql.gz
gunzip -c backup.sql.gz | docker compose exec -T database psql -U app app
docker compose down            # stop, data kept
docker compose down -v         # stop and DELETE the database
```

## duprat.cloud setup

On `debian@duprat.cloud` the app does not publish a port: `compose.override.yaml` (server only) joins the shared nginx network `docker-onlyoffice-nextcloud_default` with the alias `mossydew`, and the `mossydew.duprat.cloud` server block in `/mnt/docker-onlyoffice-nextcloud/data/nginx/default.conf` proxies to `http://mossydew`. The TLS certificate is the shared `duprat.cloud` Let's Encrypt certificate (certbot `--expand` with `-d mossydew.duprat.cloud` added).

```yaml
services:
  app:
    ports: !reset []
    networks:
      default:
      proxy:
        aliases:
          - mossydew
networks:
  proxy:
    name: docker-onlyoffice-nextcloud_default
    external: true
```
