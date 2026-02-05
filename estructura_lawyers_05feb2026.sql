-- MySQL dump 10.19  Distrib 10.3.39-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: lawyers_saas
-- ------------------------------------------------------
-- Server version	10.3.39-MariaDB-0ubuntu0.20.04.2

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
-- Table structure for table `historial_escritos`
--

DROP TABLE IF EXISTS `historial_escritos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historial_escritos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `interno_id` int(11) NOT NULL,
  `tipo_solicitud` varchar(255) DEFAULT NULL,
  `fecha_generacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `internos`
--

DROP TABLE IF EXISTS `internos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `rut` varchar(20) NOT NULL,
  `sexo` enum('masculino','femenino','otro') DEFAULT 'masculino',
  `nacionalidad` varchar(100) DEFAULT 'Chilena',
  `nacionalidad_otro` varchar(100) DEFAULT NULL,
  `gentilicio` varchar(100) DEFAULT NULL,
  `delito` varchar(255) DEFAULT NULL,
  `carcel` varchar(255) DEFAULT NULL,
  `solicitudes` text DEFAULT NULL,
  `abogado` varchar(255) DEFAULT NULL,
  `abogado_asignado` varchar(255) DEFAULT NULL,
  `rit` varchar(50) DEFAULT NULL,
  `ruc` varchar(50) DEFAULT NULL,
  `juzgado` varchar(100) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_termino` date DEFAULT NULL,
  `fecha_entrevista` date DEFAULT NULL,
  `fecha_cumplimiento_rebaja` varchar(100) DEFAULT NULL,
  `fecha_min_libertad` date DEFAULT NULL,
  `fecha_min_permiso` date DEFAULT NULL,
  `tiempo_condena` varchar(100) DEFAULT NULL,
  `estado_procesal` varchar(50) DEFAULT NULL,
  `beneficios` text DEFAULT NULL,
  `nivel_riesgo` enum('bajo','medio','alto') DEFAULT 'medio',
  `prioridad` enum('normal','alta') DEFAULT 'normal',
  `defensor_id` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `contacto1_nombre` varchar(255) DEFAULT NULL,
  `contacto1_parentesco` varchar(100) DEFAULT NULL,
  `contacto1_telefono` varchar(100) DEFAULT NULL,
  `contacto1_email` varchar(100) DEFAULT NULL,
  `contacto2_nombre` varchar(255) DEFAULT NULL,
  `contacto2_parentesco` varchar(100) DEFAULT NULL,
  `contacto2_telefono` varchar(100) DEFAULT NULL,
  `contacto2_email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_interno_usuario` (`usuario_id`),
  CONSTRAINT `fk_interno_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `internos_eliminados`
--

DROP TABLE IF EXISTS `internos_eliminados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internos_eliminados` (
  `id` int(11) NOT NULL,
  `nombres` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `rut` varchar(20) DEFAULT NULL,
  `sexo` varchar(20) DEFAULT NULL,
  `nacionalidad` varchar(50) DEFAULT NULL,
  `gentilicio` varchar(50) DEFAULT NULL,
  `delito` text DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_termino` date DEFAULT NULL,
  `fecha_registro` datetime DEFAULT NULL,
  `fecha_entrevista` date DEFAULT NULL,
  `juzgado` varchar(200) DEFAULT NULL,
  `rit` varchar(50) DEFAULT NULL,
  `ruc` varchar(50) DEFAULT NULL,
  `tiempo_condena` varchar(100) DEFAULT NULL,
  `carcel` varchar(200) DEFAULT NULL,
  `usuario` varchar(100) DEFAULT NULL,
  `solicitudes` text DEFAULT NULL,
  `abogado` varchar(100) DEFAULT NULL,
  `estado_procesal` varchar(50) DEFAULT NULL,
  `beneficios` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `nivel_riesgo` varchar(20) DEFAULT NULL,
  `prioridad` varchar(20) DEFAULT NULL,
  `fecha_actualizacion` datetime DEFAULT NULL,
  `defensor_id` int(11) DEFAULT NULL,
  `fecha_cumplimiento_rebaja` date DEFAULT NULL,
  `fecha_min_libertad` date DEFAULT NULL,
  `fecha_min_permiso` date DEFAULT NULL,
  `contacto1_nombre` varchar(100) DEFAULT NULL,
  `contacto1_parentesco` varchar(50) DEFAULT NULL,
  `contacto1_telefono` varchar(20) DEFAULT NULL,
  `contacto1_email` varchar(100) DEFAULT NULL,
  `contacto2_nombre` varchar(100) DEFAULT NULL,
  `contacto2_parentesco` varchar(50) DEFAULT NULL,
  `contacto2_telefono` varchar(20) DEFAULT NULL,
  `contacto2_email` varchar(100) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_archivado` datetime DEFAULT current_timestamp(),
  `archivado_por` int(11) DEFAULT NULL,
  `motivo_archivo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `solicitudes`
--

DROP TABLE IF EXISTS `solicitudes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `solicitudes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_solicitud` varchar(255) NOT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT 'General',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `google_id` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `rol` enum('admin','abogado') DEFAULT 'abogado',
  `tokens` int(11) DEFAULT 10,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `google_id` (`google_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-05 10:26:22
