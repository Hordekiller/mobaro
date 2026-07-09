# مانیفست کامل Assetهای پروژه Mobaro.ir

**تاریخ:** ۲۰ تیر ۱۴۰۵  
**حجم کل:** 4.6MB  
**تعداد کل فایل‌ها:** 78 فایل

---

## 1. تصاویر (39 فایل - 1.3MB)

در `/assets/images/downloaded/`:
- شامل تمام تصاویر از `picsum.photos` و `images.unsplash.com`
- نام‌گذاری تمیز و براساس محل استفاده
- مانیفست کامل در `IMAGES-MANIFEST.md`

---

## 2. فونت‌ها (19 فایل woff2 - 760K)

در `/assets/fonts/downloaded/`:

### Google Fonts (6 فایل)
| فایل | توضیح |
|------|--------|
| `Vazirmatn-Arabic-400-500-700.woff2` | نسخه عربی Vazirmatn با 3 وزن (برای فارسی) |
| `Vazirmatn-Latin-400.woff2` | پشتیبانی لاتین وزن 400 |
| `Vazirmatn-Latin-500.woff2` | پشتیبانی لاتین وزن 500 |
| `Vazirmatn-Latin-700.woff2` | پشتیبانی لاتین وزن 700 |
| `PlayfairDisplay-Latin-700.woff2` | فونت لوگوی انگلیسی وزن 700 |
| `PlayfairDisplay-LatinExt-700.woff2` | Latin Extended support |

### Vazirmatn Full (5 فایل)
| فایل | توضیح |
|------|--------|
| `Vazirmatn-Regular.woff2` | وزن 400 معمولی |
| `Vazirmatn-Medium.woff2` | وزن 500 متوسط |
| `Vazirmatn-Bold.woff2` | وزن 700 پررنگ |
| `Vazirmatn-Light.woff2` | وزن 300 نازک |
| `Vazirmatn-SemiBold.woff2` | وزن 600 نیمه‌پررنگ |

### Vazirmatn Farsi Digits (8 فایل)
| فایل | توضیح |
|------|--------|
| `Vazirmatn-FD-Regular.woff2` | FD وزن 400 |
| `Vazirmatn-FD-Medium.woff2` | FD وزن 500 |
| `Vazirmatn-FD-Bold.woff2` | FD وزن 700 |
| `Vazirmatn-FD-Light.woff2` | FD وزن 300 |
| `Vazirmatn-FD-Thin.woff2` | FD وزن 100 |
| `Vazirmatn-FD-SemiBold.woff2` | FD وزن 600 |
| `Vazirmatn-FD-ExtraBold.woff2` | FD وزن 800 |
| `Vazirmatn-FD-Black.woff2` | FD وزن 900 |

### فایل‌های CSS فونت (4 فایل)
| فایل | منبع |
|------|------|
| `google-fonts.css` | Google Fonts (TTF) |
| `google-fonts-woff2.css` | Google Fonts (WOFF2) |
| `vazirmatn-font-face.css` | Vazirmatn full @font-face |
| `vazirmatn-digits/Vazirmatn-FD-font-face.css` | Vazirmatn FD @font-face |

---

## 3. Font Awesome 6.5.1 (12 فایل - 1.2MB)

در `/assets/libs/fontawesome/`:

### CSS (5 فایل)
| فایل | محتوا |
|------|--------|
| `css/all.min.css` | همه آیکن‌ها (minified) |
| `css/fontawesome.min.css` | هسته اصلی |
| `css/brands.min.css` | برندها |
| `css/solid.min.css` | آیکن‌های solid |
| `css/regular.min.css` | آیکن‌های regular |

### Webfonts (7 فایل)
| فایل | توضیح |
|------|--------|
| `webfonts/fa-solid-900.woff2` | Solid icons (156K) |
| `webfonts/fa-solid-900.ttf` | Solid icons TTF (412K) |
| `webfonts/fa-regular-400.woff2` | Regular icons (28K) |
| `webfonts/fa-regular-400.ttf` | Regular icons TTF |
| `webfonts/fa-brands-400.woff2` | Brand icons (116K) |
| `webfonts/fa-brands-400.ttf` | Brand icons TTF |
| `webfonts/fa-v4compatibility.woff2` | Backward compatibility |

---

## 4. Tailwind CSS (2 فایل - 900K)

در `/assets/libs/tailwind/`:
| فایل | توضیح |
|------|--------|
| `tailwind.js` | Play CDN standard (398K) |
| `tailwind-full.js` | +forms, typography (500K) |

---

## 5. CSS استخراج‌شده از HTML (2 فایل - 32K)

در `/assets/css/`:
| فایل | منبع |
|------|------|
| `home-styles.css` | `<style>` از home.html |
| `profile-styles.css` | `<style>` از Profile.html |
| `home-tailwind-classes.txt` | کلاس‌های Tailwind استفاده‌شده |

---

## 6. JS استخراج‌شده از HTML (2 فایل - 32K)

در `/assets/js/`:
| فایل | منبع |
|------|------|
| `home-scripts.js` | `<script>` از home.html (30K) |
| `profile-scripts.js` | `<script>` از Profile.html (2.4K) |

---

## 7. بدون نیاز به دانلود
- **SVG:** در پروژه وجود ندارد (همه آیکن‌ها Font Awesome هستند)
- **Base64 image:** وجود ندارد
- **PDF/Zip/Video:** وجود ندارد
- **emoji:** ۶ عدد یونیکد (💇 💄 👋 🚀 🛒 🌸) - نیازی به دانلود ندارند

---

## خلاصه کلی

| دسته | تعداد | حجم |
|------|-------|------|
| تصاویر | 39 | 1.3MB |
| فونت‌های woff2 | 19 | 760K |
| Font Awesome | 12 | 1.2MB |
| Tailwind CSS | 2 | 900K |
| CSS سفارشی | 2 | 32K |
| JS سفارشی | 2 | 32K |
| CSS فونت | 4 | 8K |
| **جمع** | **78** | **4.6MB** |
