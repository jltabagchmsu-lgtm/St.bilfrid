# NewConstuc.FIRM - Construction Project Management & Monitoring System

A Laravel-powered Management & Monitoring System tailored for construction firms to track active projects, multi-trade progression (Structural, Electrical, Piping/Plumbing), Land & Floor Area specifications, Bills of Materials (BOM), materials inventory, personnel, and internal financial ledgers.

---

## 🚀 How to Run the System

### Option A: Laravel Artisan Server (Standard)
```powershell
php artisan serve
```

---

### Option B: Caddy Web Server (High Performance)

#### 1. Start PHP FastCGI Backend
In a terminal / PowerShell window:
```powershell
$env:PHP_FCGI_MAX_REQUESTS = "0"
$env:PHP_FCGI_CHILDREN = "8"
& 'C:\xampp\php\php-cgi.exe' -b 127.0.0.1:9000
```
*(On Linux: `sudo systemctl start php8.1-fpm`)*

#### 2. Start Caddy Web Server
In a second terminal / PowerShell window:
```powershell
cd "c:\Users\Carin Benjamin\Desktop\NewConstuc.FIRM"
caddy run --config Caddyfile
```

#### 3. Open in Browser
Navigate to:
👉 **[http://localhost:8000](http://localhost:8000)** or **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🐳 Docker / FrankenPHP Deployment

You can also run the application inside Docker with Caddy / FrankenPHP:
```powershell
docker compose up --build -d
```
The application will be live at `http://localhost:8000`.

---

## 🔑 Default Login Credentials

| Role | Email | Password |
|---|---|---|
| **Master Administrator** | `admin@newconstuc.firm` | `admin123` |
| **Roofing Transfer Officer** | `roofing@newconstuc.firm` | `roofing123` |
| **Windows & Doors Transfer Officer** | `windows.doors@newconstuc.firm` | `windows123` |

---

## 📖 Complete Documentation
For full details on every module, please see the [SYSTEM_GUIDE.md](file:///c:/Users/Carin%20Benjamin/Desktop/NewConstuc.FIRM/SYSTEM_GUIDE.md).
