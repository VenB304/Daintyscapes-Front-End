-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: localhost    Database: daintyscapes
-- ------------------------------------------------------
-- Server version	8.0.40

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
  `address_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `country` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `house_number` varchar(20) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  PRIMARY KEY (`address_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (3,3,'Philippines','Santa Clara','Totod','23','3746');
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `buyers`
--

DROP TABLE IF EXISTS `buyers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `buyers` (
  `buyer_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone_number` varchar(17) NOT NULL,
  PRIMARY KEY (`buyer_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone_number` (`phone_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `buyers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buyers`
--

LOCK TABLES `buyers` WRITE;
/*!40000 ALTER TABLE `buyers` DISABLE KEYS */;
INSERT INTO `buyers` VALUES (3,3,'kusagaki@gmail.com','09386254685');
/*!40000 ALTER TABLE `buyers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `charms`
--

DROP TABLE IF EXISTS `charms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `charms` (
  `charm_id` int NOT NULL AUTO_INCREMENT,
  `charm_name` varchar(30) NOT NULL,
  `charm_base_price` decimal(19,4) NOT NULL,
  `charm_image_url` varchar(255) NOT NULL,
  PRIMARY KEY (`charm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `charms`
--

LOCK TABLES `charms` WRITE;
/*!40000 ALTER TABLE `charms` DISABLE KEYS */;
INSERT INTO `charms` VALUES (1,'Car',25.0000,'\\daintyscapes\\assets\\img\\charms\\car.png'),(2,'Elephant',25.0000,'\\daintyscapes\\assets\\img\\charms\\elephant.png'),(3,'Mustache',25.0000,'\\daintyscapes\\assets\\img\\charms\\mustache.png'),(4,'Planet',25.0000,'\\daintyscapes\\assets\\img\\charms\\planet.png'),(5,'Small Jet',25.0000,'\\daintyscapes\\assets\\img\\charms\\small_jet.png'),(6,'Steth',25.0000,'\\daintyscapes\\assets\\img\\charms\\steth.png');
/*!40000 ALTER TABLE `charms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customization_charms`
--

DROP TABLE IF EXISTS `customization_charms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customization_charms` (
  `customization_charm_id` int NOT NULL AUTO_INCREMENT,
  `customization_id` int NOT NULL,
  `charm_id` int NOT NULL,
  `x_position` int NOT NULL,
  `y_position` int NOT NULL,
  PRIMARY KEY (`customization_charm_id`),
  KEY `customization_id` (`customization_id`),
  KEY `charm_id` (`charm_id`),
  CONSTRAINT `customization_charms_ibfk_1` FOREIGN KEY (`customization_id`) REFERENCES `customizations` (`customization_id`) ON DELETE CASCADE,
  CONSTRAINT `customization_charms_ibfk_2` FOREIGN KEY (`charm_id`) REFERENCES `charms` (`charm_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customization_charms`
--

LOCK TABLES `customization_charms` WRITE;
/*!40000 ALTER TABLE `customization_charms` DISABLE KEYS */;
INSERT INTO `customization_charms` VALUES (1,2,1,588,575),(2,4,1,588,575),(3,6,1,588,575),(4,8,1,588,575),(5,10,1,588,575),(6,12,1,588,575),(7,14,1,588,575),(8,15,2,215,495),(9,16,3,223,63);
/*!40000 ALTER TABLE `customization_charms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customizations`
--

DROP TABLE IF EXISTS `customizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customizations` (
  `customization_id` int NOT NULL AUTO_INCREMENT,
  `buyer_id` int NOT NULL,
  `customized_name` varchar(9) DEFAULT NULL,
  `customized_name_color` varchar(50) DEFAULT NULL,
  `customization_cost` decimal(19,4) NOT NULL,
  PRIMARY KEY (`customization_id`),
  KEY `buyer_id` (`buyer_id`),
  CONSTRAINT `customizations_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`buyer_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customizations`
--

LOCK TABLES `customizations` WRITE;
/*!40000 ALTER TABLE `customizations` DISABLE KEYS */;
INSERT INTO `customizations` VALUES (1,3,'213','#7b4a1e',0.0000),(2,3,'My Nigga','#e9d7b9',0.0000),(3,3,'213','#7b4a1e',0.0000),(4,3,'My Nigga','#e9d7b9',0.0000),(5,3,'213','#7b4a1e',0.0000),(6,3,'My Nigga','#e9d7b9',0.0000),(7,3,'213','#7b4a1e',0.0000),(8,3,'My Nigga','#e9d7b9',0.0000),(9,3,'213','#7b4a1e',0.0000),(10,3,'My Nigga','#e9d7b9',0.0000),(11,3,'213','#7b4a1e',0.0000),(12,3,'My Nigga','#e9d7b9',0.0000),(13,3,'213','#7b4a1e',0.0000),(14,3,'My Nigga','#e9d7b9',0.0000),(15,3,'123456789','#7b4a1e',0.0000),(16,3,'','#e9d7b9',0.0000);
/*!40000 ALTER TABLE `customizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_details`
--

DROP TABLE IF EXISTS `order_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_details` (
  `order_detail_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `customization_id` int DEFAULT NULL,
  `charm_id` int DEFAULT NULL,
  `charm_name` varchar(30) DEFAULT NULL,
  `variant_name` varchar(50) NOT NULL,
  `variant_url` varchar(255) NOT NULL,
  `order_quantity` int NOT NULL,
  `base_price_at_order` decimal(19,4) NOT NULL,
  `total_price_at_order` decimal(19,4) NOT NULL,
  PRIMARY KEY (`order_detail_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  KEY `customization_id` (`customization_id`),
  KEY `charm_id` (`charm_id`),
  CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `order_details_ibfk_3` FOREIGN KEY (`customization_id`) REFERENCES `customizations` (`customization_id`) ON DELETE SET NULL,
  CONSTRAINT `order_details_ibfk_4` FOREIGN KEY (`charm_id`) REFERENCES `charms` (`charm_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_details`
--

LOCK TABLES `order_details` WRITE;
/*!40000 ALTER TABLE `order_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status`
--

DROP TABLE IF EXISTS `order_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_status` (
  `status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) NOT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status`
--

LOCK TABLES `order_status` WRITE;
/*!40000 ALTER TABLE `order_status` DISABLE KEYS */;
INSERT INTO `order_status` VALUES (1,'Pending'),(2,'Shipped'),(3,'Delivered'),(4,'Cancelled'),(5,'Processing');
/*!40000 ALTER TABLE `order_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `buyer_id` int NOT NULL,
  `status_id` int NOT NULL,
  `order_date` date NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `buyer_id` (`buyer_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`buyer_id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `order_status` (`status_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
INSERT INTO `product_categories` VALUES (1,'Passport'),(2,'Multi-use Wristlet');
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `variant_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `variant_name` varchar(50) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  PRIMARY KEY (`variant_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (13,4,'Green','\\daintyscapes\\assets\\img\\Synthetic Green.png'),(14,4,'Turqouise Green','\\daintyscapes\\assets\\img\\Synthetic Turqouise Green.png'),(15,4,'Violet','\\daintyscapes\\assets\\img\\Synthetic Violet.png'),(18,1,'Beige','\\daintyscapes\\assets\\img\\Passport Matte Leather Beige.png'),(19,1,'Black','\\daintyscapes\\assets\\img\\Passport Matte Leather Black.png'),(20,1,'Brown','\\daintyscapes\\assets\\img\\Passport Matte Leather Brown.png'),(21,1,'Gray','\\daintyscapes\\assets\\img\\Passport Matte Leather Gray.png'),(22,1,'Green','\\daintyscapes\\assets\\img\\Passport Matte Leather Green.png'),(23,1,'Orange','\\daintyscapes\\assets\\img\\Passport Matte Leather Orange.png'),(24,5,'Navy Blue','\\daintyscapes\\assets\\img\\Passport Metallic Navy Blue.png'),(25,5,'Maroon','\\daintyscapes\\assets\\img\\Passport Metallic Maroon.png'),(26,5,'Pink','\\daintyscapes\\assets\\img\\Passport Metallic Pink.png'),(33,2,'Gold','\\daintyscapes\\assets\\img\\Multi-use Wristlet Gold.png'),(34,2,'Light Gray','\\daintyscapes\\assets\\img\\Multi-use Wristlet Light Gray.png'),(35,2,'Light Pastel Green','\\daintyscapes\\assets\\img\\Multi-use Wristlet Light Pastel Green.png'),(36,2,'Red','\\daintyscapes\\assets\\img\\Multi-use Wristlet Red.png'),(37,6,'Black','\\daintyscapes\\assets\\img\\Passport Metallic Black with Lock.png'),(38,6,'Navy Blue','\\daintyscapes\\assets\\img\\Passport Metallic Navy Blue with Lock.png');
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `available_quantity` int NOT NULL DEFAULT '0',
  `base_price` decimal(19,4) NOT NULL,
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Matte Leather Passport Holder',15,225.0000),(2,2,'Multi-use Wristlet',45,150.0000),(4,1,'Synthetic Passport Holder',55,225.0000),(5,1,'Metallic Passport Holder',50,225.0000),(6,1,'Metallic Passport Holder with Lock',50,250.0000);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(100) NOT NULL,
  `role` enum('buyer','seller','admin') NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Kusu','gaki','admin','$2y$10$ysnGZt/sCBerBI2P6KX1dOWMevfPPZ8iAuZQpf9uPmm2SWTwjI5Ue','admin'),(2,'Kusu','gaki','seller','$2y$10$ysnGZt/sCBerBI2P6KX1dOWMevfPPZ8iAuZQpf9uPmm2SWTwjI5Ue','seller'),(3,'Kusu','gaki','buyer','$2y$10$3nsFyHepzwEWkZicRJ1Lf.L7ZYNvQK2it9V7KSgbWy7eoY2.eAgPW','buyer');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-14 15:19:05
