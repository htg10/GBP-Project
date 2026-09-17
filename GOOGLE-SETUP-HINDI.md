# Google Setup Guide (Hindi) — ReviewFlow

Yeh guide batati hai ki **asli Google reviews** laane ke liye kya-kya karna hai.

Do hisse hain:
- **A. Code** — ho chuka hai (Connect button, OAuth flow, real API calls). Kuch nahi karna.
- **B. Google Cloud Console** — yeh **aapko** karna hai (neeche steps).

> Zaroori baat: jab tak Google connect nahi hota, app **demo data** pe chalta rahega —
> kuch tootega nahi. Connect hote hi asli reviews aane lagenge.

---

## Kya hoga jab sab set ho jayega

1. Kisi client ke page par **"Connect Google"** button dabao.
2. Google ka login/permission screen khulega → apna business Google account chuno → Allow.
3. Wapas app me aa jaoge, client ab **"Connected"** dikhega.
4. **Reviews → Sync from Google** dabate hi us client ke **asli reviews** aa jayenge,
   AI unka sentiment lagayega, aur aap reply karke seedhe Google par post kar sakte ho.

---

## Part B — Google Cloud Console setup

### Step 1 — Project banao
1. Kholo: https://console.cloud.google.com/
2. Upar project dropdown → **New Project** → naam do (e.g. "ReviewFlow") → Create.

### Step 2 — APIs enable karo
Left menu → **APIs & Services → Library**. In sab ko search karke **Enable** karo:
- **Google My Business Account Management API**
- **My Business Business Information API**
- **Google My Business API** (yeh "Additional APIs" me hoti hai — reviews/posts isi se chalte hain)

> Agar "Google My Business API" enable karne ka option na dikhe, to woh **request/approval**
> maangti hai (neeche Step 5 dekho).

### Step 3 — OAuth consent screen
Left menu → **APIs & Services → OAuth consent screen**:
1. User type: **External** → Create.
2. App name, apni email bharो.
3. **Scopes** add karo: `.../auth/business.manage`
4. **Test users** me apni Google email add karo (jab tak app "Testing" me hai, sirf yeh
   emails connect kar payenge — aapke liye kaafi hai).
5. Save.

### Step 4 — Credentials (Client ID + Secret) — YEH SABSE ZAROORI
Left menu → **APIs & Services → Credentials**:
1. **+ Create Credentials → OAuth client ID**
2. Application type: **Web application**
3. **Authorized redirect URIs** me EXACTLY yeh daalo:
   ```
   http://localhost:8000/google/callback
   ```
4. Create → ab aapko milega **Client ID** aur **Client Secret**. Copy kar lo.

### Step 5 — .env me daalo
`D:\reviewflow-laravel\.env` kholo, yeh 3 line bharो:
```
GOOGLE_CLIENT_ID=yahan_client_id_paste_karo
GOOGLE_CLIENT_SECRET=yahan_client_secret_paste_karo
GOOGLE_REDIRECT_URI=http://localhost:8000/google/callback
```
Save karo, phir server restart:
```
php artisan serve
```

### Step 6 — Connect karo
App me login → **Clients** → koi client kholo → **Connect Google** → Google screen pe Allow.
Bas! Ab **Reviews → Sync from Google** asli reviews layega.

---

## Business Profile API "approval" ke baare me (important)

- Google ki **My Business API (reviews/posts wali)** "restricted" hai. Naye projects ko
  Google se **access request** karna padta hai (ek form): 
  https://developers.google.com/my-business/content/prereqs
- Form bharne ke baad approval me **kuch din se hafte** lag sakte hain.
- Jab tak approval nahi milta:
  - OAuth connect **chal jayega** (login, token sab),
  - par reviews list karne par Google **khali** ya **permission error** de sakta hai.
  - Us haalat me app apne aap demo data dikhata rahega — kuch tootega nahi.

Yaani: **abhi connect ka poora flow test kar sakte ho**, aur approval milte hi asli
reviews apne aap aane lagenge — code me kuch nahi badalna padega.

---

## Agar error aaye

- **redirect_uri_mismatch** → Step 4 me daala URI aur `.env` ka `GOOGLE_REDIRECT_URI`
  bilkul same hone chahiye (`http://localhost:8000/google/callback`).
- **access_denied / this app isn't verified** → Step 3 me apni email **Test users** me
  add karo. Testing mode me sirf woh emails allowed hain.
- **Connect ke baad reviews khali** → matlab OAuth to ho gaya, par My Business API access
  abhi approve nahi hua (upar wala section). Normal hai — approval ka wait karo.
- **Koi aur error** → terminal / browser ka poora error mujhe bhej do, fix bata dunga.
