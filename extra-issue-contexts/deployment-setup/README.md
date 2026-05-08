# Deployment Setup — Jeep Tracker API

**Server:** u867165545@191.101.230.55 (port 65002)
**Server path:** `~/domains/eishipartners.com/public_html/api`
**GitHub repo:** https://github.com/colewan-design/jeep-tracker
**Branch:** `main`

---

## One-Time Setup (Already Done — 2026-05-08)

This was run once on the server to connect the existing files to GitHub:

```bash
# SSH into server
ssh -p 65002 u867165545@191.101.230.55

# Navigate to API folder
cd ~/domains/eishipartners.com/public_html/api

# Back up .env before anything (production credentials live here, never in git)
cp .env .env.backup

# Initialize git and connect to GitHub
git init
git remote add origin https://github.com/colewan-design/jeep-tracker.git

# Fetch and overwrite all files with what's on GitHub
git fetch origin main
git reset --hard origin/main
```

> `.env` is in `.gitignore` so Git never touches it. It stays on the server permanently.

---

## Deploying Changes (Every Time Moving Forward)

### Step 1 — Push from your local machine
```powershell
git push origin main
```

### Step 2 — SSH into server and pull
```bash
ssh -p 65002 u867165545@191.101.230.55
cd ~/domains/eishipartners.com/public_html/api
git pull origin main
php artisan config:cache
php artisan route:cache
```

---

## Optional — One-Command Deploy Script

Create `deploy.ps1` in the project root on your local machine:

```powershell
git push origin main
ssh -p 65002 u867165545@191.101.230.55 "cd ~/domains/eishipartners.com/public_html/api && git pull origin main && php artisan config:cache && php artisan route:cache"
```

Run it with:
```powershell
.\deploy.ps1
```

---

## Important Notes

- **Never commit `.env`** — production credentials (DB, Reverb keys) live only on the server
- **If you change `.env` on the server**, also update `.env.backup` manually: `cp .env .env.backup`
- **After adding new routes**, `php artisan route:cache` is required or new routes won't work
- **After changing `.env` values**, run `php artisan config:cache` on the server
- **After new migrations**, run `php artisan migrate` on the server:
  ```bash
  ssh -p 65002 u867165545@191.101.230.55 "cd ~/domains/eishipartners.com/public_html/api && php artisan migrate --force"
  ```
