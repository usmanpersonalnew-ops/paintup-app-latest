CREATE TABLE IF NOT EXISTS `migrations` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `migration` VARCHAR(255) NOT NULL, `batch` INT NOT NULL);
INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2026_02_01_070417_create_master_surfaces_table',1);
INSERT INTO migrations VALUES(5,'2026_02_01_070451_create_master_products_table',1);
INSERT INTO migrations VALUES(6,'2026_02_01_070611_create_product_surface_links_table',1);
INSERT INTO migrations VALUES(7,'2026_02_01_070656_create_master_painting_systems_table',1);
INSERT INTO migrations VALUES(8,'2026_02_01_070734_create_quotes_table',1);
INSERT INTO migrations VALUES(9,'2026_02_01_070809_create_quote_zones_table',1);
INSERT INTO migrations VALUES(10,'2026_02_01_080000_create_projects_table',1);
INSERT INTO migrations VALUES(11,'2026_02_01_090000_add_role_to_users_table',1);
INSERT INTO migrations VALUES(12,'2026_02_01_100000_create_inquiries_table',1);
INSERT INTO migrations VALUES(13,'2026_02_01_110000_create_project_rooms_table',1);
INSERT INTO migrations VALUES(14,'2026_02_01_110001_add_fields_to_project_rooms_table',1);
INSERT INTO migrations VALUES(15,'2026_02_01_120000_create_quote_items_table',1);
INSERT INTO migrations VALUES(16,'2026_02_01_130000_create_master_services_table',1);
INSERT INTO migrations VALUES(17,'2026_02_01_130001_create_quote_services_table',1);
INSERT INTO migrations VALUES(18,'2026_02_01_140000_create_quote_media_table',1);
INSERT INTO migrations VALUES(19,'2026_02_01_150000_add_project_room_id_to_quote_services_and_media',1);
INSERT INTO migrations VALUES(20,'2026_02_01_160000_remove_duplicate_zone_columns',1);
INSERT INTO migrations VALUES(21,'2026_02_01_170000_standardize_foreign_keys',1);
INSERT INTO migrations VALUES(22,'2026_02_01_191759_add_manual_area_to_quote_items_table',1);
INSERT INTO migrations VALUES(23,'2026_02_01_200000_add_measurements_to_quote_services',1);
INSERT INTO migrations VALUES(24,'2026_02_01_210000_rename_zone_id_to_room_id',1);
INSERT INTO migrations VALUES(25,'2026_02_07_145000_make_custom_name_nullable_in_quote_services',1);
INSERT INTO migrations VALUES(26,'2026_02_08_000000_add_public_token_to_projects',1);
INSERT INTO migrations VALUES(27,'2026_02_08_064741_add_total_amount_to_projects_table',1);
INSERT INTO migrations VALUES(28,'2026_02_08_100000_create_customers_table',1);
INSERT INTO migrations VALUES(29,'2026_02_08_100001_create_customer_otps_table',1);
INSERT INTO migrations VALUES(30,'2026_02_08_120000_add_payment_columns_to_projects_table',1);
INSERT INTO migrations VALUES(31,'2026_02_08_130000_add_milestone_payment_columns_to_projects_table',1);
INSERT INTO migrations VALUES(32,'2026_02_08_140000_consolidate_payment_columns',1);
INSERT INTO migrations VALUES(33,'2026_02_08_150000_add_missing_payment_columns_to_projects',2);
INSERT INTO migrations VALUES(34,'2026_02_08_172635_add_supervisor_id_to_projects_table',3);
INSERT INTO migrations VALUES(35,'2026_02_09_000000_add_user_fields',4);
INSERT INTO migrations VALUES(36,'2026_02_09_100000_add_work_status_to_projects_table',5);
INSERT INTO migrations VALUES(37,'2026_02_09_200000_create_project_photos_table',6);
INSERT INTO migrations VALUES(38,'2026_02_09_210000_add_stage_to_project_photos',7);
INSERT INTO migrations VALUES(39,'2026_02_09_220000_create_zone_photos_table',8);
INSERT INTO migrations VALUES(40,'2026_02_09_220000_update_customer_otps_table',9);
INSERT INTO migrations VALUES(41,'2026_02_10_000000_create_coupons_table',10);
INSERT INTO migrations VALUES(42,'2026_02_10_000001_add_coupon_fields_to_projects_table',10);
INSERT INTO migrations VALUES(43,'2026_02_10_100000_add_gst_columns_to_projects',11);
INSERT INTO migrations VALUES(44,'2026_02_10_200000_add_warranty_months_to_master_painting_systems',12);
INSERT INTO migrations VALUES(45,'2026_02_10_210000_create_warranties_table',12);
INSERT INTO migrations VALUES(46,'2026_02_11_000000_create_settings_table',13);
INSERT INTO migrations VALUES(47,'2026_02_11_100000_create_milestone_payments_table',14);
INSERT INTO migrations VALUES(48,'2026_02_11_100000_add_payment_columns_to_projects',15);
INSERT INTO migrations VALUES(49,'2026_02_11_000000_create_project_billing_details_table',16);
INSERT INTO migrations VALUES(50,'2026_02_11_100000_add_remarks_to_master_services_table',17);
INSERT INTO migrations VALUES(51,'2026_02_11_100001_add_remarks_to_quote_services_table',18);
INSERT INTO migrations VALUES(52,'2026_02_11_120000_fix_milestone_amounts',19);
INSERT INTO migrations VALUES(53,'2026_02_11_130000_add_payment_fields_to_milestone_payments',20);
INSERT INTO migrations VALUES(54,'2026_02_11_100000_add_profile_fields_to_customers_table',21);
INSERT INTO migrations VALUES(55,'2026_02_12_000000_create_settings_table',22);
CREATE TABLE IF NOT EXISTS `users` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `name` VARCHAR(255) NOT NULL, `email` VARCHAR(255) NOT NULL, `email_verified_at` DATETIME, `password` VARCHAR(255) NOT NULL, `remember_token` VARCHAR(255), `created_at` DATETIME, `updated_at` DATETIME, `role` VARCHAR(255) NOT NULL DEFAULT 'SUPERVISOR', `phone` VARCHAR(255), `status` VARCHAR(255) CHECK (`status` in ('ACTIVE', 'INACTIVE')) NOT NULL DEFAULT 'ACTIVE', `last_login_at` DATETIME);
INSERT INTO users VALUES(1,'Admin','admin@paintup.in',NULL,'$2y$12$Rp1tRvITbFAV1bbDfVrkTecFbcTEvQW5xoUv/apFFPPQslnJIG8gC',NULL,'2026-02-08 15:14:19','2026-02-09 06:09:48','ADMIN','N/A','ACTIVE',NULL);
INSERT INTO users VALUES(2,'Usman Khan','usman@paintup.in',NULL,'$2y$12$QL0yVv5k9x7s1GcMJhwKheqoqrpOM9BhjM0gA3IU8CjLVdrAxgj3y','OVJf5eps6H25AM4tu4szzrLgjgdqB6rf4bL53u6MqY0L2x66eEoX3i9FlxkO','2026-02-08 15:14:19','2026-02-14 11:21:22','SUPERVISOR','N/A','ACTIVE',NULL);
INSERT INTO users VALUES(3,'Rahul Supervisor','rahul@paintup.in',NULL,'$2y$12$Wgpb7nyYCXcqPG4Dy5qlGu1vyZvWtkzQglRaqm8jEkmPCzhmKIJRa',NULL,'2026-02-08 15:14:19','2026-02-09 06:13:28','SUPERVISOR','N/A','ACTIVE',NULL);
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (`email` VARCHAR(255) NOT NULL, `token` VARCHAR(255) NOT NULL, `created_at` DATETIME, PRIMARY KEY (`email`));
CREATE TABLE IF NOT EXISTS `sessions` (`id` VARCHAR(255) NOT NULL, `user_id` INT, `ip_address` VARCHAR(255), `user_agent` TEXT, `payload` TEXT NOT NULL, `last_activity` INT NOT NULL, PRIMARY KEY (`id`));
CREATE TABLE IF NOT EXISTS `cache` (`key` VARCHAR(255) NOT NULL, `value` TEXT NOT NULL, `expiration` INT NOT NULL, PRIMARY KEY (`key`));
CREATE TABLE IF NOT EXISTS `cache_locks` (`key` VARCHAR(255) NOT NULL, `owner` VARCHAR(255) NOT NULL, `expiration` INT NOT NULL, PRIMARY KEY (`key`));
CREATE TABLE IF NOT EXISTS `jobs` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `queue` VARCHAR(255) NOT NULL, `payload` TEXT NOT NULL, `attempts` INT NOT NULL, `reserved_at` INT, `available_at` INT NOT NULL, `created_at` INT NOT NULL);
CREATE TABLE IF NOT EXISTS `job_batches` (`id` VARCHAR(255) NOT NULL, `name` VARCHAR(255) NOT NULL, `total_jobs` INT NOT NULL, `pending_jobs` INT NOT NULL, `failed_jobs` INT NOT NULL, `failed_job_ids` TEXT NOT NULL, `options` TEXT, `cancelled_at` INT, `created_at` INT NOT NULL, `finished_at` INT, PRIMARY KEY (`id`));
CREATE TABLE IF NOT EXISTS `failed_jobs` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `uuid` VARCHAR(255) NOT NULL, `connection` TEXT NOT NULL, `queue` TEXT NOT NULL, `payload` TEXT NOT NULL, `exception` TEXT NOT NULL, `failed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS `master_surfaces` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `name` VARCHAR(255) NOT NULL, `category` VARCHAR(255) CHECK (`category` in ('INTERIOR', 'EXTERIOR', 'BOTH')) NOT NULL, `unit_type` VARCHAR(255) CHECK (`unit_type` in ('AREA', 'LINEAR', 'COUNT', 'LUMPSUM')) NOT NULL, `created_at` DATETIME, `updated_at` DATETIME);
INSERT INTO master_surfaces VALUES(1,'Interior Wall','INTERIOR','AREA','2026-02-08 15:14:19','2026-02-08 15:14:19');
INSERT INTO master_surfaces VALUES(2,'Ceiling','INTERIOR','AREA','2026-02-08 15:14:19','2026-02-08 15:14:19');
CREATE TABLE IF NOT EXISTS `master_products` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `name` VARCHAR(255) NOT NULL, `brand` VARCHAR(255) NOT NULL, `tier` VARCHAR(255) CHECK (`tier` in ('ECONOMY', 'PREMIUM', 'LUXURY', 'ULTRA_LUXURY')) NOT NULL, `created_at` DATETIME, `updated_at` DATETIME);
INSERT INTO master_products VALUES(1,'Royal Glitz','Asian Paints','PREMIUM','2026-02-08 15:16:42','2026-02-11 10:35:50');
CREATE TABLE IF NOT EXISTS `product_surface_links` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `product_id` INT NOT NULL, `surface_id` INT NOT NULL, `created_at` DATETIME, `updated_at` DATETIME);
INSERT INTO product_surface_links VALUES(1,1,1,NULL,NULL);
INSERT INTO product_surface_links VALUES(2,1,2,NULL,NULL);
CREATE TABLE IF NOT EXISTS `master_painting_systems` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `product_id` INT NOT NULL, `system_name` VARCHAR(255) NOT NULL, `process_remarks` TEXT, `base_rate` DECIMAL(10,2) NOT NULL, `created_at` DATETIME, `updated_at` DATETIME, `warranty_months` INT NOT NULL DEFAULT '0');
INSERT INTO master_painting_systems VALUES(8,1,'Fresh Painting','2 Primer, 2 Paint',40,'2026-02-14 08:47:19','2026-02-14 08:47:19',12);
CREATE TABLE IF NOT EXISTS `quotes` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `customer_name` VARCHAR(255), `customer_phone` VARCHAR(255), `status` VARCHAR(255) CHECK (`status` in ('DRAFT', 'SENT', 'APPROVED')) NOT NULL DEFAULT 'DRAFT', `created_at` DATETIME, `updated_at` DATETIME);
CREATE TABLE IF NOT EXISTS `quote_zones` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `quote_id` INT NOT NULL, `name` VARCHAR(255) NOT NULL, `zone_type` VARCHAR(255) CHECK (`zone_type` in ('INTERIOR', 'EXTERIOR')) NOT NULL, `default_length` DECIMAL(10,2), `default_breadth` DECIMAL(10,2), `default_height` DECIMAL(10,2), `created_at` DATETIME, `updated_at` DATETIME);
CREATE TABLE IF NOT EXISTS `inquiries` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `name` VARCHAR(255) NOT NULL, `phone` VARCHAR(255) NOT NULL, `email` VARCHAR(255), `city` VARCHAR(255), `source` VARCHAR(255) NOT NULL, `status` VARCHAR(255) CHECK (`status` in ('NEW', 'CALLED', 'VISIT_BOOKED')) NOT NULL DEFAULT 'NEW', `created_at` DATETIME, `updated_at` DATETIME);
CREATE TABLE IF NOT EXISTS `project_rooms` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `project_id` INT NOT NULL, `name` VARCHAR(255) NOT NULL, `created_at` DATETIME, `updated_at` DATETIME, `type` VARCHAR(255) NOT NULL DEFAULT 'INTERIOR', `length` DECIMAL(10,2), `breadth` DECIMAL(10,2), `height` DECIMAL(10,2), FOREIGN KEY(`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE);
INSERT INTO project_rooms VALUES(1,1,'Bedroom','2026-02-08 15:17:49','2026-02-08 15:17:49','INTERIOR',NULL,NULL,NULL);
INSERT INTO project_rooms VALUES(2,2,'Bedroom','2026-02-08 16:30:36','2026-02-08 16:30:36','INTERIOR',NULL,NULL,NULL);
INSERT INTO project_rooms VALUES(3,3,'Bedroom','2026-02-10 18:23:17','2026-02-10 18:23:17','INTERIOR',NULL,NULL,NULL);
INSERT INTO project_rooms VALUES(4,4,'Bedroom','2026-02-11 11:53:57','2026-02-11 11:53:57','INTERIOR',20,20,20);
INSERT INTO project_rooms VALUES(5,4,'Bedroom 2','2026-02-11 11:55:49','2026-02-11 11:56:00','INTERIOR',20,20,20);
INSERT INTO project_rooms VALUES(6,5,'Master Bedroom','2026-02-14 11:23:22','2026-02-14 11:23:22','INTERIOR',20,10,14);
CREATE TABLE IF NOT EXISTS `quote_items` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `project_room_id` INT NOT NULL, `surface_id` INT NOT NULL, `master_product_id` INT NOT NULL, `master_system_id` INT NOT NULL, `qty` DECIMAL(10,2) NOT NULL, `rate` DECIMAL(10,2) NOT NULL, `amount` DECIMAL(10,2) NOT NULL, `system_rate` DECIMAL(10,2), `measurement_mode` VARCHAR(255), `pricing_mode` VARCHAR(255), `deductions` TEXT, `color_code` VARCHAR(255), `description` TEXT, `manual_price` DECIMAL(10,2) NOT NULL DEFAULT '0', `gross_qty` DECIMAL(10,2) NOT NULL DEFAULT '0', `net_qty` DECIMAL(10,2) NOT NULL DEFAULT '0', `created_at` DATETIME, `updated_at` DATETIME, `manual_area` DECIMAL(10,2));
INSERT INTO quote_items VALUES(1,1,1,1,8,750,40,30000,40,'MANUAL','CALCULATED',NULL,'0765',NULL,0,800,750,'2026-02-08 15:18:18','2026-02-08 15:18:18',NULL);
INSERT INTO quote_items VALUES(2,2,1,1,8,780,40,31200,40,'MANUAL','CALCULATED',NULL,'076',NULL,0,800,780,'2026-02-08 16:31:22','2026-02-08 16:31:22',NULL);
INSERT INTO quote_items VALUES(3,1,2,1,8,550,40,22000,40,'MANUAL','CALCULATED',NULL,'0785',NULL,0,600,550,'2026-02-09 10:05:48','2026-02-09 10:05:48',NULL);
INSERT INTO quote_items VALUES(4,3,1,1,8,450,40,18000,40,'MANUAL','CALCULATED',NULL,'0758',NULL,0,500,450,'2026-02-10 18:23:46','2026-02-10 18:23:46',NULL);
INSERT INTO quote_items VALUES(5,4,1,1,8,1400,40,56000,40,'MANUAL','CALCULATED',NULL,'0765 Snow White',NULL,0,1500,1400,'2026-02-11 11:54:40','2026-02-11 11:54:40',NULL);
INSERT INTO quote_items VALUES(6,4,2,1,8,400,40,16000,40,'MANUAL','CALCULATED',NULL,'0546 Yellow',NULL,0,400,400,'2026-02-11 11:55:07','2026-02-11 11:55:07',NULL);
INSERT INTO quote_items VALUES(7,5,1,1,8,1400,40,56000,40,'MANUAL','CALCULATED',NULL,'0765 Snow White',NULL,0,1500,1400,'2026-02-11 11:55:49','2026-02-11 11:55:49',NULL);
INSERT INTO quote_items VALUES(8,5,2,1,8,400,40,16000,40,'MANUAL','CALCULATED',NULL,'0546 Yellow',NULL,0,400,400,'2026-02-11 11:55:49','2026-02-11 11:55:49',NULL);
INSERT INTO quote_items VALUES(9,6,1,1,8,500,40,20000,40,'MANUAL','CALCULATED',NULL,'0765',NULL,0,600,500,'2026-02-14 11:24:00','2026-02-14 11:24:00',NULL);
CREATE TABLE IF NOT EXISTS `master_services` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `name` VARCHAR(255) NOT NULL, `unit_type` VARCHAR(255) CHECK (`unit_type` in ('AREA', 'LINEAR', 'COUNT', 'LUMPSUM')) NOT NULL, `default_rate` DECIMAL(10,2) NOT NULL, `is_repair` TINYINT(1) NOT NULL DEFAULT '0', `created_at` DATETIME, `updated_at` DATETIME, `remarks` TEXT);
INSERT INTO master_services VALUES(1,'Floor Masking','AREA',5,0,'2026-02-08 15:17:15','2026-02-14 10:06:54','Masking Used for Covering Furniture etc');
INSERT INTO master_services VALUES(2,'Waterproofing','AREA',45,1,'2026-02-09 09:06:16','2026-02-09 09:06:16',NULL);
CREATE TABLE IF NOT EXISTS `quote_media` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `photo_url` VARCHAR(255) NOT NULL, `tag` VARCHAR(255) NOT NULL DEFAULT 'BEFORE', `created_at` DATETIME, `updated_at` DATETIME, `project_room_id` INT, FOREIGN KEY(`project_room_id`) REFERENCES project_rooms(`id`) ON DELETE SET NULL ON UPDATE NO ACTION);
CREATE TABLE IF NOT EXISTS `quote_services` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `master_service_id` INT, `custom_name` VARCHAR(255), `unit_type` VARCHAR(255) NOT NULL, `quantity` DECIMAL(10,2) NOT NULL, `rate` DECIMAL(10,2) NOT NULL, `amount` DECIMAL(10,2) NOT NULL, `photo_url` VARCHAR(255), `created_at` DATETIME, `updated_at` DATETIME, `project_room_id` INT, `length` DECIMAL(10,2), `breadth` DECIMAL(10,2), `count` DECIMAL(10,2), `remarks` TEXT, FOREIGN KEY(`master_service_id`) REFERENCES master_services(`id`) ON DELETE SET NULL ON UPDATE NO ACTION, FOREIGN KEY(`project_room_id`) REFERENCES project_rooms(`id`) ON DELETE SET NULL ON UPDATE NO ACTION);
INSERT INTO quote_services VALUES(1,1,NULL,'AREA',520,5,2600,NULL,'2026-02-08 15:18:31','2026-02-08 15:18:31',1,NULL,NULL,NULL,NULL);
INSERT INTO quote_services VALUES(2,1,NULL,'AREA',500,5,2500,NULL,'2026-02-08 16:31:37','2026-02-08 16:31:37',2,NULL,NULL,NULL,NULL);
INSERT INTO quote_services VALUES(3,1,NULL,'AREA',500,5,2500,NULL,'2026-02-09 10:33:10','2026-02-09 10:33:10',1,NULL,NULL,NULL,NULL);
INSERT INTO quote_services VALUES(4,2,NULL,'AREA',500,45,22500,NULL,'2026-02-09 10:53:54','2026-02-09 10:53:54',1,NULL,NULL,NULL,NULL);
INSERT INTO quote_services VALUES(5,1,'Floor Masking','AREA',600,5,3000,NULL,'2026-02-10 18:23:59','2026-02-10 18:23:59',3,NULL,NULL,NULL,NULL);
INSERT INTO quote_services VALUES(6,1,'Floor Masking','AREA',1500,5,7500,NULL,'2026-02-11 11:55:25','2026-02-11 11:55:25',4,NULL,NULL,NULL,NULL);
INSERT INTO quote_services VALUES(7,2,'Waterproofing','AREA',400,45,18000,NULL,'2026-02-11 11:55:40','2026-02-11 11:55:40',4,NULL,NULL,NULL,NULL);
INSERT INTO quote_services VALUES(8,1,'Floor Masking','AREA',1500,5,7500,NULL,'2026-02-11 11:55:49','2026-02-11 11:55:49',5,NULL,NULL,NULL,NULL);
INSERT INTO quote_services VALUES(9,2,'Waterproofing','AREA',400,45,18000,NULL,'2026-02-11 11:55:49','2026-02-11 11:55:49',5,NULL,NULL,NULL,NULL);
CREATE TABLE IF NOT EXISTS `customers` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `name` VARCHAR(255), `phone` VARCHAR(255) NOT NULL, `whatsapp_verified_at` DATETIME, `created_at` DATETIME, `updated_at` DATETIME, `email` VARCHAR(255), `address` TEXT, `city` VARCHAR(255), `state` VARCHAR(255), `pincode` VARCHAR(255));
INSERT INTO customers VALUES(1,'Usman Khan','7021759374','2026-02-14 06:43:25','2026-02-08 15:15:23','2026-02-14 10:05:19','fruitifiedmedia@gmail.com','Goregaon West','mumbai','Mahārāshtra','400104');
CREATE TABLE IF NOT EXISTS `project_photos` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `project_id` INT NOT NULL, `uploaded_by_type` VARCHAR(255), `uploaded_by_id` INT, `google_drive_file_id` VARCHAR(255) NOT NULL, `google_drive_link` VARCHAR(255) NOT NULL, `file_name` VARCHAR(255), `description` TEXT, `created_at` DATETIME, `updated_at` DATETIME, `stage` VARCHAR(255) NOT NULL DEFAULT 'before', FOREIGN KEY(`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE);
INSERT INTO project_photos VALUES(1,1,'App\Models\User',2,'162WmlYGj2YFoEl5G32C_u5oUYy6lnQp-','https://drive.google.com/uc?id=162WmlYGj2YFoEl5G32C_u5oUYy6lnQp-&export=view','ChatGPT Image Jan 5, 2026, 04_53_49 PM.png',NULL,'2026-02-09 10:29:52','2026-02-09 10:29:52','before');
INSERT INTO project_photos VALUES(2,1,'App\Models\User',2,'1J33V1KG6m_zj65t1dtIm7r0zDeLdeOLz','https://drive.google.com/uc?id=1J33V1KG6m_zj65t1dtIm7r0zDeLdeOLz&export=view','ChatGPT Image Jan 31, 2026, 07_30_22 PM.png',NULL,'2026-02-09 10:30:47','2026-02-09 10:30:47','before');
INSERT INTO project_photos VALUES(3,1,'App\Models\User',2,'1R1thk6lLYu4ptUC1eTKhfcW5DO7fETDS','https://drive.google.com/uc?id=1R1thk6lLYu4ptUC1eTKhfcW5DO7fETDS&export=view','jhhhg.png',NULL,'2026-02-09 10:31:21','2026-02-09 10:31:21','in-progress');
INSERT INTO project_photos VALUES(4,1,'App\Models\User',2,'1Jo6-ohoMgJ72TzGXx4hYJCATub178KTw','https://drive.google.com/uc?id=1Jo6-ohoMgJ72TzGXx4hYJCATub178KTw&export=view','Gemini_Generated_Image_39p1we39p1we39p1.png',NULL,'2026-02-09 10:31:44','2026-02-09 10:31:44','after');
CREATE TABLE IF NOT EXISTS `zone_photos` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `project_id` INT NOT NULL, `zone_id` INT NOT NULL, `google_drive_file_id` VARCHAR(255) NOT NULL, `google_drive_url` VARCHAR(255) NOT NULL, `file_name` VARCHAR(255) NOT NULL, `uploaded_by` INT NOT NULL, `created_at` DATETIME, `updated_at` DATETIME, FOREIGN KEY(`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE, FOREIGN KEY(`zone_id`) REFERENCES `zones`(`id`) ON DELETE CASCADE, FOREIGN KEY(`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS `customer_otps` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `phone` VARCHAR(255) NOT NULL, `expires_at` DATETIME NOT NULL, `created_at` DATETIME, `updated_at` DATETIME, `otp_hash` VARCHAR(255) NOT NULL);
CREATE TABLE IF NOT EXISTS `coupons` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `code` VARCHAR(255) NOT NULL, `type` VARCHAR(255) CHECK (`type` in ('FLAT', 'PERCENT')) NOT NULL, `value` DECIMAL(10,2) NOT NULL, `min_order_amount` DECIMAL(10,2), `expires_at` DATETIME, `is_active` TINYINT(1) NOT NULL DEFAULT '1', `created_at` DATETIME, `updated_at` DATETIME);
INSERT INTO coupons VALUES(1,'PAINTUP500','FLAT',600,5000,'2026-05-09 16:48:00',1,'2026-02-09 16:48:59','2026-02-09 17:51:55');
INSERT INTO coupons VALUES(2,'PAINTUP1000','FLAT',1000,10000,'2026-08-09 16:48:59',1,'2026-02-09 16:48:59','2026-02-09 16:48:59');
INSERT INTO coupons VALUES(3,'SAVE10','PERCENT',10,NULL,'2026-03-09 16:48:59',1,'2026-02-09 16:48:59','2026-02-09 16:48:59');
INSERT INTO coupons VALUES(4,'SAVE15','PERCENT',15,15000,'2026-04-09 16:48:59',1,'2026-02-09 16:48:59','2026-02-09 16:48:59');
INSERT INTO coupons VALUES(5,'WELCOME2000','FLAT',2000,20000,NULL,1,'2026-02-09 16:48:59','2026-02-09 16:48:59');
INSERT INTO coupons VALUES(6,'EXPIRED50','FLAT',50,NULL,'2026-02-08 16:48:59',1,'2026-02-09 16:48:59','2026-02-09 16:48:59');
INSERT INTO coupons VALUES(7,'DISABLED100','FLAT',100,NULL,NULL,0,'2026-02-09 16:48:59','2026-02-09 17:52:21');
INSERT INTO coupons VALUES(8,'WELCOME2500','FLAT',2500,20000,NULL,1,'2026-02-09 17:52:53','2026-02-09 17:52:53');
CREATE TABLE IF NOT EXISTS `projects` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `client_name` VARCHAR(255) NOT NULL, `location` VARCHAR(255) NOT NULL, `phone` VARCHAR(255) NOT NULL, `status` VARCHAR(255) NOT NULL DEFAULT 'NEW', `created_at` DATETIME, `updated_at` DATETIME, `public_token` VARCHAR(255), `total_amount` DECIMAL(10,2) NOT NULL DEFAULT '0', `booking_amount` DECIMAL(10,2), `payment_method` VARCHAR(255), `booking_paid_at` DATETIME, `mid_paid_at` DATETIME, `final_paid_at` DATETIME, `booking_reference` VARCHAR(255), `mid_reference` VARCHAR(255), `final_reference` VARCHAR(255), `cash_confirmed_by` INT, `cash_confirmed_at` DATETIME, `mid_amount` DECIMAL(10,2) NOT NULL DEFAULT '0', `final_amount` DECIMAL(10,2) NOT NULL DEFAULT '0', `booking_status` VARCHAR(255) NOT NULL DEFAULT 'PENDING', `mid_status` VARCHAR(255) NOT NULL DEFAULT 'PENDING', `final_status` VARCHAR(255) NOT NULL DEFAULT 'PENDING', `supervisor_id` INT, `work_status` VARCHAR(255) NOT NULL DEFAULT 'PENDING', `work_started_at` DATETIME, `work_completed_at` DATETIME, `coupon_id` INT, `coupon_code` VARCHAR(255), `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT '0', `subtotal` DECIMAL(10,2) NOT NULL DEFAULT '0', `gst_rate` DECIMAL(10,2) NOT NULL DEFAULT '18', `gst_amount` DECIMAL(10,2) NOT NULL DEFAULT '0', `grand_total` DECIMAL(10,2) NOT NULL DEFAULT '0', `base_total` DECIMAL(10,2) NOT NULL DEFAULT '0', booking_gst DECIMAL(12, 2) DEFAULT 0, booking_total DECIMAL(12, 2) DEFAULT 0, mid_gst DECIMAL(12, 2) DEFAULT 0, mid_total DECIMAL(12, 2) DEFAULT 0, final_gst DECIMAL(12, 2) DEFAULT 0, final_total DECIMAL(12, 2) DEFAULT 0, FOREIGN KEY(`supervisor_id`) REFERENCES users(`id`) ON DELETE SET NULL ON UPDATE NO ACTION, FOREIGN KEY(`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE SET NULL);
INSERT INTO projects VALUES(1,'Amitabh','Juhu','7021759374','COMPLETED','2026-02-08 15:17:38','2026-02-11 11:07:02','N3AdgyPhyndjlSFjKkjaLfH0wxUK7Cjg',79600,31840,'CASH','2026-02-08 15:40:42','2026-02-08 16:32:55','2026-02-10 18:22:19',NULL,'MID-ONLINE-6988BAB7AE190','FINAL-ONLINE-698B775B45D0F',2,'2026-02-08 15:40:42',31840,15920,'PAID','PAID','PAID',NULL,'COMPLETED','2026-02-09 06:52:38','2026-02-11 09:53:40',NULL,NULL,0,0,18,0,0,79600,5731.1999999999998179,37571.19999999999709,5731.1999999999998179,37571.19999999999709,2865.5999999999999089,18785.599999999998545);
INSERT INTO projects VALUES(2,'Usman','Juhu','7021383812','NEW','2026-02-08 16:30:22','2026-02-08 16:30:22',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'PENDING','PENDING','PENDING',NULL,'PENDING',NULL,NULL,NULL,NULL,0,0,18,0,0,0,0,0,0,0,0,0);
INSERT INTO projects VALUES(3,'Amitabh','Juhu','7021759374','COMPLETED','2026-02-09 18:27:03','2026-02-11 18:29:07','N0mFgbaiC9EHemWFRqjF2Y8oXDZC4ftn',21000,8160,'CASH','2026-02-11 10:20:45','2026-02-11 11:08:11','2026-02-11 11:40:33',NULL,'MID-ONLINE-698C631B65016','FINAL-ONLINE-698C6AB1673DE',2,'2026-02-11 10:20:45',8160,4080,'PAID','PAID','PAID',NULL,'COMPLETED','2026-02-11 18:28:58','2026-02-11 18:29:02',1,'PAINTUP500',600,0,18,0,0,20400,1468.7999999999999545,9628.7999999999992727,1468.7999999999999545,9628.7999999999992727,734.39999999999997725,4814.3999999999996363);
INSERT INTO projects VALUES(4,'Usman','Juhu','7021759374','ACCEPTED','2026-02-11 11:53:38','2026-02-14 10:03:31','rnxsNUsWqPWC33vqx3G1NsvXgMhFnEih',195000,78000,'ONLINE','2026-02-11 12:01:50',NULL,NULL,'ONLINE-698C6FAE403F4',NULL,NULL,NULL,NULL,78000,39000,'PAID','AWAITING_CONFIRMATION','PENDING',NULL,'IN_PROGRESS','2026-02-14 05:27:46',NULL,NULL,NULL,0,0,18,0,0,195000,14040,92040,14040,92040,0,0);
INSERT INTO projects VALUES(5,'Usman','Goregaon West Mumbai - 400067','7021759374','NEW','2026-02-14 11:23:04','2026-02-14 11:28:10','1BfBao2heeKvSguXqbGnLSyRHMEioFws',20000,8000,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,8000,4000,'PENDING','PENDING','PENDING',NULL,'PENDING',NULL,NULL,NULL,NULL,0,0,18,0,0,20000,0,0,0,0,0,0);
CREATE TABLE IF NOT EXISTS `warranties` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `project_id` INT NOT NULL, `pdf_path` VARCHAR(255), `generated_at` DATETIME, `created_at` DATETIME, `updated_at` DATETIME, FOREIGN KEY(`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS `milestone_payments` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `project_id` INT NOT NULL, `milestone_name` VARCHAR(255) NOT NULL, `base_amount` DECIMAL(10,2) NOT NULL, `gst_amount` DECIMAL(10,2) NOT NULL DEFAULT '0', `total_amount` DECIMAL(10,2) NOT NULL, `payment_status` VARCHAR(255) NOT NULL DEFAULT 'PENDING', `payment_method` VARCHAR(255), `payment_reference` VARCHAR(255), `paid_at` DATETIME, `gst_invoice_generated_at` DATETIME, `gst_invoice_number` VARCHAR(255), `created_at` DATETIME, `updated_at` DATETIME, `tracking_id` VARCHAR(255), `bank_ref_no` VARCHAR(255), `payment_mode` VARCHAR(255), `card_name` VARCHAR(255), FOREIGN KEY(`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE);
INSERT INTO milestone_payments VALUES(1,1,'booking',31840,5731.1999999999998179,37571.19999999999709,'PAID','ONLINE','ONLINE-TEST-698C361CB8CE5','2026-02-11 07:56:12',NULL,NULL,'2026-02-11 07:56:12','2026-02-11 07:56:12',NULL,NULL,NULL,NULL);
INSERT INTO milestone_payments VALUES(2,1,'mid',31840,5731.1999999999998179,37571.19999999999709,'PAID','ONLINE','MID-ONLINE-698C36853AA7B','2026-02-11 07:57:57',NULL,NULL,'2026-02-11 07:57:57','2026-02-11 07:57:57',NULL,NULL,NULL,NULL);
INSERT INTO milestone_payments VALUES(3,1,'final',15920,2865.5999999999999089,18785.599999999998545,'PAID','ONLINE','FINAL-ONLINE-698C36853D723','2026-02-11 07:57:57',NULL,NULL,'2026-02-11 07:57:57','2026-02-11 07:57:57',NULL,NULL,NULL,NULL);
INSERT INTO milestone_payments VALUES(4,3,'booking',8160,1468.7999999999999545,9628.7999999999992727,'AWAITING_CONFIRMATION','CASH','CASH-698C54FB87E8C',NULL,NULL,NULL,'2026-02-11 10:07:55','2026-02-11 10:07:55',NULL,NULL,NULL,NULL);
INSERT INTO milestone_payments VALUES(5,3,'booking',8160,1468.7999999999999545,9628.7999999999992727,'AWAITING_CONFIRMATION','CASH','CASH-698C5555942B6',NULL,NULL,NULL,'2026-02-11 10:09:25','2026-02-11 10:09:25',NULL,NULL,NULL,NULL);
INSERT INTO milestone_payments VALUES(6,3,'booking',8160,1468.7999999999999545,9628.7999999999992727,'AWAITING_CONFIRMATION','CASH','CASH-698C55A96014B',NULL,NULL,NULL,'2026-02-11 10:10:49','2026-02-11 10:10:49',NULL,NULL,NULL,NULL);
INSERT INTO milestone_payments VALUES(7,3,'booking',8160,1468.7999999999999545,9628.7999999999992727,'AWAITING_CONFIRMATION','CASH','CASH-698C573884E45',NULL,NULL,NULL,'2026-02-11 10:17:28','2026-02-11 10:17:28',NULL,NULL,NULL,NULL);
INSERT INTO milestone_payments VALUES(8,3,'mid',8160,1468.7999999999999545,9628.7999999999992727,'PAID','ONLINE','MID-ONLINE-698C631B63C74','2026-02-11 11:08:11',NULL,NULL,'2026-02-11 11:08:11','2026-02-11 11:08:11',NULL,NULL,NULL,NULL);
INSERT INTO milestone_payments VALUES(9,3,'final',4080,734.39999999999997725,4814.3999999999996363,'PAID','ONLINE','FINAL-ONLINE-698C6AB165F54','2026-02-11 11:40:33',NULL,NULL,'2026-02-11 11:40:33','2026-02-11 11:40:33',NULL,NULL,NULL,NULL);
INSERT INTO milestone_payments VALUES(10,4,'booking',78000,14040,92040,'PAID','ONLINE','ONLINE-698C6FAE3F43C','2026-02-11 12:01:50',NULL,NULL,'2026-02-11 12:01:50','2026-02-11 12:01:50',NULL,NULL,NULL,NULL);
INSERT INTO milestone_payments VALUES(11,4,'mid',78000,14040,92040,'AWAITING_CONFIRMATION','CASH','MID-CASH-699048731139D',NULL,NULL,NULL,'2026-02-14 10:03:31','2026-02-14 10:03:31',NULL,NULL,NULL,NULL);
CREATE TABLE IF NOT EXISTS `project_billing_details` (`id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL, `project_id` INT NOT NULL, `milestone_type` VARCHAR(255) NOT NULL, `buying_type` VARCHAR(255) CHECK (`buying_type` in ('INDIVIDUAL', 'BUSINESS')) NOT NULL DEFAULT 'INDIVIDUAL', `gstin` VARCHAR(255), `business_name` VARCHAR(255), `business_address` TEXT, `state` VARCHAR(255), `pincode` VARCHAR(255), `created_at` DATETIME, `updated_at` DATETIME, FOREIGN KEY(`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    `company_name` VARCHAR(255) NOT NULL DEFAULT 'PaintUp',
    `logo_path` VARCHAR(255) NULL,
    `primary_color` VARCHAR(255) NOT NULL DEFAULT '#2563eb',
    `secondary_color` VARCHAR(255) NOT NULL DEFAULT '#1e293b',
    `support_whatsapp` VARCHAR(255) NULL,
    `support_email` VARCHAR(255) NULL,
    `footer_text` VARCHAR(255) NULL,
    `gst_number` VARCHAR(255) NULL,
    `address` TEXT NULL,
    `invoice_prefix` VARCHAR(255) NOT NULL DEFAULT 'INV',
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `settings` (
    `id`, `company_name`, `primary_color`, `secondary_color`, `support_whatsapp`,
    `support_email`, `gst_number`, `address`, `invoice_prefix`, `created_at`, `updated_at`
) VALUES (
    1, 'PaintUp', '#79a2fb', '#1e293b', '+91 63842 38919',
    'support@paintup.in', '27LJYPK6051K1Z7', 'Kandivali West Mumbai - 400067', 'INV',
    '2026-02-14 10:19:08', '2026-02-14 14:04:07'
) ON DUPLICATE KEY UPDATE `id` = `id`;
CREATE UNIQUE INDEX `users_email_unique` ON `users` (`email`);
CREATE INDEX `sessions_user_id_index` ON `sessions` (`user_id`);
CREATE INDEX `sessions_last_activity_index` ON `sessions` (`last_activity`);
CREATE INDEX `cache_expiration_index` ON `cache` (`expiration`);
CREATE INDEX `cache_locks_expiration_index` ON `cache_locks` (`expiration`);
CREATE INDEX `jobs_queue_index` ON `jobs` (`queue`);
CREATE UNIQUE INDEX `failed_jobs_uuid_unique` ON `failed_jobs` (`uuid`);
CREATE UNIQUE INDEX `customers_phone_unique` ON `customers` (`phone`);
CREATE INDEX `customer_otps_phone_expires_at_index` ON `customer_otps` (`phone`, `expires_at`);
CREATE UNIQUE INDEX `coupons_code_unique` ON `coupons` (`code`);
CREATE UNIQUE INDEX `projects_public_token_unique` ON `projects` (`public_token`);
CREATE UNIQUE INDEX `project_billing_details_project_id_milestone_type_unique` ON `project_billing_details` (`project_id`, `milestone_type`);
