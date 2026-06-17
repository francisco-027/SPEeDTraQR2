# Deploying SPeEdtracQR to Render

A step-by-step guide. Uses Docker (`Dockerfile` + `render.yaml` in the repo root),
managed PostgreSQL, a queue worker, and a cron scheduler.

## Before you start — three Render realities

1. **PostgreSQL, not MySQL.** Render has no managed MySQL, so this guide switches
   `DB_CONNECTION` to `pgsql`. The code already supports it.
2. **Ephemeral disk.** Uploaded attachments and QR PNGs live under `storage/app`.
   Without a **persistent disk** (configured in `render.yaml`) they vanish on every
   redeploy. The free plan does **not** include disks — see Step 6.
3. **Reverb + Ollama are off by default** (`BROADCAST_CONNECTION=log`, `AI_PROVIDER=none`).
   Live tracking falls back to refresh-on-load; the AI assistant uses its rule-based
   answer. Add them later as separate services.

---

## Step 1 — Push the repo to GitHub

Render deploys from a Git remote.

```bash
git add Dockerfile docker/ render.yaml DEPLOYMENT_RENDER.md
git commit -m "Add Render deployment config"
git push origin main
```

Make sure `.env` is **not** committed (it's git-ignored by default) — secrets go in
the Render dashboard.

## Step 2 — Generate an APP_KEY

You'll paste this into Render (don't reuse your local one).

```bash
php artisan key:generate --show
```

Copy the `base64:...` string.

## Step 3 — Create the Render account + Blueprint

1. Sign in at https://dashboard.render.com with your GitHub account.
2. **New → Blueprint**, pick this repository.
3. Render reads `render.yaml` and proposes: a web service, a worker, a cron job,
   and a Postgres database.

## Step 4 — Fill in the secret env vars

`render.yaml` marks these `sync: false`, so Render prompts for them on every service
that uses them (web, worker, cron — paste the same values):

| Key | Value |
|-----|-------|
| `APP_KEY` | the `base64:...` from Step 2 |
| `APP_URL` | `https://speedtraqr-web.onrender.com` (web only; you can fix it after the URL is assigned) |
| `ADMIN_PASSWORD` | a strong password for `admin@speedtraqr.com` |

`DB_URL` is wired automatically from the database — leave it.

## Step 5 — Deploy

Click **Apply**. Render builds the Docker image and starts all services. The web
container's `start-web.sh` runs `config:cache`, `route:cache`, `view:cache`, and
`php artisan migrate --force` on every boot, so the schema is created on first deploy.

Watch the **Logs** tab for `Starting Apache on :PORT`.

## Step 6 — Persist uploaded files (important)

`render.yaml` attaches a 1 GB disk at `/var/www/html/storage/app`. **Disks require a
paid plan** (`starter` or higher). Options:

- **Paid plan (simplest):** keep the disk block as-is; `FILESYSTEM_DISK=local` works.
- **Free plan:** remove the `disk:` block and switch to S3-compatible storage
  (AWS S3, Cloudflare R2, Backblaze B2). Set `FILESYSTEM_DISK=s3` and the `AWS_*`
  vars, then the QR/attachment code stores remotely and survives restarts.

> On the free plan with **no** disk and **no** S3, attachments and QR images are lost
> on every redeploy. Don't ship that for real document tracking.

## Step 7 — Seed roles + admin user

Migrations run automatically, but the roles/admin seeder does not. Open the web
service's **Shell** tab in the dashboard and run once:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

Now log in at `https://<your-app>.onrender.com/login` as `admin@speedtraqr.com`
with the `ADMIN_PASSWORD` you set. **Change it immediately.**

## Step 8 — Email (so SLA warnings actually send)

`MAIL_MAILER=log` only writes mail to the log. Pick a provider (Mailgun, Postmark,
Resend, Gmail SMTP) and set on the web + worker + cron services:

```
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=noreply@yourdomain
MAIL_FROM_NAME="SPeED TraQR"
```

## Step 9 — Verify (go-live checklist)

On the live URL, walk through `TESTING.md`. Key checks:

- [ ] `/login` loads, admin login works, password changed
- [ ] Create a document → QR generates and displays
- [ ] Scan IN / OUT works
- [ ] Public `/track/{number}` works without login
- [ ] `APP_DEBUG=false` — no stack traces on errors
- [ ] Redeploy, then confirm a previously-uploaded attachment still opens (disk/S3 works)
- [ ] Wait for the hourly cron run; confirm SLA mail sends (check provider/logs)

---

## Routine updates

`git push` to the deploy branch → Render auto-builds and redeploys all services.
Migrations run automatically on the web service's next boot.

## Cost note

The free tier: web service **sleeps** after 15 min idle (cold starts ~30s), free
Postgres **expires after 90 days**, and disks aren't available. For an office tool
that staff rely on, budget for the `starter` plan on the web service + database.

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| Build fails on `gd` | already handled in Dockerfile; re-check the build log for the failing extension |
| 500 on first load | check **Logs**; usually a missing `APP_KEY` or DB not reachable |
| QR images 404 | disk/S3 not configured (Step 6); on local disk run `php artisan storage:link` in Shell |
| Migrations didn't run | check web logs for `Running migrations`; the entrypoint runs them on boot |
| SLA emails never arrive | `MAIL_*` still on `log` (Step 8), or cron service failing |
| Worker idle | check the `speedtraqr-worker` service logs; it must show `Processing jobs` |
