-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: vostok
-- ------------------------------------------------------
-- Server version	8.0.44-0ubuntu0.24.04.1

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
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `society_id` smallint unsigned DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `warehouse` enum('history','planning','trashcan') NOT NULL DEFAULT 'history' COMMENT 'L''emplacement où est consigné l''évènement',
  `user_id` tinyint unsigned DEFAULT NULL,
  `user_position` enum('active','passive') DEFAULT NULL,
  `type` tinytext,
  `media` tinytext,
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `society_id` (`society_id`),
  KEY `user_id` (`user_id`),
  KEY `warehouse` (`warehouse`),
  CONSTRAINT `event_ibfk_1` FOREIGN KEY (`society_id`) REFERENCES `society` (`society_id`) ON DELETE CASCADE,
  CONSTRAINT `event_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_involvement`
--

DROP TABLE IF EXISTS `event_involvement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_involvement` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `event_id` int unsigned NOT NULL,
  `individual_id` smallint unsigned NOT NULL,
  `role` tinytext COMMENT 'Le rôle tenu par la personne impliquée',
  `comment` text,
  PRIMARY KEY (`id`),
  KEY `individual_id` (`individual_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `event_involvement_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_involvement_ibfk_3` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`individual_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `individual`
--

DROP TABLE IF EXISTS `individual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `individual` (
  `individual_id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `individual_x_id` varchar(15) DEFAULT NULL,
  `individual_linkedin_id` tinytext,
  `individual_instagram_id` tinytext,
  `individual_creation_date` datetime DEFAULT NULL,
  `individual_creation_user_id` tinyint unsigned DEFAULT NULL,
  `individual_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `individual_salutation` varchar(5) DEFAULT NULL,
  `individual_firstName` varchar(25) DEFAULT NULL,
  `individual_lastName` varchar(25) DEFAULT NULL,
  `individual_birth_date` date DEFAULT NULL,
  `individual_phone` varchar(25) DEFAULT NULL,
  `individual_email` varchar(50) DEFAULT NULL,
  `individual_street` tinytext,
  `individual_city` varchar(50) DEFAULT NULL,
  `individual_postalCode` mediumint unsigned DEFAULT NULL,
  `individual_state` varchar(35) DEFAULT NULL,
  `individual_country` varchar(50) DEFAULT NULL,
  `individual_mobile` varchar(25) DEFAULT NULL,
  `individual_description` mediumtext,
  `individual_web` tinytext,
  `individual_lastPin_date` datetime DEFAULT NULL,
  PRIMARY KEY (`individual_id`),
  KEY `individual_creation_user_id` (`individual_creation_user_id`),
  CONSTRAINT `individual_ibfk_1` FOREIGN KEY (`individual_creation_user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4871 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `industry`
--

DROP TABLE IF EXISTS `industry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `industry` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `name` tinytext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lead`
--

DROP TABLE IF EXISTS `lead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead` (
  `lead_id` int unsigned NOT NULL AUTO_INCREMENT,
  `lead_creation_date` datetime DEFAULT NULL,
  `lead_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `lead_lastmodification_user_id` tinyint unsigned DEFAULT NULL,
  `lead_assignment_user_id` tinyint unsigned DEFAULT NULL,
  `lead_creation_user_id` tinyint unsigned DEFAULT NULL,
  `lead_refered_by` varchar(100) DEFAULT NULL,
  `lead_shortdescription` tinytext,
  `lead_type` tinytext,
  `lead_status` enum('relevée','à suivre','suivie','abandonnée') NOT NULL DEFAULT 'relevée',
  `lead_source` varchar(100) DEFAULT NULL,
  `lead_source_description` text,
  `lead_description` text,
  `individual_id` smallint unsigned DEFAULT NULL,
  `society_id` smallint unsigned DEFAULT NULL,
  PRIMARY KEY (`lead_id`),
  KEY `individual_id` (`individual_id`),
  KEY `society_id` (`society_id`),
  KEY `lead_lastmodification_user_id` (`lead_lastmodification_user_id`),
  KEY `lead_assignment_user_id` (`lead_assignment_user_id`),
  KEY `lead_creation_user_id` (`lead_creation_user_id`),
  CONSTRAINT `lead_ibfk_1` FOREIGN KEY (`lead_lastmodification_user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `lead_ibfk_2` FOREIGN KEY (`lead_assignment_user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `lead_ibfk_3` FOREIGN KEY (`lead_creation_user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `lead_ibfk_4` FOREIGN KEY (`society_id`) REFERENCES `society` (`society_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=796 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `membership`
--

DROP TABLE IF EXISTS `membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `membership` (
  `membership_id` int unsigned NOT NULL AUTO_INCREMENT,
  `individual_id` smallint unsigned DEFAULT NULL,
  `society_id` smallint unsigned DEFAULT NULL,
  `title` tinytext,
  `department` tinytext,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `url` tinytext,
  `description` mediumtext,
  `weight` smallint unsigned NOT NULL DEFAULT '0',
  `init_year` year DEFAULT NULL,
  `end_year` year DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`membership_id`),
  KEY `individual_id` (`individual_id`),
  KEY `society_id` (`society_id`),
  CONSTRAINT `membership_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individual` (`individual_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `membership_ibfk_2` FOREIGN KEY (`society_id`) REFERENCES `society` (`society_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7863 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `relationship`
--

DROP TABLE IF EXISTS `relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `relationship` (
  `relationship_id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `item0_class` enum('Individual','Society') DEFAULT NULL,
  `item1_class` enum('Individual','Society') DEFAULT NULL,
  `item0_id` smallint unsigned DEFAULT NULL,
  `item1_id` smallint unsigned DEFAULT NULL,
  `item0_role` tinytext,
  `item1_role` tinytext,
  `description` text,
  `url` tinytext,
  `init_year` year DEFAULT NULL,
  `end_year` year DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`relationship_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2717 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `society`
--

DROP TABLE IF EXISTS `society`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `society` (
  `society_id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `society_creation_date` date DEFAULT NULL,
  `society_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `society_lastModification_user_id` tinyint unsigned DEFAULT NULL,
  `society_creation_user_id` tinyint unsigned DEFAULT NULL,
  `society_name` varchar(150) DEFAULT NULL,
  `society_street` varchar(150) DEFAULT NULL,
  `society_city` varchar(100) DEFAULT NULL,
  `society_administrativeAreaName` tinytext,
  `society_subAdministrativeAreaName` tinytext,
  `society_postalcode` varchar(20) DEFAULT NULL,
  `society_countryNameCode` varchar(3) DEFAULT NULL,
  `society_description` text,
  `society_phone` varchar(25) DEFAULT NULL,
  `society_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`society_id`),
  KEY `society_name` (`society_name`),
  KEY `society_lastModification_user_id` (`society_lastModification_user_id`),
  KEY `society_creation_user_id` (`society_creation_user_id`),
  CONSTRAINT `society_ibfk_1` FOREIGN KEY (`society_creation_user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3473 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `society_industry`
--

DROP TABLE IF EXISTS `society_industry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `society_industry` (
  `society_id` smallint unsigned NOT NULL DEFAULT '0',
  `industry_id` smallint unsigned NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`society_id`,`industry_id`),
  KEY `industry_id` (`industry_id`),
  CONSTRAINT `society_industry_ibfk_1` FOREIGN KEY (`society_id`) REFERENCES `society` (`society_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `society_industry_ibfk_2` FOREIGN KEY (`industry_id`) REFERENCES `industry` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `task`
--

DROP TABLE IF EXISTS `task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task` (
  `taskId` mediumint unsigned NOT NULL AUTO_INCREMENT,
  `priority` tinyint unsigned NOT NULL DEFAULT '0',
  `project` varchar(100) DEFAULT NULL,
  `context` varchar(20) NOT NULL DEFAULT 'A',
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `status` tinyint unsigned NOT NULL DEFAULT '0',
  `creationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastChangeDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deadlineDate` date DEFAULT NULL,
  PRIMARY KEY (`taskId`),
  KEY `project` (`project`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `user_id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL,
  `email` tinytext,
  `lastModification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-28 16:53:39
