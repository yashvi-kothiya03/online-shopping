-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: ceramic
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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

--
-- Current Database: `ceramic`
--

/*!40000 DROP DATABASE IF EXISTS `ceramic`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `ceramic` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `ceramic`;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `image` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (3,'kitchen Ware'),(4,'Table Ware'),(5,'Bath Ware'),(7,'Artist');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `number` int(11) NOT NULL,
  `message` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `method` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `total_products` varchar(1000) NOT NULL,
  `total_price` int(100) NOT NULL,
  `placed_on` varchar(50) NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `return_requested` tinyint(1) DEFAULT 0,
  `return_otp` varchar(6) DEFAULT NULL,
  `return_otp_verified` tinyint(1) DEFAULT 0,
  `return_otp_created_at` timestamp NULL DEFAULT NULL,
  `return_reason` text DEFAULT NULL,
  `return_details` text DEFAULT NULL,
  `return_processed` tinyint(1) DEFAULT 0,
  `return_approved` tinyint(1) DEFAULT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `otp_verified` tinyint(1) DEFAULT 0,
  `otp_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seller_ids` varchar(255) DEFAULT NULL,
  `order_items_json` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `details` varchar(500) DEFAULT NULL,
  `price` int(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `seller_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (3,'Cork and Clay Tea Set','The teapot and two cups feature a unique design with a black ceramic top and a cork-finished bottom. Perfect for a stylish and functional tea-drinking experience.',1500,'2d0f2acd3b174ef9219134d15ca4c858.jpg',3,6,28,17),(4,'Matte Black Ceramic Mug','Its organic shape and unique handle provide a comfortable grip. Perfect for enjoying your favorite beverages with a touch of sophistication.',800,'5e3c48b1e1458e9fb36432a3c152b80c.jpg',3,6,6,18),(5,'Earthenware Coffee Cup Set','The cups have a unique, rounded shape and the saucers feature a contrasting color. Perfect for enjoying your favorite coffee or tea in style.',1800,'5eb35d0a67e99fd061e6b840a6aa6526.jpg',3,6,34,17),(7,'Espresso Cup Set','This elegant espresso cup set features two cups and saucers in a modern, minimalist design. The cups are made of a high-quality ceramic with a smooth finish.',1700,'40e9603f2d6f35457d95d11d3b2333d9.jpg',3,6,11,17),(10,'demitasse cup','The cups have a unique, rounded shape and the saucers feature a contrasting color. Perfect for enjoying your favorite coffee or tea in style.',1750,'1875d95625f0888f352c755d7acb4632.jpg',3,6,40,18),(12,'Personalized Couple Mugs','These adorable ceramic mugs feature the words \"WIFEY\" and \"HUBBY\" in a charming, handwritten style. The mugs are available in different colors and finishes, making them a perfect gift for couples celebrating their love.',1900,'a144f57455b6c665f72f62a44fe6aa4b.jpg',3,6,23,18),(13,'Zen Porcelain cup','This elegant cup features a teapot and two cups crafted from high-quality porcelain. The set has a minimalist design with a soft, neutral color and subtle texture.',2050,'a0239ee31e8caf10c58dd18ca24a36e6.jpg',3,6,39,17),(15,'Textured Ceramic Mug','This unique mug is crafted from ceramic with a textured surface that adds visual interest. The warm, neutral color and organic shape create a rustic and inviting aesthetic.\r\n\r\n',700,'d096b89d83431dd54bdd9a9e0b2d1264.jpg',3,6,29,17),(19,'Nordic Blue Dinnerware Set','This elegant dinnerware set features a modern, minimalist design with a combination of blue and white tones. The set includes plates, bowls, and mugs, perfect for everyday dining or special occasions.\r\n\r\n',12500,'39fd37a8fe5cedfbb6a15e3d11c31132.jpg',3,7,22,17),(20,'Earthenware Dinnerware Set','This elegant dinnerware set features a warm, earthy color palette with a ribbed texture for added visual interest. The set includes plates, bowls, and mugs, perfect for everyday dining or special occasions.',13000,'79e65e819667fe22e3f6f9654f2357c7.jpg',3,7,18,18),(21,'Pastel Pink Dinnerware Set','This elegant dinnerware set features a soft pink color that adds a touch of femininity to your table. The set includes plates, bowls, and mugs, perfect for everyday dining or special occasions. ',9000,'90ef3ef5e8d73e3fd78afe6e09cd975e.jpg',3,7,18,17),(23,'Matte Black Dinnerware Set','This sleek dinnerware set features matte black plates and bowls with a gold-toned rim. The set includes a variety of pieces, perfect for everyday dining or special occasions.',14000,'1715f7187ce50d24094d8ef51d6789e8.jpg',3,7,34,17),(26,'ceramic plate','This elegant dinner set features a neutral green color with a textured finish. The set includes plates and a mug, perfect for everyday dining or special occasions.',8000,'f4d867cc3885dac489e01a7ac5bcf51d.jpg',3,7,20,18),(29,'Matte Black Ceramic Jug','This elegant set includes a pitcher and two smaller vessels, all crafted from high-quality ceramic with a matte black finish. The organic shapes and unique handles add a touch of modern sophistication. Perfect for serving water, juice, or other beverages in style.',2500,'02b5b332f5a7c3b29b100dbf458cc3f3.jpg',3,8,37,17),(30,'Rosendahl Grand Jug','This stylish vacuum jug from Rosendahl is perfect for keeping your drinks hot or cold for hours. The sleek, minimalist design and high-quality materials make it a functional and elegant addition to any kitchen or dining table.',4500,'4be2bd35655948f53388149d680d76e1.jpg',3,8,31,18),(32,'Ridge Jug','This elegant ceramic jug features a ribbed design and a unique, curved handle. Its neutral color and minimalist aesthetic make it a versatile piece that can be used as a vase or pitcher',2500,'1581a5afbe817f8d9c6a4bb41e586ff4.jpg',3,8,38,18),(34,'Sucabaruca Jug','The set includes three matching cups, a wooden tray and ceramic Jugs. The sleek, minimalist aesthetic and high-quality ceramic make it a perfect addition to any modern kitchen.',7500,'8428f85300f20e10692dad3c9274d8b9.jpg',3,8,47,18),(35,'Geometric Ceramic Jug','This unique pitcher features a geometric design with contrasting colors of black, brown, and blue. The tapered shape and curved handle add a modern touch. Perfect for serving water, juice, or other beverages in style.',2200,'300216ca36f48bf4ddf27aab398938b1.jpg',3,8,23,17),(36,'Rustic Ceramic Jug','This charming set features a handcrafted ceramic pitcher with a unique, elongated spout and a rope handle. The matching cup and saucer complete the set. The earthy, neutral color and rustic texture add a touch of warmth and character to any kitchen.',2500,'a7fa9c3c9d37d666e9e8b8d7f5a0763b.jpg',3,8,15,18),(37,'Pastel Pink Jug','This elegant set features a pitcher and a mug in a soft pink color. The minimalist design and smooth finish make it a stylish addition to any kitchen. Perfect for serving water, juice, or your favorite beverages.',1600,'c6cbb3bd43ec1957ab9693ca269f2a63.jpg',3,8,50,17),(38,'Tapered Ceramic Jug','This elegant pitcher features a sleek, tapered design and a curved handle. The smooth, white finish gives it a minimalist and timeless aesthetic. Perfect for serving water, juice, or other beverages in style.',1600,'e4e19af23cc5a296d2c7d26191efe462.jpg',3,8,14,18),(39,'Splatterware Jugs','This unique pitcher features a white ceramic base with a vibrant splash of red and orange paint. The playful design and bold colors add a touch of personality to any kitchen or dining table. Perfect for serving water, juice, or other beverages in style.',1800,'fb61063c065430383a10ed301fa39803.jpg',3,8,9,17),(51,'Modern Grey Composite Kitchen Sink','This contemporary kitchen sink features a sleek, single-bowl design made from durable composite material. Its matte grey finish adds a stylish and sophisticated look to any kitchen',28000,'46830723606c904c9fab99d3dfe9d434.jpg',3,9,45,17),(56,'Leaf-Shaped Ceramic Tray','This unique ceramic tray is designed to resemble a leaf, adding a natural touch to your home. The textured surface and subtle color variations create a rustic aesthetic. ',1100,'510d9d458f036f5d251880cd268d4386.jpg',3,10,8,18),(59,'Organic Ceramic Trays Set','This set of three handmade ceramic trays features unique, organic shapes with textured finishes. The soft, neutral colors add a touch of elegance to your home. ',3100,'c81961d56e1a0bc568cbf13eba7cb5c1.jpg',3,10,40,17),(60,'Whale Ceramic Platter','This stunning ceramic platter features a beautifully sculpted whale emerging from the ocean waves. The intricate details, including the whale\'s skin texture and the crashing waves, make it a true work of art. Perfect for serving food or displaying as a decorative piece.',1500,'cecad1deaaa492ebd643e35716050946.jpg',3,10,31,18),(66,'Textured Ceramic Serving Set','This versatile ceramic serving set includes a large platter and two bowls, all featuring a textured, rustic finish. The neutral color and unique shapes add a touch of character to your table setting.\r\n\r\n',1900,'b4d80a7823a8d83d2de4eabef64a93b2.jpg',3,10,31,18),(67,'White Rectangular Serving Platter','This versatile ceramic platter features a clean, white design with a smooth finish. Its rectangular shape and generous size make it perfect for serving a variety of dishes, from appetizers to main courses.\r\n\r\n',1800,'0393a7e19b93eba5c29ad5cb35f8d5a8.jpg',3,10,12,17),(68,'Modern Dining Room Set','This stylish dining room set features a round, white table with a fluted base and a set of upholstered chairs with curved backs. The minimalist design and neutral colors create a clean and contemporary look.\r\n\r\n',50000,'30c748c5e207100a7e595b7889df0736.jpg',4,11,9,18),(69,'Square Dining Table Set','This modern dining set features a square table with a black finish and four upholstered chairs in a neutral gray color. The curved design of the chairs adds a touch of comfort and style.',40000,'48e7c760fd9038195ad2c9e48b2e81d4.jpg',4,11,50,17),(71,'Modern Ceramic Dining table','This elegant dining room features a curved, white table, upholstered chairs with wooden legs, and a stunning collection of pendant lights. The neutral color palette and natural materials create a calming and inviting atmosphere. Perfect for modern homes with a bohemian flair.',95000,'1928023b9dabaf3f7075c97a779018cf.jpg',4,11,37,17),(72,'Minimalist Dining Table','This modern dining Table features a white oval table, upholstered chairs with black legs, and a large, neutral rug. The minimalist design and clean lines create a calm and inviting atmosphere.',75000,'b03963624d86e01d7095f59232f7ea86.jpg',4,11,28,18),(79,'Black Ceramic Extending Dining Table','This modern dining table features a sleek black ceramic top and a striking X-shaped base. The table can be extended to accommodate more guests, making it perfect for entertaining. The matching chairs with velvet upholstery add a touch of luxury.',95000,'2d1068bf09f72714174f8ccaf6e03543.jpg',4,11,23,17),(80,'Modern Black Ceramic Dining Table Set','This stylish dining set features a large rectangular table with a black marble top and a unique, curved base. The upholstered chairs in a neutral gray color add a touch of comfort',90000,'e62621bd85cdbf49bbfc2672f2e1ddf0.jpg',4,11,29,18),(82,'Organic Modern Coffee Table','This unique coffee table features a sculpted, organic shape with two distinct legs. The white finish and textured surface create a modern and minimalist look. Perfect for adding a focal point to your living room.',30000,'04ce13ce24d9438c18e546049721f8a0.jpg',4,12,23,18),(84,'Organic Ceramic Coffee Table','This unique coffee table features a sculpted, organic shape with a smooth white finish. The large, flowing design creates a striking focal point for any living room.',70000,'6ec37b2f6cb653ef97a910d27a1a05f6.jpg',4,12,27,18),(86,'Cloud-Shaped Table Set','This unique set of two coffee tables features a modern, cloud-like shape. The larger table has three legs, while the smaller one has a single leg. The white finish and textured surface create a minimalist and sculptural look.',75000,'8b381876dadf305f3c8cb790eb5446a8.jpg',4,12,13,18),(87,'Organic- Ceramic Table','This unique dining table features a sculpted, organic shape with a smooth white concrete finish. The two cylindrical legs add a modern and minimalist touch. The table is perfect for adding a focal point to your dining room.',45000,'40a6a786e12b7996ef82ba05277672bb.jpg',4,12,26,17),(89,'Geometric Coffee Table','This modern coffee table features a unique geometric shape with a white top and a black base. The sleek lines and clean finish create a minimalist and contemporary look.',55000,'2240b57bd5255463a49b42e49f9d1163.jpg',4,12,41,17),(90,'Organic Modern Coffee-Table','This unique coffee table features a sculpted, organic shape with a smooth white finish. The two cylindrical legs add a modern and minimalist touch. It\'s perfect for adding a focal point to your living room.',45000,'71266e8705b1b6ce3aa79e784ca09990.jpg',4,12,32,18),(92,'Designer Ceramic Table ','This stylish coffee table features a large, rectangular marble top with a beautiful pattern. The curved base and open bottom shelf provide ample space for storage. Perfect for adding a touch of elegance and functionality to your living room.',80000,'f07c1e4b971ffd2461a682d9aebbf09d.jpg',4,12,29,18),(93,'Organic Ceramic Table Set','This unique coffee table set features two organic-shaped tables with textured finishes. The larger table has three legs, while the smaller one has a single leg. The set adds a modern and sculptural element to your living room.',45000,'f04655b958adc1d72ad7bfae75cbe95e.jpg',4,12,48,17),(94,'Modern Outdoor Dining Set','This stylish outdoor dining set features a round table with a textured top and four comfortable chairs with curved backs. The set is perfect for enjoying meals and socializing in your outdoor space',150000,'4f7d3801f907b1ef0f0a7bd42acebea5.jpg',4,13,7,18),(95,'Modern Outdoor Table Set','This stylish outdoor table set includes a large, round coffee table and a smaller side table. Both tables feature a modern, tapered design and a neutral gray finish. ',45000,'8d360adef6543d737360e83107b176c4.jpg',4,13,27,17),(96,'black Ceramic Dining Table','This sleek, rectangular dining table features a stylish, split design and a textured, gray top. The matching chairs with comfortable backrests and armrests are perfect for enjoying meals and socializing outdoors. The table is ideal for both small and large gatherings.',120000,'57c2ed54710fa0fa1ed4eb24a56f6fa9.jpg',4,13,16,18),(98,'White Ceramic Table','This stylish outdoor dining set features an oval table and a matching bench, both made from concrete for a durable and modern look. The set is perfect for creating a cozy and inviting outdoor seating area.',140000,'15176ccc1da1edd5fb30e7ab1093b27d.jpg',4,13,40,18),(100,'Modern Ceramic Dining-Set','This stylish outdoor dining set features a rectangular table with a slatted design and a white finish. The matching chairs offer comfortable seating with curved armrests. ',170000,'778580304beb80dab896a6595c130417.jpg',4,13,10,18),(101,'Ceramic Luxury Dining Set','This elegant outdoor dining set features a rectangular table with a concrete top and a modern, minimalist design. The woven chairs offer comfortable seating and a touch of natural texture.\r\n\r\n',160000,'b77b3b9e4e6ef1ff079f1d75bc74bd99.jpg',4,13,20,17),(102,'Ceramic Outdoor Dining Set','This stylish outdoor dining set features a large, oval table with a textured concrete finish and matching benches. The set\'s modern design and neutral color palette create a sophisticated and inviting atmosphere.',155000,'baf534127ccb9aa6b7237e963c7bea92.jpg',4,13,16,18),(105,'Modern Ceramic Dining Ensemble','This stylish outdoor dining set features an oval table with a ribbed base and matching benches, all made from durable concrete. The set\'s modern design and neutral color create a sophisticated and inviting atmosphere. ',145000,'dc4e8cc28c9374290b9705dd2f0f4013.jpg',4,13,17,17),(106,'Stylish Ceramic Dining Set','This sleek and stylish outdoor dining set features a rectangular table with a slatted design and a gray finish. The comfortable chairs with fabric backrests and armrests offer a relaxing seating experience.',175000,'f45e41ab066066cfa77c1b1b1ff97241.jpg',4,13,30,18),(107,'Freestanding Green Ceramic Bathtub','This elegant bathtub is crafted from solid surface material, offering a luxurious and durable bathing experience. Its oval shape and modern design make it a focal point in any bathroom.\r\n\r\n',175000,'111fff91c4528cfec225db9fd91ee65f.jpg',5,14,6,17),(108,'Freestanding Ceramic-Bathtub','This elegant bathtub is crafted from solid surface material, offering a luxurious and durable bathing experience. Its oval shape and modern design make it a focal point in any bathroom. ',150000,'7416c054bd3a4f116e8fe174017ea633.jpg',5,14,25,18),(109,'Freestanding White Bathtub','This elegant bathtub is crafted from solid surface material, offering a luxurious and durable bathing experience. Its oval shape and modern design make it a focal point in any bathroom. ',140000,'b10887f24bab84cd588fc1bfc812f5ff.jpg',5,14,13,17),(110,'Freestanding Ceramic soaking tub','This elegant bathtub is crafted from solid surface material, offering a luxurious and durable bathing experience. Its oval shape and modern design make it a focal point in any bathroom.',135000,'depositphotos_528416488-stock-photo-modern-ceramic-bathtub-towel-white.jpg',5,14,31,18),(111,'Light Gray Bathtub','This elegant bathtub is crafted from solid surface material, offering a luxurious and durable bathing experience. Its oval shape and modern design make it a focal point in any bathroom. ',125000,'8e6d08989eea96e904afc1947a093dc9.jpg',5,14,20,17),(112,'Freestanding Oval Bathtub with Marble Surround','This luxurious bathroom features a freestanding oval bathtub surrounded by stunning white Ceramic tiles.\r\n\r\n',140000,'images (1).jpg',5,14,49,18),(113,'Freestanding Ceramic Bath','This elegant bathtub is crafted from solid surface material, offering a luxurious and durable bathing experience',120000,'images (8).jpg',5,14,43,17),(114,' Stacked Ceramic Mugs','These are two minimalist ceramic mugs stacked on top of each other. They have a neutral gray color and a textured finish. The mugs have a unique shape with a wider base and a tapered top.',1500,'25e2170ad00ff4954356f1693b84fceb.jpg',3,6,17,18),(115,'Two-Tone Ceramic Cup','This elegant set includes a ceramic cup and saucer. The cup features a unique two-tone design with a dark-colored interior and a lighter-colored exterior. The saucer has a matching design and provides a stable base for the cup.',1200,'7385b6c3aa7e547acb37fb8195025ef4.jpg',3,6,45,17),(116,'Danish Ice Green Dinner Set','This elegant set of bowls is perfect for serving snacks, desserts, or even as a stylish storage container. The bowls are made from high-quality ceramic and feature a sleek, modern design.',3000,'2ed68bdf23ae25ac1592cd23be8aa4f2.jpg',3,7,29,18),(118,'Modern White Dinnerware Set','This elegant dinnerware set adds a touch of sophistication to your table. The clean, white design is versatile and complements any kitchen decor. The set includes a variety of plates, bowls, and mugs, making it perfect for everyday meals or special occasions. The durable ceramic construction ensures long-lasting use.',3500,'3483a5cee2217184303c473a3b359c83.jpg',3,7,5,18),(119,'Earthy Green Dinnerware Set',' This elegant dinnerware set brings a touch of nature to your table. The muted green color and organic shapes create a calming and inviting atmosphere. The set includes plates, bowls, and mugs, perfect for everyday meals or special occasions.',4500,'e628ce0a18662cd1f9d2674f7fc312bc.jpg',3,7,24,17),(121,'Sleek Black Dinnerware Set','This minimalist dinnerware set adds a touch of sophistication to your table. The deep black color and sleek design create a modern and elegant atmosphere. The set includes plates, bowls, mugs, and cutlery, perfect for everyday meals or special occasions',5500,'f11a40ff03119e541fcc0ae703e28b41.jpg',3,7,12,17),(122,'Modern Color Palette Dinnerware Set','This vibrant dinnerware set adds a touch of personality to your table. The plates and bowls come in a variety of colors, including white, pink, and blue, making them perfect for mixing and matching. The sleek design and durable ceramic construction ensure long-lasting use.',5000,'ab3b60513c78b90b0be110a1b357fdf8.jpg',3,7,27,18),(123,'Barazza Lab Evolution Induction Cooktop','his sleek and modern induction cooktop features a black glass surface and integrated controls for a streamlined look. It offers precise temperature control and rapid heating, making it perfect for various cooking tasks. The cooktop also includes a built-in hob, sink, and faucet, providing a complete cooking and cleaning solution in one unit.',80000,'3cc5dffb3d963bb733e5bbcd1d19f4c9.jpg',3,9,49,17),(124,'Sleek Black Kitchen Faucet','This modern kitchen faucet features a sleek black finish and a high-arc spout for easy filling of pots and pans. The faucet also includes a built-in hot water dispenser, perfect for making tea, coffee, or instant noodles.\r\n\r\n',15000,'4a3c37a34cfb8f98cc9b14ea1f4a6ae2.jpg',3,9,23,18),(125,'leek Black Undermount Sink','This minimalist sink features a sleek black finish and a rectangular undermount design for a clean and modern look. The sink is made from durable ceramic and includes a matching faucet in a chrome finish.',20000,'24b38e8e2045cf5306e0bf08379e9dbe.jpg',3,9,10,17),(126,'Modern Black Kitchen Faucet',' This sleek and modern kitchen faucet features a black finish and a high-arc spout for easy filling of pots and pans. The faucet has a minimalist design with clean lines and a single-lever handle for easy control. The black color adds a touch of sophistication to any kitchen.',25000,'95a72ac2ce01cb26e0bbb47cadf8a792.jpg',3,9,21,18),(127,'Ceramic Black Kitchen Faucet','This modern kitchen faucet features a sleek black finish and a high-arc spout for easy filling of pots and pans. The faucet has a minimalist design with clean lines and a single-lever handle for easy control.',45000,'95f40badca1026ec1cec541701ee6aed.jpg',3,9,25,17),(128,'Modern Black Kitchen Sink with Cutting Board','This sleek and functional kitchen sink features a black finish and a rectangular undermount design. The sink includes a built-in cutting board made from durable bamboo, providing a convenient workspace for food preparation. The matching black faucet adds to the modern aesthetic of the sink.',35000,'4337a94f804798456084da5b7c418bdd.jpg',3,9,13,18),(129,'Minimalist Black Vanity with Integrated Sink','This sleek and modern vanity features a dark gray finish and a built-in rectangular sink. The minimalist design creates a clean and uncluttered look, perfect for contemporary bathrooms',40000,'6613adb2c07a68f665f36571afc5f755.jpg',3,9,30,17),(130,'Modern Black Kitchen Sink with Accessories','This sleek and functional kitchen sink features a black finish and a double-basin design for efficient multitasking. The sink includes a built-in colander and chopping board, providing convenient workspace for food preparation.',45000,'130518f011449a2248a75f0258683b04.jpg',3,9,14,18),(131,'Organic Black and White Plate Set','This unique dinnerware set features a stunning combination of black and white, with organic shapes and textures that add a touch of artistry to your table. The set includes a large plate, a medium plate, and a small bowl, making it perfect for serving appetizers, main courses, or desserts.',900,'b184d353b52a4e689d851b86fa2f0d24.jpg',3,10,24,17),(132,'Ebony Wood Serving Bowl','This elegant and versatile bowl is crafted from solid ebony wood, known for its rich, dark color and natural durability. The bowl\'s smooth, organic shape and deep, lustrous finish make it a stunning centerpiece for any table. ',1200,'fc2172f517e0eb9d79688e6144404b78.jpg',3,10,25,18),(133,'Organic Ceramic Tray','This unique tray features a wavy, organic shape and a soft, muted color. Made from high-quality ceramic, it\'s perfect for serving snacks, desserts, or as a decorative piece. The tray\'s textured surface adds a touch of interest to any table setting.',1500,'d30748d35ec25ac5c4c7311ed2917af7.jpg',3,10,50,17),(134,'Serving Platter','This elegant platter features a unique, organic shape and a deep black finish. It\'s perfect for serving appetizers, desserts, or as a decorative piece. The platter\'s textured surface adds a touch of interest to any table setting.',1300,'e8272b550b00edcc6d26f79bfca2aa7c.jpg',3,10,32,18),(135,'ceramic  Dining Set','This elegant dining set features a round ceramic table with a sleek black base and a set of four upholstered chairs. The chairs have a modern design with black, white and gray fabric seats.',60000,'ba7b6242940c359760ed40ce740051e4.jpg',4,11,6,17),(136,'Ceramic Black Dining Set','This sleek and stylish dining set features a rectangular black table with a unique base and a set of six upholstered chairs. The chairs have a modern design with black metal legs and brown leather seats. The set is perfect for creating a contemporary and inviting dining space.',75000,'e39eb58f70632c2ccaa82754af44c68a.jpg',4,11,23,18),(137,'Hayden Light Grey Ceramic','If you\'re looking to add a statement to your dining room with all the durable qualities of a ceramic table, look no further than the Hayden Ceramic Table. ',45000,'be5c8831ed1dad648a725ee54f3b5381.jpg',4,12,44,17),(138,' Modern white Freestanding Bathtub','This elegant bathtub features a sleek, rectangular design with four raised legs. The bathtub is made from a high-quality material, such as acrylic or cast iron, and is designed to stand alone in your bathroom.',60000,'dc680a2d35711c16c84c7c4c8e7555d5.jpg',5,14,9,18),(139,'Sleek Black Freestanding Bathtub','This elegant bathtub features a modern, oval design and a deep black finish. The bathtub is made from a high-quality material, such as acrylic or cast iron, and is designed to stand alone in your bathroom.\r\n\r\n',85000,'6fd8ce6e1012f19a45ceab1aa5080dd3.jpg',5,14,48,17),(140,'Vintage-Inspired Bathroom Vanity','This elegant bathroom vanity features a dark-colored countertop with a white oval sink. The vanity is paired with a vintage-inspired mirror and brass fixtures, including a faucet, towel rail, and shelf.',90000,'dc3003a40ed0b9b6dcd0c2b381c06df3.jpg',5,15,26,18),(141,'Modern Rectangular Bathroom Sink','This sleek and stylish bathroom sink features a rectangular shape and a clean, white finish. The sink is made from high-quality ceramic and is designed for above-counter installation.',75000,'e52e917b4af7b1a1486193a7c6f4c887.jpg',5,15,24,17),(142,'Modern Rectangular Basin','This stylish bathroom sink features a rectangular shape and a sleek, two-tone design. The interior is white, while the exterior is a vibrant teal color. ',50000,'shopping (3).webp',5,15,39,18),(143,'Modern Gray Bathroom Sink','This stylish bathroom sink features a rectangular shape and a sleek, gray finish. The sink is made from high-quality ceramic and is perfect for adding a touch of modern elegance to your bathroom.',60000,'shopping (5).webp',5,15,27,17),(146,'Modern Round Basin','This sleek and stylish bathroom sink features a round shape and a clean, white finish. The sink is made from high-quality ceramic and is perfect for adding a modern touch to your bathroom.',20000,'6edd5940d713751e6fc4fb6ee3bc5f2d.jpg',5,15,15,18),(147,'Modern Green Ceramic Basin','This stylish bathroom sink features a rectangular shape and a vibrant green color. The sink is made from high-quality ceramic and is perfect for adding a touch of color and modern elegance to your bathroom',65000,'shopping (8).webp',5,15,35,17),(148,'Matte Black Rectangular Basin','This sleek and modern bathroom sink features a rectangular shape and a matte black finish. It\'s made from high-quality ceramic and is perfect for adding a touch of sophistication to your bathroom. ',40000,'shopping (9).webp',5,15,32,18),(149,'Modern Black Vessel Sink','This sleek and stylish bathroom sink features a round shape and a deep black finish. It\'s made from high-quality ceramic and is perfect for adding a modern touch to your bathroom. ',35000,'716842b706f75778566b08901f65a7c4.jpg',5,15,5,17),(150,'Matte Black Square Basin','This stylish bathroom sink features a sleek, square shape and a deep black finish. It\'s made from high-quality ceramic and is perfect for adding a modern touch to your bathroom. ',45000,'d7cad4c58cf985e45e899d466beb778b.jpg',5,15,18,18),(151,'Ceramic Foam Soap Dispenser','This stylish soap dispenser features a textured ceramic body in a soft pink color. It has a gray pump that dispenses a foamy lather, making handwashing more fun and effective.\r\n\r\n',800,'1a98ef02cb7430b64b9f260dd35113ea.jpg',5,16,23,17),(152,'Pink Ceramic Soap Dispenser','This elegant soap dispenser features a soft pink ceramic body and a gold-toned pump. It\'s perfect for adding a touch of color and style to your bathroom or kitchen.\r\n\r\n',900,'4d5343ee13f67341a4b164c4c3e64ca7.jpg',5,16,11,18),(153,'Rustic Ceramic Soap Dispenser','This stylish soap dispenser features a textured stoneware body in a soft gray color. It has a copper-toned pump that adds a touch of elegance and complements the natural look of the ceramic.\r\n\r\n',1100,'07f3510db1ca5b8efb685053da39d92c.jpg',5,16,29,17),(154,'Matte Ceramic Soap Dispenser with Brass Pump','This elegant soap dispenser features a soft, matte finish and a sleek, rounded shape. The dispenser is made from high-quality ceramic and has a brass pump that adds a touch of luxury.\r\n\r\n',1600,'9d23cc1e343c459ac1b0703b5fb7c679.jpg',5,16,13,18),(155,'Matte Green Soap Dispenser with Brass Pump','It\'s made from high-quality ceramic and has a brass pump that adds a touch of luxury. Perfect for adding a touch of sophistication to your bathroom or kitchen.',1400,'74ab3e591fd5bd1b9aa618977a42af8c.jpg',5,16,20,17),(156,'Beige Bathroom Accessory Set','This stylish bathroom set includes a soap dispenser, a toothbrush holder, and a soap dish, all in a coordinating beige color. The set is made from high-quality ceramic and features a modern, minimalist design.',1200,'302c2077482fa064ab47f07848359415.jpg',5,16,9,18),(157,'Earthenware Soap Dispenser Set','This stylish set of soap dispensers features a rustic, earthy finish and a unique, textured design. The dispensers come in various sizes, making them perfect for holding different types of liquids, such as hand soap, lotion, or shampoo.',2000,'856f8a6099701cbc687f9fd069042401.jpg',5,16,30,17),(158,'Ceramic Soap Dispenser Set','This stylish set of soap dispensers features a soft pink ceramic body and a gray pump. The dispensers come in various sizes, making them perfect for holding different types of liquids, such as hand soap, lotion, or shampoo. ',2400,'b8d158e9ef417afabd6c65906e95dddc.jpg',5,16,24,18),(159,'Modern Beige Soap Dispenser','This sleek and stylish soap dispenser features a soft, beige color and a rounded shape. It\'s made from high-quality ceramic and has a modern, minimalist design. ',900,'d6e8c0101a162688bf69bb733f9bb935.jpg',5,16,25,17),(160,'Black Wall-Hung Toilet','This sleek and modern toilet features a black finish and a wall-mounted design for a clean and contemporary look. The toilet is equipped with a dual flush system for water conservation and a soft-close seat for quiet operation.',15000,'2a0f77ed253eb4acda024fae93f99853.jpg',5,17,48,18),(161,'Modern Black Smart Toilet','This sleek and futuristic toilet features a black finish and a unique egg-shaped design. It\'s equipped with advanced features like automatic flushing, heated seat, and built-in bidet for a luxurious and hygienic experience.',11000,'7e7276c424c90edc3a0b569eca5eb681.jpg',5,17,23,17),(162,'Modern Gray Toilet Suite','This sleek and stylish bathroom suite features a wall-hung toilet and bidet in a matching gray color. The toilet has a soft-close seat and a dual flush system for water efficiency. ',12000,'851cbd14fc2eacb50cdabff7237fc971.jpg',5,17,12,18),(163,'Matte Black Wall-Hung Toilet','This sleek and modern toilet features a black finish and a wall-mounted design for a clean and contemporary look. The toilet is equipped with a dual flush system for water conservation and a soft-close seat for quiet operation.',12000,'9a281f3f23605f10e57643761f75e0ba.jpg',5,17,32,17),(164,'Modern Brown Toilet Suite','This stylish bathroom suite features a wall-hung toilet and bidet in a matching brown color. The toilet has a soft-close seat and a dual flush system for water efficiency. The bidet is equipped with a modern faucet for easy use. ',16000,'7335c0ac86dd4f738403d3f59e141898.jpg',5,17,27,18),(165,'Catalano SCSTPAS Toilet Suite','This sleek and modern bathroom suite features a wall-hung toilet and bidet in a light blue color. The toilet has a soft-close seat and a dual flush system for water efficiency.',14000,'bb11a5851ae86940ae1812f69476fba0.jpg',5,17,34,17),(166,'Globo Lalita Back to Wall Bidet and Toilet','This sleek and modern bathroom suite features a wall-hung toilet and bidet in a matching gray color. The toilet has a soft-close seat and a dual flush system for water efficiency. ',17000,'c3b95b152fa3bc6113b97a89741127ac.jpg',5,17,39,18),(167,'Cielo Le Giare Toilet Suite',' This sleek and modern bathroom suite features a wall-hung toilet and bidet in a matching gray color. The toilet has a soft-close seat and a dual flush system for water efficiency. ',17000,'083273c9a7bbb7cd0350a02696ab5711.jpg',5,17,44,17),(169,'Duravit Vero Air Rim Toilet','This sleek and modern toilet features a black finish and a rimless design for enhanced hygiene. The toilet is wall-mounted and comes with a soft-close seat for quiet operation.\r\n\r\n',18000,'2a0f77ed253eb4acda024fae93f99853.jpg',5,17,7,17),(170,'Virtune Premium Matte Black ','This sleek and modern bathroom accessory set includes a soap dispenser, toothbrush holder, cotton swab holder, and razor holder. All pieces are made from high-quality matte black material, providing a cohesive and stylish look for your bathroom. ',2500,'2a0e33978144bcb72de688369a3b5f20.jpg',5,18,35,18),(171,'Duravit Vero Air Caddy','This sleek and modern shower caddy is designed to fit seamlessly with the Duravit Vero series of bathroom fixtures. It features a minimalist design with a rectangular shape and a white finish.',3000,'9fb3cef2a7f63e15ed114f012a9e7bf4.jpg',5,18,13,17),(172,'Concrete Bathroom Organizer Set','This stylish and functional bathroom set includes a toothbrush holder, a soap dispenser, a cup, and a small tray. The set is made from concrete, giving it a rustic and modern look. ',4000,'32b39c1b6e131fef1575f57ae195094c.jpg',5,18,47,18),(173,'Bamboo Toothbrush Holder Set','This eco-friendly bathroom set includes a ceramic toothbrush holder filled with bamboo toothbrushes. The set is paired with a vase holding dried pampas grass, creating a natural and minimalist aesthetic. ',2500,'39d22622b2b6ff11da030ddf7593c3c1.jpg',5,18,6,17),(174,' White Ceramic Bathroom Accessory Set','This stylish bathroom set includes a soap dispenser, a toothbrush holder, a wastebasket with a lid, and a soap dish, all in a coordinating white color. The set is made from high-quality ceramic and features a textured finish for added interest. ',3000,'4787b1ce7e46fa7f8a80ae3b4d6d2701.jpg',5,18,22,18),(175,'JIALTO 2 Pcs Corner Shelf for Bathroom','This practical and space-saving bathroom shelf set offers two corner shelves for storing toiletries and other bathroom essentials. The shelves are made from durable aluminum and feature a sleek, modern design.\r\n\r\n',3500,'15410614ff7fee8f2308bd9b95b43a87.jpg',5,18,42,17),(176,'Terrazzo Bathroom Accessory Set','This stylish and modern bathroom set includes a soap dispenser, a toothbrush holder, a cotton swab holder, a toilet brush holder, and a soap dish. All pieces are made from terrazzo, a composite material known for its speckled appearance and durability. ',2000,'b3fee0958770a493f5a3521578664ffa.jpg',5,18,48,18),(177,'Textured Ceramic Bathroom Organizer','This stylish and functional bathroom organizer features a textured ceramic design and includes compartments for a toothbrush, toothpaste, and soap. The organizer is perfect for keeping your bathroom essentials tidy and adding a touch of modern elegance to your space.',2200,'3dbd60abef9009d855235d39ada8247e.jpg',5,18,19,17),(178,' Textured Ceramic Bathroom Accessory Set','This stylish and functional bathroom set includes a soap dispenser, a tumbler, and a tray. The set is made from ceramic with a textured finish, giving it a modern and elegant look. ',3100,'6f639bcb475f778382e944c741e80803.jpg',5,18,37,18),(179,'Skull Glasses Holder','This unique and quirky skull-shaped glasses holder is both stylish and practical. Made of high-quality ceramic, it offers a perfect place to rest your glasses when not in use. The skull design adds a cool touch to any desk or table, and the holder can double as an eye-catching decoration. Its neutral colors make it a great fit for modern home or office setups. A perfect gift for anyone who tends to misplace their glasses!',499,'1fbbc128eb4c737eae98db565fee46f1.jpg',7,20,32,17),(180,'Hugging Couple Planter','This elegant and modern planter, shaped like two embracing figures, adds a touch of warmth and creativity to your living space. The minimalist white design is perfect for small indoor plants or decorative items. The planter can hold small potted plants or succulents, bringing a refreshing vibe to any table, shelf, or office desk. The embracing figures symbolize love and unity, making it a thoughtful gift for special occasions or to enhance your home d??cor.',700,'03a2fbdd4545f04333df447556550845.jpg',7,20,46,18),(181,'Handy Page Holder','This practical and stylish page holder makes reading much more comfortable. Shaped ergonomically to fit your thumb, it allows you to keep your book open with ease, freeing up the other hand. Made from lightweight and durable material, it???s perfect for long reading sessions or for multitasking. The speckled design adds a modern, minimalist touch to your reading accessories. Ideal for book lovers and a great gift for any reader!',300,'4f25a8db3c09875937564b6274388cab.jpg',7,20,38,17),(182,' The Wave Tissue Box','Ad Name: The Wave Tissue Box\r\nDescription:\r\n\r\nElevate your living space with the Wave Tissue Box, a sleek and modern addition to your decor. Its unique, wavy design adds a touch of sophistication to any room. Crafted from high-quality ceramic, this tissue box is both durable and stylish. The box comes with a wooden tray for a complete and elegant look. Keep your tissues organized and easily accessible while adding a touch of luxury to your home.',440,'5e4eeea6626ecfbd2f540d8ff99ab13a.jpg',7,20,48,18),(183,' The Swirl Makeup Brush Holder','Organize your makeup brushes in style with the Swirl Makeup Brush Holder. Its unique, swirling design adds a touch of elegance to your vanity. Made from high-quality ceramic, this holder is both durable and stylish. The holder\'s open design allows for easy access to your brushes while keeping them neatly stored. Perfect for storing various types of makeup brushes, including foundation brushes, powder brushes, and blush brushes.',550,'0317e4596110dd9f08aed75974bdff7e.jpg',7,20,29,17),(184,' The Folded Lamp','Add a unique touch to your space with the Folded Lamp. Its innovative, folded design creates a captivating and sculptural look. Crafted from textured clay, this lamp is both functional and artistic. The soft, warm light emitted by the lamp creates a cozy and inviting atmosphere. The lamp\'s unique shape and textured finish make it a conversation starter and a beautiful addition to any room.',1700,'0151a26c99bcddc539a50e280e724e52.jpg',7,20,42,18),(185,'The Fisherman Incense Burner','Create a serene atmosphere with the Fisherman Incense Burner. This unique and artistic piece features a charming fisherman figure sitting on a boat, holding an incense stick. The smoke rises from the incense, creating a peaceful and calming ambiance. The burner is made from high-quality ceramic and is perfect for meditation, yoga, or simply relaxing at home. Add a touch of tranquility to your space with this beautiful and functional incense burner.',2000,'795b28a049461d8de45949dd5fcad4c9.jpg',7,20,25,17),(186,'The Hand Bookends','Add a touch of artistic flair to your bookshelf with the Hand Bookends. These unique and sculptural bookends are shaped like two hands, holding your books upright. Made from high-quality ceramic, these bookends are both functional and decorative. The minimalist design allows them to blend seamlessly with any decor style. Use them to display your favorite books while adding a unique and eye-catching element to your space.',1500,'1051b4628969fe868b670f2ef1937b2b.jpg',7,20,44,18),(187,'The Wavy Tablet & Cookbook Holder','Keep your tablet or cookbook upright and organized with the Wavy Tablet & Cookbook Holder. Its unique, wavy design adds a touch of style to your kitchen or dining table. Made from durable ceramic, this holder is both functional and decorative. The holder\'s angled design provides easy access to your device or cookbook while keeping it securely in place. Perfect for recipe books, tablets, or even small notebooks, this versatile holder is a must-have for any kitchen or home office.',1100,'d8f984ef454bf17ac96480876ffda071.jpg',7,20,45,17),(188,'The Ceramic Bathroom Trio','Elevate your bathroom with this stylish and functional ceramic trio. The set includes a tissue box, a toothbrush holder, and a soap dish, all designed to complement each other. The minimalist, white design adds a touch of elegance to your bathroom decor. Made from high-quality ceramic, these pieces are both durable and easy to clean. Keep your bathroom essentials organized and stylish with this beautiful and practical set.',2400,'e0b21953382a9aa5444aecf30f9dfc5b.jpg',7,20,46,18),(189,' The Ruffled Vase','Add a touch of elegance to your home with the Ruffled Vase. Its unique, ruffled design creates a captivating and sculptural look. Crafted from textured ceramic, this vase is both functional and artistic. The vase is perfect for displaying a variety of flowers, from fresh bouquets to dried arrangements. The ruffled texture and organic shape make it a beautiful centerpiece for any room.',999,'9fbe669153d2c57fb2dbb558325ead65.jpg',7,20,42,17);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subcategory`
--

DROP TABLE IF EXISTS `subcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subcategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subcategory`
--

LOCK TABLES `subcategory` WRITE;
/*!40000 ALTER TABLE `subcategory` DISABLE KEYS */;
INSERT INTO `subcategory` VALUES (6,3,'Cups'),(7,3,'Dinner Set'),(8,3,'Jugs'),(9,3,'Kitchen Sink'),(10,3,'Platters'),(11,4,'Kitchen Table'),(12,4,'Livingroom Table'),(13,4,'Outdoor Table'),(14,5,'Bath'),(15,5,'Bath Sink'),(16,5,'Bottle'),(17,5,'Sanatory'),(18,5,'Stand'),(20,7,'Artist');
/*!40000 ALTER TABLE `subcategory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'user',
  `login_otp` varchar(6) DEFAULT NULL,
  `otp_created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (7,'admin','admin@gmail.com','admin','admin',NULL,NULL),(9,'hk','hk@gmail.com','hk123','user',NULL,NULL),(10,'vpatel','v_patel@gmail.com','1824','user',NULL,NULL),(12,'jyoti','j@gmail.com','123','user',NULL,NULL),(13,'Sarthak Shekhada','sarthakshekhada8@gmail.com','1234','user',NULL,NULL),(17,'seller1','seller@gmail.com','seller123','seller',NULL,NULL),(18,'seller2','seller2@gmail.com','seller123','seller',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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

-- Dump completed on 2026-03-20  0:41:22
