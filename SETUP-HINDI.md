# ReviewFlow (Laravel) — Setup Guide (Hindi)

Yeh project **Laravel 11 + Blade + MySQL** par bana hai. Aapke XAMPP machine par chalega.
Docker ki zaroorat **nahi** hai.

---

## Step 0 — Zaroori cheezein (ek baar)

Aapke computer par yeh hona chahiye:
- **PHP 8.2 ya naya** — XAMPP ke saath aata hai (`C:\xampp\php`). Check: `php -v`
- **Composer** — https://getcomposer.org/download se install karo. Check: `composer -V`
- **XAMPP** — MySQL chal raha ho (control panel me MySQL green).

> Agar `php` ya `composer` command terminal me nahi chalti, to XAMPP ka php folder
> (`C:\xampp\php`) ko Windows PATH me add karna padega.

---

## Step 1 — Project nikaalo

Zip ko unzip karo, maan lo yahaan: `D:\reviewflow-laravel`

Terminal (CMD/PowerShell) us folder me kholo:
```
cd D:\reviewflow-laravel
```

---

## Step 2 — Dependencies install karo

```
composer install
```
(Pehli baar thoda time lega — Laravel + packages download honge.)

---

## Step 3 — .env file banao

```
copy .env.example .env
php artisan key:generate
```

`key:generate` apne aap `APP_KEY` bhar dega.

---

## Step 4 — Database banao (phpMyAdmin)

1. Browser me kholo: `http://localhost/phpmyadmin`
2. **New** par click → database name: **reviewflow** → Create.

`.env` me database settings pehle se XAMPP ke liye sahi hain:
```
DB_DATABASE=reviewflow
DB_USERNAME=root
DB_PASSWORD=
```
(XAMPP me root ka password blank hota hai — isiliye khaali chhoda hai.)

---

## Step 5 — Tables banao + demo data daalo

```
php artisan migrate
php artisan db:seed
```

Yeh saari tables banayega aur demo users/client/location daal dega.

---

## Step 6 — Server chalao

```
php artisan serve
```

Browser me kholo: **http://localhost:8000**

---

## Login (demo accounts)

| Role        | Email             | Password     |
|-------------|-------------------|--------------|
| Super Admin | admin@demo.com    | admin123     |
| Agency Owner| owner@demo.com    | password123  |
| Staff       | staff@demo.com    | staff123     |

- **admin@demo.com** se login karoge → seedha **Admin Panel** khulega (Users, Billing).
- **owner@demo.com** se login karoge → normal **Dashboard** khulega.

---

## Pehli baar kya try karo

1. Login karo `owner@demo.com / password123` se.
2. Left menu me **Reviews** → upar **"Sync from Google"** dabao.
   → 6 demo reviews aa jaayenge, har ek ka sentiment (Positive/Negative/Neutral) AI ne laga diya.
3. Kisi review par **"Draft AI reply"** dabao → AI ek reply likh dega → **Post reply**.
4. **Leads**, **Social**, **WhatsApp**, **Ads** — sab pages chal rahe hain, data add kar ke dekho.
5. **My Profile** → photo upload aur password change.

---

## AI ko "asli" banane ke liye (optional, free)

Abhi AI **local logic** se chal raha hai (bina key ke bhi kaam karta hai).
Asli Google Gemini AI ke liye:

1. Free key lo: https://aistudio.google.com/app/apikey
2. `.env` me daalo:
   ```
   GEMINI_API_KEY=yahan_apni_key_paste_karo
   ```
3. `php artisan serve` dobara chalao. Bas — ab AI replies asli Gemini se aayenge.

---

## Agar koi error aaye

- **"could not find driver" (mysql)** → XAMPP ke `php.ini` me `extension=pdo_mysql` aur
  `extension=mysqli` ke aage ka `;` hata do, phir server restart.
- **"Access denied for user root"** → `.env` me `DB_PASSWORD` aapke MySQL password se match karna chahiye.
  XAMPP me aam taur par blank hota hai.
- **migrate par "Base table already exists"** → database `reviewflow` me purani tables hain.
  phpMyAdmin me database drop kar ke dobara banao, phir `php artisan migrate`.
- **Koi bhi aur error** → terminal ka pura error mujhe bhej do, main fix bata dunga.

---

## Aage kya banega (roadmap)

Yeh foundation hai. Agle steps (ek-ek karke):
1. **Razorpay payment** — aapki test key se: plan kharido → pay → plan activate.
2. **Asli Google connect (OAuth)** — "Connect Google" dabate hi asli reviews/ads
   apne aap aane lagein (abhi mock demo data hai).
3. Meta / WhatsApp connect.

Bas terminal me jo bhi error aaye wo bhej dena, ya "ab Razorpay jodo" bolo — agla step shuru karte hain.
