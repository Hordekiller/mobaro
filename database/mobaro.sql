-- MySQL dump 10.13  Distrib 8.4.10, for Linux (x86_64)
--
-- Host: localhost    Database: mobaro
-- ------------------------------------------------------
-- Server version	8.4.10-0ubuntu0.26.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addresses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(100) DEFAULT 'خانه',
  `address` text,
  `city` varchar(100) DEFAULT 'تهران',
  `zip_code` varchar(20) DEFAULT '',
  `phone` varchar(20) DEFAULT '',
  `postal_code` varchar(20) DEFAULT '',
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `service_id` int DEFAULT NULL,
  `artist_id` int DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` varchar(20) NOT NULL DEFAULT '',
  `price` decimal(15,0) DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,1,1,1,'2026-07-09','۱۴:۰۰',NULL,'confirmed','2026-07-09 10:36:33',NULL),(2,2,1,1,'2026-07-09','۱۰:۰۰',NULL,'confirmed','2026-07-09 11:39:40',NULL),(3,1,1,1,'2026-07-11','۱۱:۰۰',NULL,'confirmed','2026-07-09 14:19:56',NULL),(4,1,6,5,'2026-07-13','۱۱:۰۰',NULL,'confirmed','2026-07-09 15:06:24',NULL),(5,1,1,5,'2026-07-09','۱۴:۴۵',NULL,'confirmed','2026-07-09 15:27:49',NULL),(6,1,1,5,'2026-07-10','۱۱:۰۰',NULL,'confirmed','2026-07-09 15:51:27',NULL),(7,5,3,5,'2026-07-14','11:00',NULL,'pending','2026-07-10 11:27:52',NULL);
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `artist_services`
--

DROP TABLE IF EXISTS `artist_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `artist_services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `artist_id` int NOT NULL,
  `service_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_artist_service` (`artist_id`,`service_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `artist_services_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `artist_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `artist_services`
--

LOCK TABLES `artist_services` WRITE;
/*!40000 ALTER TABLE `artist_services` DISABLE KEYS */;
INSERT INTO `artist_services` VALUES (2,5,1),(3,5,2),(4,5,3),(5,5,4),(6,5,5),(7,5,6);
/*!40000 ALTER TABLE `artist_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `artists`
--

DROP TABLE IF EXISTS `artists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `artists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `family` varchar(100) DEFAULT '',
  `specialty` varchar(255) DEFAULT '',
  `bio` text,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `instagram` varchar(255) DEFAULT '#',
  `working_hours` varchar(100) DEFAULT '۹ صبح - ۸ شب',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `artists`
--

LOCK TABLES `artists` WRITE;
/*!40000 ALTER TABLE `artists` DISABLE KEYS */;
INSERT INTO `artists` VALUES (4,'روژین','','رنگ مو',':)',NULL,1,'2026-07-09 14:33:09','','همیشه'),(5,'سلام','','سلامی','سلام',NULL,1,'2026-07-09 15:05:39','#','همیشه');
/*!40000 ALTER TABLE `artists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(15,0) NOT NULL DEFAULT '0',
  `min_order` decimal(15,0) DEFAULT '0',
  `max_uses` int DEFAULT '0',
  `used_count` int DEFAULT '0',
  `expires_at` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (1,'MOBARO20','percentage',20,0,100,0,'2027-07-10',1,'2026-07-10 11:29:42'),(2,'WELCOME15','percentage',15,50000,100,0,'2027-07-10',1,'2026-07-10 11:29:42');
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_enrollments`
--

DROP TABLE IF EXISTS `course_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_enrollments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `course_id` int NOT NULL,
  `progress` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_course` (`user_id`,`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_enrollments`
--

LOCK TABLES `course_enrollments` WRITE;
/*!40000 ALTER TABLE `course_enrollments` DISABLE KEYS */;
INSERT INTO `course_enrollments` VALUES (1,4,1,35,'2026-07-10 06:08:51'),(2,5,4,6,'2026-07-10 06:12:13'),(3,1,3,0,'2026-07-10 09:43:27'),(4,5,3,0,'2026-07-10 11:22:25');
/*!40000 ALTER TABLE `course_enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_lessons_completed`
--

DROP TABLE IF EXISTS `course_lessons_completed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_lessons_completed` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `course_id` int NOT NULL,
  `module_index` int NOT NULL DEFAULT '0',
  `lesson_index` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lesson` (`user_id`,`course_id`,`lesson_index`),
  KEY `idx_user_course` (`user_id`,`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_lessons_completed`
--

LOCK TABLES `course_lessons_completed` WRITE;
/*!40000 ALTER TABLE `course_lessons_completed` DISABLE KEYS */;
INSERT INTO `course_lessons_completed` VALUES (1,5,4,0,0,'2026-07-10 11:22:31');
/*!40000 ALTER TABLE `course_lessons_completed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `teacher` varchar(100) DEFAULT '',
  `type` varchar(50) DEFAULT '',
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT '',
  `duration` varchar(20) DEFAULT '',
  `price` int DEFAULT '0',
  `old_price` int DEFAULT '0',
  `rating` decimal(2,1) DEFAULT '0.0',
  `students` int DEFAULT '0',
  `level` varchar(50) DEFAULT 'همه سطوح',
  `is_free` tinyint(1) DEFAULT '0',
  `slug` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text,
  `curriculum` text,
  `audience` text,
  `faqs` text,
  `reviews` text,
  `video_url` varchar(500) DEFAULT NULL,
  `video_type` enum('upload','youtube','aparat') DEFAULT 'upload',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'تکنیک‌های حرفه‌ای رنگ مو','سارا احمدی','online','course-hair-color.jpg','رنگ مو','۱۲ ساعت',2500000,3500000,4.8,342,'متوسط',0,'professional-hair-coloring',1,'2026-07-10 05:40:20','در این دوره جامع با تمام تکنیک‌های رنگ مو از بالیاژ تا سامبره آشنا میشوید. مناسب برای علاقه‌مندان به یادگیری حرفه‌ای رنگ مو.','[{\"title\":\"آشنایی مقدماتی\",\"duration\":\"۴۵ دقیقه\",\"lessons\":[{\"title\":\"معرفی دوره و مدرس\",\"duration\":\"۱۲ دقیقه\"},{\"title\":\"ابزار و تجهیزات مورد نیاز\",\"duration\":\"۱۸ دقیقه\"},{\"title\":\"آماده‌سازی محیط کار\",\"duration\":\"۱۵ دقیقه\"}]},{\"title\":\"تکنیک‌های پایه\",\"duration\":\"۱ ساعت\",\"lessons\":[{\"title\":\"تئوری رنگ‌شناسی\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"ترکیب رنگ‌ها\",\"duration\":\"۱۵ دقیقه\"},{\"title\":\"تکنیک تست رنگ\",\"duration\":\"۱۵ دقیقه\"},{\"title\":\"تمرین عملی\",\"duration\":\"۱۰ دقیقه\"}]},{\"title\":\"تکنیک‌های پیشرفته\",\"duration\":\"۱.۵ ساعت\",\"lessons\":[{\"title\":\"بالیاژ حرفه‌ای\",\"duration\":\"۲۵ دقیقه\"},{\"title\":\"سامبره و اومبره\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"هایلایت فویلی\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"رفع اشکال رنگ\",\"duration\":\"۱۵ دقیقه\"},{\"title\":\"پروژه پایانی\",\"duration\":\"۱۰ دقیقه\"}]}]','[{\"title\":\"علاقه‌مندان به زیبایی\",\"desc\":\"افرادی که می‌خواهند مهارت جدید یاد بگیرند\"},{\"title\":\"آرایشگران حرفه‌ای\",\"desc\":\"برای ارتقاء مهارت‌های تخصصی\"},{\"title\":\"ورزشکاران زیبایی\",\"desc\":\"مسابقات و رقابت‌های حرفه‌ای\"},{\"title\":\"مدیران سالن‌ها\",\"desc\":\"برای گسترش خدمات سالن\"}]','[{\"q\":\"آیا پیش‌نیاز خاصی دارد؟\",\"a\":\"خیر، این دوره از پایه شروع میشود و برای همه سطوح مناسب است.\"},{\"q\":\"آیا گواهی پایان دوره ارائه میشود؟\",\"a\":\"بله، پس از اتمام موفق دوره گواهی معتبر آکادمی موبارو دریافت خواهید کرد.\"},{\"q\":\"چقدر زمان برای مشاهده دوره دارم؟\",\"a\":\"دسترسی به دوره مادام‌العمر است و هر زمان که بخواهید میتوانید محتوا را مشاهده کنید.\"},{\"q\":\"آیا امکان بازگشت وجه وجود دارد؟\",\"a\":\"بله، تا ۳۰ روز پس از خرید در صورت عدم رضایت امکان بازگشت وجه وجود دارد.\"}]','[{\"name\":\"نگین عزیزی\",\"initial\":\"ن\",\"color\":\"rose\",\"rating\":5,\"text\":\"واقعاً عالی بود! توضیحات مدرس خیلی ساده و کاربردی بود. بعد از این دوره توانستم تکنیک‌های جدیدی را در سالنم پیاده کنم.\"},{\"name\":\"سارا محمدی\",\"initial\":\"س\",\"color\":\"emerald\",\"rating\":5,\"text\":\"محتوای بسیار جامعی بود. مخصوصاً بخش تکنیک‌های پیشرفته خیلی مفید بود. پیشنهاد میکنم حتماً شرکت کنید.\"},{\"name\":\"مریم رستمی\",\"initial\":\"م\",\"color\":\"amber\",\"rating\":4,\"text\":\"دوره خوبی بود ولی اگر تمرین‌های عملی بیشتری داشت عالی‌تر میشد. در کل راضی هستم.\"}]',NULL,'upload'),(2,'آموزش جامع میکاپ عروس','نگین محمدی','online','course-bridal-makeup.jpg','میکاپ','۸ ساعت',1800000,2500000,4.9,215,'مبتدی',0,'bridal-makeup-masterclass',1,'2026-07-10 05:40:20','تکنیک‌های میکاپ عروس از صفر تا صد با متریال حرفه‌ای. شامل آماده‌سازی پوست، کانتورینگ، سایه چشم و لب.','[{\"title\":\"مبانی میکاپ\",\"duration\":\"۲ ساعت\",\"lessons\":[{\"title\":\"آماده‌سازی پوست\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"پرایمر و فاندیشن\",\"duration\":\"۲۵ دقیقه\"},{\"title\":\"کانتورینگ صورت\",\"duration\":\"۳۰ دقیقه\"},{\"title\":\"هایلایت و برانزر\",\"duration\":\"۲۵ دقیقه\"},{\"title\":\"تثبیت آرایش\",\"duration\":\"۲۰ دقیقه\"}]},{\"title\":\"آرایش چشم\",\"duration\":\"۲.۵ ساعت\",\"lessons\":[{\"title\":\"سایه چشم تک‌رنگ\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"سایه چشم چند رنگ\",\"duration\":\"۳۰ دقیقه\"},{\"title\":\"خط چشم و بن‌مژه\",\"duration\":\"۲۵ دقیقه\"},{\"title\":\"فرمژه و ریمل\",\"duration\":\"۱۵ دقیقه\"},{\"title\":\"ابرو حرفه‌ای\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"سایه دودی\",\"duration\":\"۲۵ دقیقه\"}]},{\"title\":\"آرایش لب و نهایی\",\"duration\":\"۱.۵ ساعت\",\"lessons\":[{\"title\":\"فرم دهی لب\",\"duration\":\"۱۵ دقیقه\"},{\"title\":\"خط لب و رژ لب\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"آرایش لب براق و مات\",\"duration\":\"۱۵ دقیقه\"},{\"title\":\"آرایش نهایی و فیکس\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"عکاسی از آرایش\",\"duration\":\"۱۵ دقیقه\"}]}]','[{\"title\":\"عروس‌های آینده\",\"desc\":\"برای آماده شدن برای روز عروسی\"},{\"title\":\"آرایشگران مبتدی\",\"desc\":\"شروع حرفه‌ای در میکاپ\"},{\"title\":\"علاقه‌مندان آرایش\",\"desc\":\"یادگیری آرایش حرفه‌ای در خانه\"},{\"title\":\"تشریفات و مجالس\",\"desc\":\"آرایش مناسب مراسم خاص\"}]','[{\"q\":\"آیا به لوازم خاصی نیاز دارم؟\",\"a\":\"خیر، با لوازم ساده و پایه شروع میکنیم و به تدریج لوازم حرفه‌ای را معرفی میکنیم.\"},{\"q\":\"این دوره مناسب مبتدیان است؟\",\"a\":\"بله، این دوره از صفر شروع میشود و هیچ پیش‌نیازی ندارد.\"},{\"q\":\"آیا میتوانم از گواهی استفاده کنم؟\",\"a\":\"بله، گواهی پایان دوره آکادمی موبارو قابل ارائه در سالن‌ها و آموزشگاه‌ها است.\"}]','[{\"name\":\"زهرا حسینی\",\"initial\":\"ز\",\"color\":\"rose\",\"rating\":5,\"text\":\"بهترین دوره میکاپی بود که شرکت کردم. مدرس خیلی صبور و حرفه‌ای بود.\"},{\"name\":\"الهام کریمی\",\"initial\":\"ا\",\"color\":\"emerald\",\"rating\":5,\"text\":\"خیلی چیزها یاد گرفتم که توی هیچ دوره‌ای یاد نگرفته بودم. مخصوصاً بخش کانتورینگ عالی بود.\"},{\"name\":\"نسرین عباسی\",\"initial\":\"ن\",\"color\":\"amber\",\"rating\":4,\"text\":\"محتوا عالی بود ولی کیفیت ویدیوها میتوانست بهتر باشد.\"}]',NULL,'upload'),(3,'مراقبت از پوست و جوانسازی','دکتر مریم رضایی','online','course-skincare.jpg','پوست','۶ ساعت',0,0,4.7,892,'مبتدی',1,'skincare-anti-aging',1,'2026-07-10 05:40:20','آموزش روتین مراقبت از پوست، ماسک‌های خانگی، آشنایی با مواد مؤثر و تکنیک‌های جوانسازی پوست.','[{\"title\":\"شناخت پوست\",\"duration\":\"۱.۵ ساعت\",\"lessons\":[{\"title\":\"انواع پوست\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"مشکلات رایج پوست\",\"duration\":\"۲۵ دقیقه\"},{\"title\":\"شناخت مواد مؤثر\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"تست نوع پوست\",\"duration\":\"۱۵ دقیقه\"}]},{\"title\":\"روتین مراقبتی\",\"duration\":\"۲ ساعت\",\"lessons\":[{\"title\":\"پاکسازی صبح و شب\",\"duration\":\"۱۵ دقیقه\"},{\"title\":\"تونر و سرم\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"مرطوب کننده و ضد آفتاب\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"ماسک‌های خانگی\",\"duration\":\"۲۵ دقیقه\"},{\"title\":\"لایه برداری\",\"duration\":\"۲۰ دقیقه\"}]},{\"title\":\"جوانسازی و تکنیک‌ها\",\"duration\":\"۲.۵ ساعت\",\"lessons\":[{\"title\":\"میکرونیدلینگ خانگی\",\"duration\":\"۲۵ دقیقه\"},{\"title\":\"ماساژ صورت\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"آشنایی با دستگاه‌ها\",\"duration\":\"۳۰ دقیقه\"},{\"title\":\"تغذیه و پوست\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"روتین ضد پیری\",\"duration\":\"۲۵ دقیقه\"}]}]','[{\"title\":\"خانم‌ها و آقایان\",\"desc\":\"برای همه سنین و جنسیت‌ها\"},{\"title\":\"علاقه‌مندان به سلامت پوست\",\"desc\":\"یادگیری روتین مراقبت از پوست\"},{\"title\":\"افراد با مشکلات پوستی\",\"desc\":\"درمان آکنه، لک و چین و چروک\"},{\"title\":\"متخصصان زیبایی\",\"desc\":\"ارتقای دانش حرفه‌ای\"}]','[{\"q\":\"آیا این دوره جایگزین پزشک است؟\",\"a\":\"خیر، این دوره برای مراقبت‌های خانگی طراحی شده و جایگزین مشاوره پزشکی نیست.\"},{\"q\":\"آیا به مواد خاصی نیاز دارم؟\",\"a\":\"خیر، با مواد ساده و در دسترس شروع میکنیم.\"},{\"q\":\"آیا برای مشکلات خاص پوستی مناسب است؟\",\"a\":\"بله، تکنیک‌های متنوعی برای مشکلات مختلف پوستی آموزش داده میشود.\"}]','[{\"name\":\"مینا فتحی\",\"initial\":\"م\",\"color\":\"rose\",\"rating\":5,\"text\":\"خیلی چیزها یاد گرفتم. پوستم بعد از ۲ هفته تغییر محسوسی کرد.\"},{\"name\":\"رضا نوری\",\"initial\":\"ر\",\"color\":\"emerald\",\"rating\":5,\"text\":\"دوره بسیار مفیدی بود. مخصوصاً بخش ماساژ صورت عالی بود.\"},{\"name\":\"سمیرا احمدی\",\"initial\":\"س\",\"color\":\"amber\",\"rating\":4,\"text\":\"محتوا خوب بود ولی اگر ویدیوهای عملی بیشتری داشت بهتر میشد.\"}]',NULL,'upload'),(4,'طراحی و دیزاین ناخن','زهرا کریمی','online','course-nail-art.jpg','ناخن','۱۰ ساعت',1200000,1800000,4.6,179,'همه سطوح',0,'nail-art-design-course',1,'2026-07-10 05:40:20','از مانیکور ساده تا طراحی‌های پیشرفته ناخن با ژل و اکریلیک. شامل ترندهای روز دنیا.','[{\"title\":\"مبانی مانیکور\",\"duration\":\"۲ ساعت\",\"lessons\":[{\"title\":\"ابزار و تجهیزات\",\"duration\":\"۱۵ دقیقه\"},{\"title\":\"آماده‌سازی ناخن\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"مانیکور کلاسیک\",\"duration\":\"۲۵ دقیقه\"},{\"title\":\"پوشش ژلی ساده\",\"duration\":\"۳۰ دقیقه\"},{\"title\":\"تکنیک‌های خشک کردن\",\"duration\":\"۱۵ دقیقه\"}]},{\"title\":\"طراحی و دیزاین\",\"duration\":\"۳ ساعت\",\"lessons\":[{\"title\":\"نقاشی با قلمو\",\"duration\":\"۳۰ دقیقه\"},{\"title\":\"استفاده از استنت\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"سنگ و اکسسوری\",\"duration\":\"۲۵ دقیقه\"},{\"title\":\"ترندهای روز\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"طراحی فرنچ\",\"duration\":\"۲۵ دقیقه\"},{\"title\":\"اکریلیک پایه\",\"duration\":\"۳۰ دقیقه\"},{\"title\":\"ژل اکستنشن\",\"duration\":\"۳۰ دقیقه\"}]},{\"title\":\"پیشرفته و تخصصی\",\"duration\":\"۳ ساعت\",\"lessons\":[{\"title\":\"ناخن شیشه‌ای\",\"duration\":\"۲۵ دقیقه\"},{\"title\":\"کات‌آوت\",\"duration\":\"۳۰ دقیقه\"},{\"title\":\"آمبره ناخن\",\"duration\":\"۲۰ دقیقه\"},{\"title\":\"۳D آرت\",\"duration\":\"۳۵ دقیقه\"},{\"title\":\"تعمیر و پر کردن\",\"duration\":\"۲۰ دقیقه\"}]}]','[{\"title\":\"علاقه‌مندان به ناخن\",\"desc\":\"یادگیری طراحی و دیزاین ناخن\"},{\"title\":\"تکنسین‌های ناخن\",\"desc\":\"ارتقای مهارت‌های حرفه‌ای\"},{\"title\":\"آرایشگران\",\"desc\":\"گسترش خدمات سالن\"},{\"title\":\"کارآفرینان\",\"desc\":\"شروع کسب‌وکار ناخن\"}]','[{\"q\":\"آیا به ابزار خاصی نیاز دارم؟\",\"a\":\"بله، لیست ابزار مورد نیاز در ابتدای دوره ارائه میشود و میتوانید به تدریج تهیه کنید.\"},{\"q\":\"آیا امکان کسب درآمد از این مهارت وجود دارد؟\",\"a\":\"بله، با تکمیل این دوره میتوانید به عنوان تکنسین ناخن فعالیت کنید.\"},{\"q\":\"آیا گواهی معتبر ارائه میشود؟\",\"a\":\"بله، گواهی پایان دوره آکادمی موبارو دریافت خواهید کرد.\"}]','[{\"name\":\"الناز شریفی\",\"initial\":\"ا\",\"color\":\"rose\",\"rating\":5,\"text\":\"خیلی عالی بود! الان میتوانم طراحی‌های حرفه‌ای روی ناخن انجام دهم.\"},{\"name\":\"مهسا تقوی\",\"initial\":\"م\",\"color\":\"emerald\",\"rating\":5,\"text\":\"محتوا بسیار جامع و کاربردی بود. پیشنهاد میکنم حتماً شرکت کنید.\"},{\"name\":\"هانیه رستمی\",\"initial\":\"ه\",\"color\":\"amber\",\"rating\":4,\"text\":\"دوره خوبی بود ولی اگر تمرین‌های عملی بیشتری داشت عالی‌تر میشد.\"}]',NULL,'upload');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorite_models`
--

DROP TABLE IF EXISTS `favorite_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorite_models` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `model_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `favorite_models_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorite_models`
--

LOCK TABLES `favorite_models` WRITE;
/*!40000 ALTER TABLE `favorite_models` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorite_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hair_models`
--

DROP TABLE IF EXISTS `hair_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hair_models` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT '',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hair_models`
--

LOCK TABLES `hair_models` WRITE;
/*!40000 ALTER TABLE `hair_models` DISABLE KEYS */;
INSERT INTO `hair_models` VALUES (1,'مدل موی بلند','model-bangs.jpg','classic',1,'2026-07-09 10:13:59'),(2,'مدل موی کوتاه','model-bob.jpg','modern',1,'2026-07-09 10:13:59'),(3,'مدل موی فر','model-chignon.jpg','classic',1,'2026-07-09 10:13:59'),(4,'مدل موی رنگ شده','model-golden-light.jpg','color',1,'2026-07-09 10:13:59'),(5,'مدل موی خاص','model-nude-makeup.jpg','modern',1,'2026-07-09 10:13:59'),(6,'مدل موی کلاسیک','model-6.jpg','classic',1,'2026-07-09 10:13:59'),(7,'مدل موی سگی','product_1783661153_4ddd27ee.png','ساده',1,'2026-07-10 05:25:53');
/*!40000 ALTER TABLE `hair_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `success` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_identifier` (`identifier`),
  KEY `idx_ip` (`ip_address`),
  KEY `idx_time` (`attempted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter`
--

DROP TABLE IF EXISTS `newsletter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter`
--

LOCK TABLES `newsletter` WRITE;
/*!40000 ALTER TABLE `newsletter` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `price` decimal(12,0) DEFAULT '0',
  `quantity` int DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,'شامپو گیاهی',185000,1),(2,2,1,'شامپو گیاهی',185000,6),(3,3,6,'برس حرفه‌ای',95000,2),(4,3,3,'روغن آرگان',350000,1);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total` decimal(12,0) DEFAULT '0',
  `discount` decimal(15,0) DEFAULT '0',
  `coupon_code` varchar(50) DEFAULT NULL,
  `coupon_discount` decimal(15,0) DEFAULT '0',
  `postal_code` varchar(20) DEFAULT '',
  `status` varchar(50) DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_id` varchar(255) DEFAULT NULL,
  `tracking_code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `address` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,1,185000,0,NULL,0,'','processing','pending',NULL,NULL,'MB-20260709-727','2026-07-09 10:47:51',NULL),(2,1,1110000,0,NULL,0,'','processing','pending',NULL,NULL,'MB-20260709-832','2026-07-09 11:57:49',NULL),(3,1,540000,0,NULL,0,'','processing','pending',NULL,NULL,'MB-20260709-420','2026-07-09 12:36:15',NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(12,0) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT '',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `brand` varchar(100) DEFAULT '',
  `old_price` decimal(15,0) DEFAULT '0',
  `is_new` tinyint(1) DEFAULT '0',
  `is_sale` tinyint(1) DEFAULT '0',
  `reviews` int DEFAULT '0',
  `stock` int NOT NULL DEFAULT '10',
  `rating` decimal(2,1) DEFAULT '4.5',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'شامپو گیاهی','شامپوی طبیعی و بدون سولفات',185000,'product-shampoo.jpg','مراقبت مو',1,'2026-07-09 10:13:59','لورآل',295000,0,1,124,10,4.5),(2,'نرم‌کننده حرفه‌ای','نرم‌کننده فوق‌العاده برای موهای خشک',220000,'product-conditioner.jpg','مراقبت مو',1,'2026-07-09 10:13:59','',0,0,0,0,10,4.5),(3,'روغن آرگان','روغن آرگان خالص برای درخشندگی مو',350000,'product-argan.jpg','روغن‌ها',1,'2026-07-09 10:13:59','',0,0,0,0,10,4.5),(4,'سرم مو','سرم ترمیم‌کننده مو',280000,'product-serum.jpg','مراقبت مو',1,'2026-07-09 10:13:59','',0,0,0,0,10,4.5),(5,'ماسک مو','ماسک ترمیم‌کننده و پروتئینه',195000,'product-mask.jpg','مراقبت مو',1,'2026-07-09 10:13:59','لورآل',380000,0,1,67,10,4.5),(6,'برس حرفه‌ای','برس چوبی با موی طبیعی',95000,'product-brush.jpg','ابزار',1,'2026-07-09 10:13:59','',0,0,0,0,10,4.5);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT '5.0',
  `text` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(12,0) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `artist_id` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `category` varchar(100) DEFAULT '',
  `duration` varchar(50) DEFAULT '',
  `rating` decimal(2,1) DEFAULT '0.0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'کوتاهی مو','کوتاهی مو با جدیدترین متدهای روز دنیا',250000,'service-haircut.jpg',NULL,1,'کوتاهی','۴۵ دقیقه',5.0,'2026-07-09 10:13:59'),(2,'رنگ مو','رنگ مو با برندهای معتبر و مواد باکیفیت',550000,'service-color.jpg',NULL,1,'رنگ','۲ ساعت',5.0,'2026-07-09 10:13:59'),(3,'کراتینه مو','کراتینه مو برای صافی و درخشندگی',800000,'service-keratin.jpg',NULL,1,'کراتینه','۳ ساعت',4.0,'2026-07-09 10:13:59'),(4,'میکاپ','آرایش صورت با بهترین متدها',450000,'service-makeup.jpg',NULL,1,'میکاپ','۱:۳۰ ساعت',5.0,'2026-07-09 10:13:59'),(5,'پدیکور','مراقبت از پاها و ناخن',350000,'service-pedicure.jpg',NULL,1,'ناخن','۱ ساعت',4.0,'2026-07-09 10:13:59'),(6,'مانیکور','مراقبت از ناخن‌ها و طراحی',250000,'service-manicure.jpg',NULL,1,'ناخن','۴۵ دقیقه',5.0,'2026-07-09 10:13:59');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'brand_name','موبارو','2026-07-09 10:13:59','2026-07-09 14:29:18'),(2,'brand_phone','۰۲۱-۲۲۸۸۴۲۶۷','2026-07-09 10:13:59','2026-07-09 14:29:18'),(3,'brand_address','تهران، خیابان ولیعصر، پلاک ۱۲۸','2026-07-09 10:13:59','2026-07-09 14:29:18'),(4,'brand_hours','شنبه تا پنجشنبه ۹ صبح - ۸ شب','2026-07-09 10:13:59','2026-07-09 14:29:18'),(5,'brand_email','info@mobaro.ir','2026-07-09 10:13:59','2026-07-09 14:29:18'),(6,'brand_instagram','#','2026-07-09 10:13:59','2026-07-09 14:29:18'),(7,'brand_telegram','#','2026-07-09 10:13:59','2026-07-09 14:29:18'),(8,'brand_linkedin','#','2026-07-09 10:13:59','2026-07-09 14:29:18'),(9,'color_primary','#e11d48','2026-07-09 10:13:59','2026-07-09 14:29:18'),(10,'color_primary_dark','#be185d','2026-07-09 10:13:59','2026-07-09 14:29:18'),(11,'color_gold','#D4AF37','2026-07-09 10:13:59','2026-07-09 14:29:18'),(12,'hero_title','زیبایی را با ما تجربه کنید','2026-07-09 10:13:59','2026-07-09 15:21:00'),(13,'hero_description','سالن زیبایی موبارو با بهترین آرایشگران و محصولات حرفه‌ای در خدمت شماست. رزرو آنلاین، آموزش‌های رایگان و فروشگاه آنلاین.','2026-07-09 10:13:59','2026-07-09 14:29:18'),(24,'booking_phone','۰۲۱-۲۲۸۸۴۲۶۷','2026-07-10 09:29:58','2026-07-10 09:29:58'),(25,'captcha_enabled_admin','1','2026-07-10 10:45:40','2026-07-10 10:45:40'),(26,'captcha_enabled_booking','1','2026-07-10 10:45:40','2026-07-10 10:45:40'),(27,'captcha_enabled_newsletter','1','2026-07-10 10:45:40','2026-07-10 10:45:40'),(28,'captcha_difficulty','medium','2026-07-10 10:45:40','2026-07-10 10:45:40'),(29,'captcha_question_1','5 + 3','2026-07-10 10:45:40','2026-07-10 10:45:40'),(30,'captcha_question_2','12 - 7','2026-07-10 10:45:40','2026-07-10 10:45:40'),(31,'captcha_question_3','9 + 4','2026-07-10 10:45:40','2026-07-10 10:45:40'),(32,'captcha_question_4','15 - 8','2026-07-10 10:45:40','2026-07-10 10:45:40'),(33,'captcha_question_5','6 + 11','2026-07-10 10:45:40','2026-07-10 10:45:40'),(34,'captcha_question_6','20 - 9','2026-07-10 10:45:40','2026-07-10 10:45:40'),(35,'captcha_question_7','3 + 14','2026-07-10 10:45:40','2026-07-10 10:45:40'),(36,'captcha_question_8','18 - 6','2026-07-10 10:45:40','2026-07-10 10:45:40'),(37,'captcha_question_9','7 + 8','2026-07-10 10:45:40','2026-07-10 10:45:40'),(38,'captcha_question_10','25 - 10','2026-07-10 10:45:40','2026-07-10 10:45:40');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `testimonials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `text` text,
  `role` varchar(100) DEFAULT '',
  `rating` varchar(5) DEFAULT '',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testimonials`
--

LOCK TABLES `testimonials` WRITE;
/*!40000 ALTER TABLE `testimonials` DISABLE KEYS */;
INSERT INTO `testimonials` VALUES (1,'مریم','عالی‌ترین سالنی که تا حالا دیدم! کادر حرفه‌ای و محیط دلنشین','مشتری وفادار','5',1,'2026-07-09 10:13:59',NULL),(2,'سارا','نتیجه کارشون فوق‌العاده بود. حتماً دوباره میام','مشتری ویژه','5',1,'2026-07-09 10:13:59',NULL),(3,'زهرا','خرید از فروشگاه آنلاینشون خیلی راحت و سریعه','مشتری جدید','4',1,'2026-07-09 10:13:59',NULL);
/*!40000 ALTER TABLE `testimonials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) DEFAULT '',
  `amount` decimal(12,0) DEFAULT '0',
  `description` text,
  `payment_id` varchar(255) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,2,'points_earn',50,'امتیاز ثبت‌نام',NULL,'pending','2026-07-09 10:25:08'),(2,3,'points_earn',50,'امتیاز ثبت‌نام',NULL,'pending','2026-07-09 10:25:58'),(3,1,'points_earn',18,'امتیاز خرید سفارش MB-20260709-727',NULL,'pending','2026-07-09 10:47:51'),(4,1,'points_earn',111,'امتیاز خرید سفارش MB-20260709-832',NULL,'pending','2026-07-09 11:57:49'),(5,1,'points_earn',54,'امتیاز خرید سفارش MB-20260709-420',NULL,'pending','2026-07-09 12:36:15'),(6,5,'points_earn',50,'امتیاز ثبت‌نام',NULL,'pending','2026-07-10 06:11:58');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tutorials`
--

DROP TABLE IF EXISTS `tutorials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tutorials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `category` varchar(100) DEFAULT '',
  `duration` varchar(20) DEFAULT '',
  `views` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `video_url` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tutorials`
--

LOCK TABLES `tutorials` WRITE;
/*!40000 ALTER TABLE `tutorials` DISABLE KEYS */;
INSERT INTO `tutorials` VALUES (1,'آموزش کوتاهی مو در خانه','tutorial-1.jpg','https://www.youtube.com/watch?v=demo1','کوتاهی','۱۵:۲۳',1245,1,'2026-07-09 10:13:59',''),(2,'آموزش رنگ موی حرفه‌ای','tutorial-2.jpg','https://www.youtube.com/watch?v=demo2','رنگ','۲۲:۱۰',2340,1,'2026-07-09 10:13:59',''),(3,'آموزش بافت مو','tutorial-3.jpg','https://www.youtube.com/watch?v=demo3','بافت','۱۸:۴۵',890,1,'2026-07-09 10:13:59',''),(4,'بافت','product_1783662232_6ba3ed1b.png',NULL,'بافت','40 سال',231321,1,'2026-07-10 05:43:52','/admin/tutorials');
/*!40000 ALTER TABLE `tutorials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `family` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT '',
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `level` varchar(50) DEFAULT 'bronze',
  `points` int DEFAULT '0',
  `wallet` decimal(12,0) DEFAULT '0',
  `google_id` varchar(255) DEFAULT NULL,
  `google_avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','سیستم','09130209657','admin@mobaro.ir','$2y$12$WG5mdxPOHKhx3IwVqQyXeeQBsEwqS2mr5lCWK5OiQfCGKl1b3DAfO','admin','gold',183,1000000,NULL,NULL,1,'','2026-01-01 06:30:00'),(2,'علی','محمدی','09121111111','','$2y$12$RCBgY6JIK7eDOisw/i1efewEmJcqKuMjXeClPb2G3oBky2IALkFVe','user','silver',50,0,NULL,NULL,1,NULL,'2026-07-09 10:25:08'),(3,'علی','محمدی','09129999999','','$2y$12$ZnwdgqEuyTpj/UKCjD7qk.dGkt2Nvor0hYOrIyPOsUVyZxDMsdgTq','user','silver',50,0,NULL,NULL,1,NULL,'2026-07-09 10:25:58'),(4,'تست','کاربر','09120000001','','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','user','',0,0,NULL,NULL,1,NULL,'2026-07-10 06:08:36'),(5,'احمد','سارایی','9333347128','','$2y$12$jxlJrTDriYfucKq7rnD1G.Akt/uRvAPIaTb7rQ1OWowzgkXeaRm9W','user','silver',50,0,NULL,NULL,1,NULL,'2026-07-10 06:11:58');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlist` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist`
--

LOCK TABLES `wishlist` WRITE;
/*!40000 ALTER TABLE `wishlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlist` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-10 18:12:37
