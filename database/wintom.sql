
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `price_per_meter` int(10) unsigned NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Curtain',150000,0,'2026-10-02 14:17:05'),(2,'Zebra Blind',100000,2,'2026-10-02 14:17:05'),(3,'Roller Blind',100000,1,'2026-10-02 14:17:05'),(4,'Motorized Curtain Track',750000,5,'2026-10-02 14:17:05'),(5,'Vertical Blind',100000,4,'2026-10-02 14:17:05'),(7,'Roman Blind',120000,3,'2026-10-21 23:23:10');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `content_videos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content_videos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `youtube_id` char(11) NOT NULL,
  `is_short` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `youtube_id` (`youtube_id`),
  KEY `idx_videos_order` (`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `content_videos` WRITE;
/*!40000 ALTER TABLE `content_videos` DISABLE KEYS */;
/*!40000 ALTER TABLE `content_videos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `galleries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `galleries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category` varchar(120) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_gallery_category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `galleries` WRITE;
/*!40000 ALTER TABLE `galleries` DISABLE KEYS */;
INSERT INTO `galleries` VALUES (1,'Ripple Fold Linen — Grand Penthouse PIK','Penthouse Sanctuary','https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1200&q=85','2026-10-02 07:17:06'),(2,'Sheer Voile Living Room — BSD City','Living Room','https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200&q=85','2026-10-02 07:17:06'),(3,'Velour Blackout Master Bedroom — Kebayoran','Living Room','https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?w=1200&q=85','2026-10-02 07:17:06'),(4,'Motorized Smart Curtain — SCBD Office Tower','Office & Commercial','https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85','2026-10-02 07:17:06'),(5,'Wood Blind Paulownia — Resort Bali','Hotel & Private Villa','https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=85','2026-10-02 07:17:06'),(6,'Double-Track Drape & Sheer — Dining Room Menteng','Dining & Atelier','https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85','2026-10-02 07:17:06'),(7,'S-Fold Wave — Penthouse Pakubuwono','Penthouse Sanctuary','https://images.unsplash.com/photo-1507652313519-d4e9174996dd?w=1200&q=85','2026-10-02 07:17:06'),(8,'Sunscreen Roller — Co-Working Space Sudirman','Office & Commercial','https://images.unsplash.com/photo-1540518614846-7ede433c4b4f?w=1200&q=85','2026-10-02 07:17:06'),(9,'French Linen Drape — Private Villa Seminyak','Hotel & Private Villa','https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=1200&q=85','2026-10-02 07:17:06'),(10,'Motorized Dual-Layer — Atelier Studio Kemang','Dining & Atelier','https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200&q=85','2026-10-02 07:17:06'),(11,'Bamboo Shade — Living Room Pondok Indah','Living Room','https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85','2026-10-02 07:17:06'),(12,'Penthouse Full-Wall Draping — Ciputra World','Penthouse Sanctuary','https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1200&q=85','2026-10-02 07:17:06');
/*!40000 ALTER TABLE `galleries` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_cover` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_images_product` (`product_id`),
  CONSTRAINT `fk_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (1,1,'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85',0,1),(2,1,'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=1200&q=85',1,0),(3,1,'https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?w=1200&q=85',2,0),(4,2,'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?w=1200&q=85',0,1),(5,2,'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?w=1200&q=85',1,0),(6,3,'https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=1200&q=85',0,1),(7,3,'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85',1,0),(8,4,'https://images.unsplash.com/photo-1540518614846-7ede433c4b4f?w=1200&q=85',0,1),(9,4,'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85',1,0),(10,5,'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85',0,1),(11,5,'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85',1,0),(12,6,'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=85',0,1),(13,6,'https://images.unsplash.com/photo-1540518614846-7ede433c4b4f?w=1200&q=85',1,0);
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_variant_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variant_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `variant_id` int(10) unsigned NOT NULL,
  `value` varchar(255) NOT NULL,
  `linked_image_id` int(10) unsigned DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_options_variant` (`variant_id`),
  KEY `fk_options_image` (`linked_image_id`),
  CONSTRAINT `fk_options_image` FOREIGN KEY (`linked_image_id`) REFERENCES `product_images` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_options_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_variant_options` WRITE;
/*!40000 ALTER TABLE `product_variant_options` DISABLE KEYS */;
INSERT INTO `product_variant_options` VALUES (1,1,'120×160cm',NULL,0),(2,1,'120×240cm',NULL,1),(3,1,'150×160cm',NULL,2),(4,1,'150×240cm',NULL,3),(5,1,'Custom (WA)',NULL,4),(6,2,'Oatmeal Cream',NULL,0),(7,2,'Warm Sand',NULL,1),(8,2,'Slate Charcoal',NULL,2),(9,2,'Muted Olive',NULL,3),(10,3,'Ripple Fold 2.2x',NULL,0),(11,3,'Double Pinch Pleat',NULL,1),(12,3,'Eyelet Minimalist',NULL,2),(13,4,'120×160cm',NULL,0),(14,4,'120×240cm',NULL,1),(15,4,'150×160cm',NULL,2),(16,4,'150×240cm',NULL,3),(17,4,'Custom (WA)',NULL,4),(18,5,'Midnight Navy',NULL,0),(19,5,'Deep Espresso',NULL,1),(20,5,'Anthracite Dark Grey',NULL,2),(21,5,'Ivory Cashmere',NULL,3),(22,6,'Ripple Fold 2.5x Fullness',NULL,0),(23,6,'Deep Pinch Pleat',NULL,1),(24,7,'120×160cm',NULL,0),(25,7,'120×240cm',NULL,1),(26,7,'150×160cm',NULL,2),(27,7,'150×240cm',NULL,3),(28,7,'Custom (WA)',NULL,4),(29,8,'Pure Snow White',NULL,0),(30,8,'Warm Alabaster',NULL,1),(31,8,'Champagne Tint',NULL,2),(32,8,'Soft Greige',NULL,3),(33,9,'Continuous S-Fold Wave',NULL,0),(34,9,'Tailored Box Pleat',NULL,1),(35,10,'120×160cm',NULL,0),(36,10,'120×240cm',NULL,1),(37,10,'150×160cm',NULL,2),(38,10,'150×240cm',NULL,3),(39,10,'Custom (WA)',NULL,4),(40,11,'Mono White',NULL,0),(41,11,'Ash Grey',NULL,1),(42,11,'Dark Bronze Charcoal',NULL,2),(43,11,'Sand Dune',NULL,3),(44,12,'Manual Stainless Chain',NULL,0),(45,12,'Cordless Spring Assist',NULL,1),(46,13,'120×160cm',NULL,0),(47,13,'120×240cm',NULL,1),(48,13,'150×160cm',NULL,2),(49,13,'150×240cm',NULL,3),(50,13,'Custom (WA)',NULL,4),(51,14,'Somfy France WireFree',NULL,0),(52,14,'Tuya Zigbee Smart Ultra',NULL,1),(53,14,'Dooya RS485 Pro',NULL,2),(54,15,'Lurus (Straight)',NULL,0),(55,15,'L-Shape Curved 90°',NULL,1),(56,15,'Double Track (Drape + Sheer)',NULL,2),(57,16,'120×160cm',NULL,0),(58,16,'120×240cm',NULL,1),(59,16,'150×160cm',NULL,2),(60,16,'150×240cm',NULL,3),(61,16,'Custom (WA)',NULL,4),(62,17,'Natural Oak',NULL,0),(63,17,'Warm Teak',NULL,1),(64,17,'Ebony Charcoal',NULL,2),(65,17,'Nordic White',NULL,3),(66,18,'Matching Fabric Tape (38mm)',NULL,0),(67,18,'Hidden Cord Minimalist',NULL,1);
/*!40000 ALTER TABLE `product_variant_options` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `label` varchar(100) NOT NULL,
  `is_image_linked` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_variants_product` (`product_id`),
  CONSTRAINT `fk_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (1,1,'Ukuran',0,0),(2,1,'Warna',0,1),(3,1,'Gaya Jahitan',0,2),(4,2,'Ukuran',0,0),(5,2,'Warna',0,1),(6,2,'Gaya Jahitan',0,2),(7,3,'Ukuran',0,0),(8,3,'Warna',0,1),(9,3,'Gaya Jahitan',0,2),(10,4,'Ukuran',0,0),(11,4,'Warna',0,1),(12,4,'Kontrol',0,2),(13,5,'Ukuran',0,0),(14,5,'Motor Brand',0,1),(15,5,'Tipe Track',0,2),(16,6,'Ukuran',0,0),(17,6,'Warna',0,1),(18,6,'Tape',0,2);
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(190) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `badge` varchar(100) DEFAULT NULL,
  `ref_code` varchar(50) DEFAULT NULL,
  `price` int(10) unsigned NOT NULL,
  `original_price` int(10) unsigned DEFAULT NULL,
  `price_unit` varchar(50) DEFAULT '/ meter',
  `description` text DEFAULT NULL,
  `features` text DEFAULT NULL,
  `options_json` text DEFAULT NULL,
  `cover_image` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `stock` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_products_category` (`category_id`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'aura-drape-french-linen','Aura Drape — French Linen Blend',1,'Curator Choice','WNT-CR-01',385000,450000,'/ meter','Tirai drapery mewah berbahan perpaduan linen Belgia dengan tenunan berpola rapat yang jatuh dengan anggun. Memberikan nuansa hangat, tekstur alami organik, dan redaman akustik ruangan yang optimal.','Material 65% Belgian Linen, 35% Poly-Loom|S-Fold Wave / Ripple Fold Hemming Presisi|Semi-Blackout (80% Light Reduction)|Termasuk Rel Arsitektural Silent-Glide','{\"colors\":[\"Oatmeal Cream\",\"Warm Sand\",\"Slate Charcoal\",\"Muted Olive\"],\"stitch\":[\"Ripple Fold 2.2x\",\"Double Pinch Pleat\",\"Eyelet Minimalist\"]}','https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1200&q=85',1,NULL,'2026-10-02 07:11:27'),(2,'velour-noir-100-blackout','Velour Noir — 100% Total Blackout',1,'100% Blackout','WNT-CR-02',495000,580000,'/ meter','Solusi kegelapan total mutlak untuk master bedroom dan private home cinema. Dilapisi 3-pass acrylic thermal layer yang meredam suhu panas sinar matahari dan kebisingan jalan perkotaan.','100% Zero-Light Transmittance Tested|Thermal Barrier (Menurunkan panas ruangan hingga 4°C)|Lapisan Anti-Statik Debu & Tungau|Double Weight Lead-Chain Bottom Hem','{\"colors\":[\"Midnight Navy\",\"Deep Espresso\",\"Anthracite Dark Grey\",\"Ivory Cashmere\"],\"stitch\":[\"Ripple Fold 2.5x Fullness\",\"Deep Pinch Pleat\"]}','https://images.unsplash.com/photo-1507652313519-d4e9174996dd?w=1200&q=85',1,NULL,'2026-10-02 07:11:27'),(3,'lumiere-sheer-ripple-fold','Lumière Sheer — Swiss Voile Vitrase',1,'Bestseller','WNT-SH-01',265000,310000,'/ meter','Vitrase voile tenun halus bermotif mikroskopis lembut asal Swiss yang meneruskan cahaya alami lembut ke dalam hunian tanpa menyilaukan mata, sekaligus menjaga privasi ruang keluarga di siang hari.','Ultra-Light Swiss Weave Micro-fiber|Difusi Cahaya Alami Matahari yang Lembut|Gelombang Lipatan Ripple Fold Sempurna & Simetris|Tahan Cuci & Anti-Kusut','{\"colors\":[\"Pure Snow White\",\"Warm Alabaster\",\"Champagne Tint\",\"Soft Greige\"],\"stitch\":[\"Continuous S-Fold Wave\",\"Tailored Box Pleat\"]}','https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=1200&q=85',1,NULL,'2026-10-02 07:11:27'),(4,'aero-roller-blind-sunscreen','Aero Blind — Minimalist Sunscreen 5%',3,'Modern Minimalist','WNT-RB-01',320000,380000,'/ m²','Roller blind berestetika arsitektural bersih dengan keterbukaan tenun 5%. Menghalau silau matahari dan 95% radiasi UV berbahaya sembari tetap mempertahankan pemandangan ke luar jendela secara jernih.','95% UV Rejection & Glare Filter|Komponen Heavy-Duty Spring & Tube Aluminium 38mm|Sistem Tarikan Rantai Stainless Steel Anti-Macet|Cocok untuk Ruang Kerja & High-Rise Apartment','{\"colors\":[\"Mono White\",\"Ash Grey\",\"Dark Bronze Charcoal\",\"Sand Dune\"],\"control\":[\"Manual Stainless Chain\",\"Cordless Spring Assist\"]}','https://images.unsplash.com/photo-1540518614846-7ede433c4b4f?w=1200&q=85',1,NULL,'2026-10-02 07:11:27'),(5,'zenith-motorized-somfy-system','Zenith Smart Motorized Curtain System',4,'Smart Home Ready','WNT-SM-01',2750000,3200000,'/ set track','Sistem motorisasi tirai kelas dunia bertenaga Somfy / Tuya Ultra-Quiet Motor (< 30dB). Terintegrasi penuh dengan Apple HomeKit, Google Home, remote multi-channel, dan sensor matahari otomatis.','Motor Whisper-Quiet (<30dB) dengan Soft-Start & Stop|Kompatibel Apple HomeKit, Google Assistant, Tuya|Touch Motion Assist (Tarik lembut kain langsung jalan)|Beban Maksimum Tirai hingga 60 Kilogram','{\"motor_brand\":[\"Somfy France WireFree\",\"Tuya Zigbee Smart Ultra\",\"Dooya RS485 Pro\"],\"track_type\":[\"Lurus (Straight)\",\"L-Shape Curved 90\\u00b0\",\"Double Track (Drape + Sheer)\"]}','https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=1200&q=85',1,NULL,'2026-10-02 07:11:27'),(6,'komorebi-paulownia-wood-blind','Komorebi Natural Paulownia Wood Blind',5,'Eco-Natural Wood','WNT-WB-01',540000,620000,'/ m²','Bilah kayu Paulownia solid 50mm yang ringan dan tidak membebani konstruksi jendela. Memberikan siluet bayangan matahari geometris tropis khas resor bintang lima dengan pernis tahan kelembapan.','Kayu Solid Paulownia 50mm Ringan & Anti-Melengkung|Pernis UV 5-Layer Tahan Cuaca Tropis Lembab|Pilihan Pita Fabric Ladder Tape Elegan|Headrail Profil Baja Kokoh Tertutup Valance Kayu','{\"colors\":[\"Natural Oak\",\"Warm Teak\",\"Ebony Charcoal\",\"Nordic White\"],\"tape\":[\"Matching Fabric Tape (38mm)\",\"Hidden Cord Minimalist\"]}','https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1200&q=85',0,NULL,'2026-10-02 07:11:27');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

