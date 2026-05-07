# Jeep Tracker API

Laravel 11 REST API with Sanctum authentication for GPS/Jeep tracking.

---

## 🚀 Deployment to Hostinger (Step-by-Step)

### Step 1 — Install dependencies locally (on your PC)

Make sure you have **Composer** installed, then run in this project folder:

```bash
composer install --optimize-autoloader --no-dev
```

This generates the `vendor/` folder which is required.

### Step 2 — Set up your .env

Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

Fill in your Hostinger database credentials:
```
DB_HOST=127.0.0.1
DB_DATABASE=your_hostinger_db_name
DB_USERNAME=your_hostinger_db_user
DB_PASSWORD=your_hostinger_db_password
```

Generate your app key:
```bash
php artisan key:generate
```

Copy the generated `APP_KEY` value from your `.env`.

### Step 3 — Upload to Hostinger

Upload the **entire project folder** contents into `public_html/api/` via Hostinger File Manager or FTP.

Your structure should be:
```
public_html/
├── (your Vue website)
└── api/
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/         ← jeep-tracker.eishipartners.com points here
    ├── routes/
    ├── storage/
    ├── vendor/
    └── .env
```

### Step 4 — Set folder permissions

In Hostinger File Manager, set these folders to **755**:
- `storage/`
- `bootstrap/cache/`

### Step 5 — Run migrations via Hostinger Terminal

In hPanel go to **Advanced → SSH Access** or use the Terminal:

```bash
cd public_html/api
php artisan migrate
```

---

## 📡 API Endpoints

### Auth
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register new user |
| POST | `/api/login` | Login, returns token |
| POST | `/api/logout` | Logout (requires token) |
| GET  | `/api/me` | Get current user |

### Jeeps
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | `/api/jeeps` | List all jeeps with latest location |
| POST   | `/api/jeeps` | Add a new jeep |
| GET    | `/api/jeeps/{id}` | Get jeep details |
| PUT    | `/api/jeeps/{id}` | Update jeep |
| DELETE | `/api/jeeps/{id}` | Delete jeep |

### Location Tracking
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/jeeps/{id}/location` | Update jeep GPS location |
| GET  | `/api/jeeps/{id}/location` | Get current location |
| GET  | `/api/jeeps/{id}/location/history` | Get location history |

### Trips
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET    | `/api/jeeps/{id}/trips` | List trips for a jeep |
| POST   | `/api/jeeps/{id}/trips` | Start a new trip |
| GET    | `/api/trips/{id}` | Get trip details |
| PUT    | `/api/trips/{id}` | Update/complete trip |
| DELETE | `/api/trips/{id}` | Delete trip |

---

## 🔐 Authentication

All protected routes require a Bearer token in the header:

```
Authorization: Bearer your-token-here
```

---

## 📦 Location Payload Example

```json
POST /api/jeeps/1/location
{
    "latitude": 14.5995,
    "longitude": 120.9842,
    "speed": 25.5,
    "heading": 180,
    "accuracy": 10
}
```
