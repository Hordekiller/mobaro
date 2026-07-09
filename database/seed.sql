-- Mobaro - Seed Data
USE mobaro;

-- Users
INSERT INTO users (id, name, family, phone, password, role, level, points, wallet, avatar) VALUES
(1, 'مدیر', 'سیستم', '09120000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'الماسی', 5000, 2500000, NULL),
(2, 'سارا', 'احمدی', '09123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'طلایی', 320, 450000, 'profile-avatar.jpg');

-- Artists
INSERT INTO artists (id, name, specialty, bio, avatar, instagram) VALUES
(1, 'نازنین شریفی', 'متخصص رنگ و لایت', '۱۲ سال سابقه درخشان در زمینه رنگ مو و مش', 'artist-nazanin.jpg', '#'),
(2, 'سارا ملکی', 'آرایش عروس', 'متخصص میکاپ حرفه‌ای عروس با ۸ سال سابقه', 'artist-sara.jpg', '#');

-- Services
INSERT INTO services (id, title, category, price, duration, description, image, rating, artist_id) VALUES
(1, 'رنگ و لایت', 'رنگ', 380000, '۱۲۰ دقیقه', 'شامل شستشو و مراقبت مو', 'service-hair-color.jpg', 4.9, 1),
(2, 'آرایش عروس', 'آرایش', 1200000, '۱۸۰ دقیقه', 'آرایش کامل + میکاپ حرفه‌ای', 'service-bridal.jpg', 5.0, 2),
(3, 'کوتاهی و شینیون', 'مو', 220000, '۴۵ دقیقه', 'کوتاهی + شینیون', 'service-haircut.jpg', 4.8, 1),
(4, 'فیشیال و پاکسازی', 'پوست', 450000, '۹۰ دقیقه', 'پاکسازی عمیق پوست', 'service-skincare.jpg', 4.9, 2);

-- Products
INSERT INTO products (id, name, category, price, image, stock, rating) VALUES
(1, 'شامپو تقویت کننده', 'مو', 245000, 'product-shampoo.jpg', 15, 4.5),
(2, 'ماسک مو کراتینه', 'مو', 315000, 'product-hair-mask.jpg', 10, 4.7),
(3, 'رژ لب مات', 'آرایش', 89000, 'product-lipstick.jpg', 25, 4.3),
(4, 'کرم مرطوب کننده', 'پوست', 175000, 'product-moisturizer.jpg', 20, 4.6),
(5, 'سشوار حرفه‌ای', 'ابزار', 1250000, 'product-hair-dryer.jpg', 5, 4.8);

-- Courses
INSERT INTO courses (id, title, teacher, type, image, category, duration) VALUES
(1, 'میکاپ حرفه‌ای عروس', 'سارا ملکی', 'offline', 'course-bridal-makeup.jpg', 'آرایش', '۴ جلسه'),
(2, 'کاشت ناخن پیشرفته', 'نازنین شریفی', 'online', 'course-nail-art.jpg', 'ناخن', '۶ جلسه'),
(3, 'مراقبت از پوست', 'دکتر محمدی', 'online', 'course-skincare.jpg', 'پوست', '۳ جلسه');

-- Course Enrollments
INSERT INTO course_enrollments (user_id, course_id, progress) VALUES
(2, 1, 45),
(2, 2, 78),
(2, 3, 20);

-- Hair Models
INSERT INTO hair_models (id, title, category, image) VALUES
(1, 'بابلیز بلند', 'مو', 'model-bob.jpg'),
(2, 'شینیون عروس', 'عروس', 'model-chignon.jpg'),
(3, 'لایت طلایی', 'رنگ', 'model-golden-light.jpg'),
(4, 'چتری کوتاه', 'مو', 'model-bangs.jpg'),
(5, 'آرایش نود', 'آرایش', 'model-nude-makeup.jpg');

-- Tutorials
INSERT INTO tutorials (id, title, category, duration, views, image) VALUES
(1, 'چگونه موهای خود را لایت کنیم؟', 'مو', '۴:۱۲', 12000, 'tutorial-hair-light.jpg'),
(2, 'آرایش روزانه در ۱۰ دقیقه', 'آرایش', '۷:۴۵', 34000, 'tutorial-daily-makeup.jpg');

-- Testimonials
INSERT INTO testimonials (id, name, role, text, rating) VALUES
(1, 'لیلا کریمی', 'مشتری ثابت از ۱۴۰۱', 'از لحظه ورود تا پایان کار، همه چیز عالی بود. آرایشم برای عروسی فوق‌العاده شد و همه از آن تعریف کردند.', 5.0),
(2, 'زهرا محمدی', 'دانشجوی دانشگاه', 'رنگ موی من را به بهترین شکل ممکن انجام دادند. واقعاً از کیفیت کار و رفتار کارکنان راضی هستم.', 4.9),
(3, 'مریم حسینی', 'خانه‌دار', 'آموزش‌های آنلاینشان بسیار کاربردی بود. توانستم در خانه آرایش چشمم را بهبود ببخشم.', 4.8);

-- Appointments
INSERT INTO appointments (user_id, service_id, artist_id, appointment_date, appointment_time, status) VALUES
(2, 1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '۱۴:۴۵', 'confirmed'),
(2, 2, 2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '۱۷:۰۰', 'pending');

-- Orders
INSERT INTO orders (id, user_id, total, status, tracking_code) VALUES
(1, 2, 560000, 'delivered', 'MB-14030201'),
(2, 2, 315000, 'processing', 'MB-14030215'),
(3, 2, 89000, 'shipped', 'MB-14030301');

INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES
(1, 1, 'شامپو تقویت کننده', 245000, 1),
(1, 4, 'کرم مرطوب کننده', 175000, 1),
(1, 3, 'رژ لب مات', 89000, 1),
(2, 2, 'ماسک مو کراتینه', 315000, 1),
(3, 3, 'رژ لب مات', 89000, 1);

-- Transactions
INSERT INTO transactions (user_id, type, amount, description) VALUES
(2, 'wallet_deposit', 500000, 'واریز به کیف پول'),
(2, 'points_earn', 100, 'امتیاز خرید سفارش MB-14030201'),
(2, 'wallet_withdraw', 50000, 'خرید از فروشگاه'),
(2, 'points_earn', 50, 'امتیاز ثبت نوبت'),
(2, 'points_spend', 30, 'استفاده از امتیازات برای تخفیف');

-- Addresses
INSERT INTO addresses (user_id, title, address, city, zip_code, is_default) VALUES
(2, 'خانه', 'تهران، خیابان ولیعصر، کوچه گلستان، پلاک ۳۲، واحد ۷', 'تهران', '۱۹۸۳۶۵۴۲۱۱', 1),
(2, 'محل کار', 'تهران، خیابان انقلاب، خیابان فلسطین، پلاک ۱۲۵', 'تهران', '۱۴۵۶۸۳۲۱۱۴', 0);

-- Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('brand_name', 'موبارو'),
('brand_phone', '۰۲۱-۲۲۸۸۴۲۶۷'),
('brand_address', 'تهران، خیابان ولیعصر، پلاک ۱۲۸'),
('brand_hours', 'شنبه تا پنجشنبه ۹ صبح - ۸ شب'),
('brand_email', 'info@mobaro.ir'),
('brand_instagram', '#'),
('brand_telegram', '#'),
('brand_linkedin', '#'),
('color_primary', '#e11d48'),
('color_primary_dark', '#be185d'),
('color_gold', '#D4AF37'),
('hero_title', 'زیبایی را با ما تجربه کنید'),
('hero_description', 'سالن زیبایی موبارو با بهترین آرایشگران و محصولات حرفه‌ای در خدمت شماست. رزرو آنلاین، آموزش‌های رایگان و فروشگاه آنلاین.');
