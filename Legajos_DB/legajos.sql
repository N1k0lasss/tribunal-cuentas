/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: legajos
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-5ubuntu0.1 from Ubuntu

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `antecedente_laborals`
--

DROP TABLE IF EXISTS `antecedente_laborals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `antecedente_laborals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `persona_id` bigint(20) unsigned NOT NULL,
  `empleador` varchar(255) NOT NULL,
  `lugar_de_trabajo` varchar(255) NOT NULL,
  `cargo` varchar(255) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `motivo_egreso` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `antecedente_laborals`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `antecedente_laborals` WRITE;
/*!40000 ALTER TABLE `antecedente_laborals` DISABLE KEYS */;
INSERT INTO `antecedente_laborals` VALUES
(1,13,'Municipalidad de La Banda','La Banda, Santiago del Estero','Administrativo','2012-03-01','2018-02-28','Renuncia','2026-09-09 16:17:04','2026-09-09 16:17:04'),
(2,24,'Correo Argentino','Santiago del Estero (Capital)','Cadete','2010-01-15','2016-12-20','Fin de contrato','2026-09-09 16:17:06','2026-09-09 16:17:06');
/*!40000 ALTER TABLE `antecedente_laborals` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `areas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `areas_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `areas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `areas` WRITE;
/*!40000 ALTER TABLE `areas` DISABLE KEYS */;
INSERT INTO `areas` VALUES
(1,'Archivo','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(2,'Auditoria','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(3,'Comision de estudios 1','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(4,'Comision de estudios 2','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(5,'Computos','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(6,'Fiscal General 1','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(7,'Fiscal General 2','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(8,'Habilitación','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(9,'Mesa de entradas','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(10,'Municipios','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(11,'Personal','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(12,'Rendición de cuentas','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(13,'Secretaria General','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(14,'Servicios Generales','2026-09-09 16:17:00','2026-09-09 16:17:00');
/*!40000 ALTER TABLE `areas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cargos`
--

DROP TABLE IF EXISTS `cargos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cargos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cargos` WRITE;
/*!40000 ALTER TABLE `cargos` DISABLE KEYS */;
INSERT INTO `cargos` VALUES
(1,'Administrativo','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(2,'Auditoria','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(3,'Comisión de Gobierno','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(4,'Consejero/a de Cuentas','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(5,'Coordinador Ejecutivo','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(6,'Fiscal Jefe/a','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(7,'Gabinete Técnico','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(8,'Informático','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(9,'Interventor','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(10,'Oficial Mayor de Estudios','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(11,'Oficial Principal de Despacho','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(12,'Operador','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(13,'Ordenanza','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(14,'Presidente/a','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(15,'Relator Mayor Coordinador','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(16,'Sección de Fiscalización','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(17,'Secretaria General','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(18,'Servicio Jurídico del Estado','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(19,'Vocero','2026-09-09 16:17:00','2026-09-09 16:17:00');
/*!40000 ALTER TABLE `cargos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(10) DEFAULT NULL,
  `descripcion` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categorias_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES
(1,'CAT 1','Secretario General - Contador/Fiscal','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(2,'CAT 2','Jefe de Rendición','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(3,'CAT 3','Auditor Junior - Sin Denominación','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(4,'CAT 4','Auditor Semijunior - Sin Denominación (sin título)','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(5,'CAT 5','Jefe de Despacho - Auditor Junior','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(6,'CAT 6','Operador','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(7,'CAT 7','Jefe de Sección','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(8,'CAT 8','Auxiliar Administrativo A - Revisor A','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(9,'CAT 9','Auxiliar Administrativo B - Revisor B','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(10,'CAT 10','Auxiliar Administrativo C - Revisor C','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(11,'CAT 11','Mayordomo','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(12,'CAT 12','Ordenanza - Chofer','2026-09-09 16:17:00','2026-09-09 16:17:00');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cursos`
--

DROP TABLE IF EXISTS `cursos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cursos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `persona_id` bigint(20) unsigned NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `institucion` varchar(255) DEFAULT NULL,
  `duracion` varchar(255) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `tiene_certificado` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cursos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cursos` WRITE;
/*!40000 ALTER TABLE `cursos` DISABLE KEYS */;
INSERT INTO `cursos` VALUES
(1,15,'Carpinteria','Instituto Banda','1 año','2024-11-30',1,'2026-09-09 16:17:00','2026-09-09 16:17:00'),
(2,8,'Excel Avanzado para la Gestión Pública','Instituto de Capacitación Provincial','40 horas','2023-05-20',1,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(3,18,'Atención al Público y Mesa de Entradas','Instituto de Capacitación Provincial','20 horas','2022-08-14',1,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(4,29,'Gestión Digital de Legajos','Instituto de Capacitación Provincial','30 horas','2024-02-10',1,'2026-09-09 16:17:07','2026-09-09 16:17:07');
/*!40000 ALTER TABLE `cursos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `documentos`
--

DROP TABLE IF EXISTS `documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `documentos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ruta` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `tipodoc` int(11) DEFAULT NULL,
  `legajo_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documentos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `documentos` WRITE;
/*!40000 ALTER TABLE `documentos` DISABLE KEYS */;
INSERT INTO `documentos` VALUES
(1,NULL,'Licencia solicitada por Verónica Soledad Barrionuevo',3,9,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(2,NULL,'Licencia solicitada por Natalia Soledad Iturre',3,17,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(3,NULL,'Licencia solicitada por Estela Marina Ledesma',3,25,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(4,NULL,'Licencia solicitada por Nicolás Ezequiel Ávila',3,28,'2026-09-09 16:17:06','2026-09-09 16:17:06');
/*!40000 ALTER TABLE `documentos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `estudios`
--

DROP TABLE IF EXISTS `estudios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `estudios` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `institucion` varchar(100) DEFAULT NULL,
  `nivel_estudio` int(11) NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `persona_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estudios_persona_id_foreign` (`persona_id`),
  CONSTRAINT `estudios_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudios`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `estudios` WRITE;
/*!40000 ALTER TABLE `estudios` DISABLE KEYS */;
INSERT INTO `estudios` VALUES
(1,'Universidad Católica de Santiago del Estero',7,'2010-12-15',7,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(2,'Instituto Superior de Formación Docente N°1',6,'2008-11-20',16,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(3,'Universidad Nacional de Santiago del Estero',7,'2015-07-10',27,'2026-09-09 16:17:06','2026-09-09 16:17:06');
/*!40000 ALTER TABLE `estudios` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `exports`
--

DROP TABLE IF EXISTS `exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `exports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_disk` varchar(255) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `exporter` varchar(255) NOT NULL,
  `processed_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `total_rows` int(10) unsigned NOT NULL,
  `successful_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exports_user_id_foreign` (`user_id`),
  CONSTRAINT `exports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exports`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `exports` WRITE;
/*!40000 ALTER TABLE `exports` DISABLE KEYS */;
/*!40000 ALTER TABLE `exports` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `failed_import_rows`
--

DROP TABLE IF EXISTS `failed_import_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_import_rows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `import_id` bigint(20) unsigned NOT NULL,
  `validation_error` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `failed_import_rows_import_id_foreign` (`import_id`),
  CONSTRAINT `failed_import_rows_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_import_rows`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `failed_import_rows` WRITE;
/*!40000 ALTER TABLE `failed_import_rows` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_import_rows` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `familiars`
--

DROP TABLE IF EXISTS `familiars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `familiars` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `dni` int(11) DEFAULT NULL,
  `parentesco` int(11) NOT NULL,
  `fecha_de_nacimiento` date DEFAULT NULL,
  `vive` tinyint(1) NOT NULL,
  `persona_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `familiars_persona_id_foreign` (`persona_id`),
  CONSTRAINT `familiars_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `familiars`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `familiars` WRITE;
/*!40000 ALTER TABLE `familiars` DISABLE KEYS */;
INSERT INTO `familiars` VALUES
(1,'Marta','Ibáñez',33111222,0,'1980-04-10',1,6,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(2,'Claudia','Robledo',30222333,0,'1982-07-19',1,14,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(3,'Tomás','Farías',NULL,2,'2015-09-03',1,19,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(4,'Elsa','Toledo',15333444,1,'1950-02-14',1,22,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(5,'Analía','Moreno',27888999,0,'1984-05-22',1,26,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(6,'Bruno','Juárez',NULL,2,'2012-08-30',1,26,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(7,'Mía','Juárez',NULL,2,'2016-03-11',1,26,'2026-09-09 16:17:06','2026-09-09 16:17:06');
/*!40000 ALTER TABLE `familiars` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `historialbajas`
--

DROP TABLE IF EXISTS `historialbajas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `historialbajas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legajo_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `motivo` int(11) NOT NULL,
  `fecha_baja` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `historialbajas_legajo_id_foreign` (`legajo_id`),
  CONSTRAINT `historialbajas_legajo_id_foreign` FOREIGN KEY (`legajo_id`) REFERENCES `legajos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historialbajas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `historialbajas` WRITE;
/*!40000 ALTER TABLE `historialbajas` DISABLE KEYS */;
INSERT INTO `historialbajas` VALUES
(1,10,9,1,'2025-11-10 00:00:00','2026-09-09 16:17:03','2026-09-09 16:17:03'),
(2,12,9,2,'2025-09-30 00:00:00','2026-09-09 16:17:03','2026-09-09 16:17:03'),
(3,21,9,0,'2025-06-15 00:00:00','2026-09-09 16:17:05','2026-09-09 16:17:05'),
(4,23,9,6,'2025-03-01 00:00:00','2026-09-09 16:17:05','2026-09-09 16:17:05'),
(5,30,9,3,'2025-12-31 00:00:00','2026-09-09 16:17:07','2026-09-09 16:17:07');
/*!40000 ALTER TABLE `historialbajas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `idiomas`
--

DROP TABLE IF EXISTS `idiomas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `idiomas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `persona_id` bigint(20) unsigned NOT NULL,
  `idioma` varchar(100) NOT NULL,
  `nivel` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `idiomas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `idiomas` WRITE;
/*!40000 ALTER TABLE `idiomas` DISABLE KEYS */;
INSERT INTO `idiomas` VALUES
(1,11,'Inglés',1,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(2,20,'Inglés',0,'2026-09-09 16:17:05','2026-09-09 16:17:05');
/*!40000 ALTER TABLE `idiomas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `imports`
--

DROP TABLE IF EXISTS `imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `imports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `importer` varchar(255) NOT NULL,
  `processed_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `total_rows` int(10) unsigned NOT NULL,
  `successful_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imports_user_id_foreign` (`user_id`),
  CONSTRAINT `imports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imports`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `imports` WRITE;
/*!40000 ALTER TABLE `imports` DISABLE KEYS */;
/*!40000 ALTER TABLE `imports` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `legajos`
--

DROP TABLE IF EXISTS `legajos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `legajos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `num_legajo` int(11) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL,
  `fecha_de_ingreso` datetime NOT NULL,
  `tipo_contrato` int(11) DEFAULT NULL,
  `persona_id` bigint(20) unsigned NOT NULL,
  `categoria_id` bigint(20) unsigned NOT NULL,
  `cargo_id` bigint(20) unsigned NOT NULL,
  `area_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `legajos_num_legajo_unique` (`num_legajo`),
  KEY `legajos_persona_id_foreign` (`persona_id`),
  KEY `legajos_categoria_id_foreign` (`categoria_id`),
  KEY `legajos_cargo_id_foreign` (`cargo_id`),
  KEY `legajos_area_id_foreign` (`area_id`),
  CONSTRAINT `legajos_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `legajos_cargo_id_foreign` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `legajos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE,
  CONSTRAINT `legajos_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legajos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `legajos` WRITE;
/*!40000 ALTER TABLE `legajos` DISABLE KEYS */;
INSERT INTO `legajos` VALUES
(1,1000,1,'2015-03-01 00:00:00',0,1,1,14,13,'2026-09-09 16:17:01','2026-09-09 16:17:01'),
(2,1001,1,'2015-03-01 00:00:00',0,2,1,6,6,'2026-09-09 16:17:01','2026-09-09 16:17:01'),
(3,1002,1,'2015-03-01 00:00:00',2,3,5,11,11,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(4,1003,1,'2015-03-01 00:00:00',2,4,6,1,11,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(5,1004,1,'2015-03-01 00:00:00',2,5,4,5,11,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(6,1005,1,'2010-03-01 00:00:00',2,6,6,1,1,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(7,1006,1,'2012-07-15 00:00:00',2,7,7,12,2,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(8,1007,1,'2015-02-10 00:00:00',2,8,8,13,3,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(9,1008,1,'2017-11-20 00:00:00',2,9,9,8,4,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(10,1009,0,'2019-04-05 00:00:00',2,10,10,16,5,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(11,1010,1,'2020-08-12 00:00:00',2,11,11,2,6,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(12,1011,0,'2021-01-25 00:00:00',1,12,12,1,7,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(13,1012,1,'2022-06-18 00:00:00',2,13,5,12,8,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(14,1013,1,'2023-03-09 00:00:00',2,14,6,13,9,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(15,1014,1,'2024-09-01 00:00:00',2,15,7,8,10,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(16,1015,1,'2010-03-01 00:00:00',2,16,8,16,12,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(17,1016,1,'2012-07-15 00:00:00',2,17,9,2,13,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(18,1017,1,'2015-02-10 00:00:00',1,18,10,1,14,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(19,1018,1,'2017-11-20 00:00:00',2,19,11,12,1,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(20,1019,1,'2019-04-05 00:00:00',1,20,12,13,2,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(21,1020,0,'2020-08-12 00:00:00',2,21,5,8,3,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(22,1021,1,'2021-01-25 00:00:00',2,22,6,16,4,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(23,1022,0,'2022-06-18 00:00:00',2,23,7,2,5,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(24,1023,1,'2023-03-09 00:00:00',2,24,8,1,6,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(25,1024,1,'2024-09-01 00:00:00',2,25,9,12,7,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(26,1025,1,'2010-03-01 00:00:00',2,26,10,13,8,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(27,1026,1,'2012-07-15 00:00:00',2,27,11,8,9,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(28,1027,1,'2015-02-10 00:00:00',2,28,12,16,10,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(29,1028,1,'2017-11-20 00:00:00',2,29,5,2,12,'2026-09-09 16:17:07','2026-09-09 16:17:07'),
(30,1029,0,'2019-04-05 00:00:00',2,30,6,1,13,'2026-09-09 16:17:07','2026-09-09 16:17:07');
/*!40000 ALTER TABLE `legajos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_05_21_234340_create_personas_table',1),
(5,'2026_05_21_234341_create_areas_table',1),
(6,'2026_05_21_234342_create_cargos_table',1),
(7,'2026_05_21_234344_create_categorias_table',1),
(8,'2026_05_21_234344_create_legajos_table',1),
(9,'2026_05_21_234345_create_historialbajas_table',1),
(10,'2026_06_04_084751_create_documentos_table',1),
(11,'2026_06_05_095017_create_estudios_table',1),
(12,'2026_06_05_095018_create_titulos_table',1),
(13,'2026_06_05_095914_create_oficinas_table',1),
(14,'2026_06_27_140036_create_familiars_table',1),
(15,'2026_06_29_155950_create_cursos_table',1),
(16,'2026_06_29_160124_create_idiomas_table',1),
(17,'2026_06_29_162049_create_antecedente_laborals_table',1),
(18,'2026_07_01_211140_create_notifications_table',1),
(19,'2026_07_01_211144_create_imports_table',1),
(20,'2026_07_01_211145_create_exports_table',1),
(21,'2026_07_01_211146_create_failed_import_rows_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` uuid NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `oficinas`
--

DROP TABLE IF EXISTS `oficinas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oficinas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `descripcion` text NOT NULL,
  `area_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `oficinas_nombre_unique` (`nombre`),
  KEY `oficinas_area_id_foreign` (`area_id`),
  CONSTRAINT `oficinas_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oficinas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `oficinas` WRITE;
/*!40000 ALTER TABLE `oficinas` DISABLE KEYS */;
/*!40000 ALTER TABLE `oficinas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `personas`
--

DROP TABLE IF EXISTS `personas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` int(11) NOT NULL,
  `cuil` bigint(20) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `genero` int(11) DEFAULT NULL,
  `estado_civil` int(11) DEFAULT NULL,
  `fecha_de_nacimiento` date NOT NULL,
  `domicilio` varchar(250) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `telefono_emergencia` varchar(20) DEFAULT NULL,
  `borrar_logico` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personas_dni_unique` (`dni`),
  UNIQUE KEY `personas_cuil_unique` (`cuil`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personas`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `personas` WRITE;
/*!40000 ALTER TABLE `personas` DISABLE KEYS */;
INSERT INTO `personas` VALUES
(1,'María marta Eugenia','Ledesma',22456700,27224567818,'mariamarta.ledesma@tribunalsgo.gob.ar',0,1,'1975-03-14','S/N, Santiago del Estero (Capital)','3854456700','3855456700',NULL,'2026-09-09 16:17:01','2026-09-09 16:17:01'),
(2,'Roberto Carlos','Juárez',20987654,20209876540,'roberto.juarez@tribunalsgo.gob.ar',1,1,'1972-11-02','S/N, La Banda','3854987654','3855987654',NULL,'2026-09-09 16:17:01','2026-09-09 16:17:01'),
(3,'Silvia Beatriz','Paz',27654321,27276543216,'silvia.paz@tribunalsgo.gob.ar',0,2,'1980-06-23','S/N, Santiago del Estero (Capital)','3854654321','3855654321',NULL,'2026-09-09 16:17:01','2026-09-09 16:17:01'),
(4,'Marcelo Fabián','Ávila',25123456,20251234567,'marcelo.avila@tribunalsgo.gob.ar',1,1,'1983-01-09','S/N, La Banda','3854123456','3855123456',NULL,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(5,'Gabriela Noelia','Cardozo',30111222,27301112225,'gabriela.cardozo@tribunalsgo.gob.ar',0,0,'1990-09-30','S/N, Termas de Río Hondo','3854111222','3855111222',NULL,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(6,'Diego Alejandro','Herrera',28765432,20287654325,'diego.herrera@tribunalsgo.gob.ar',1,1,'1979-02-18','S/N, Santiago del Estero (Capital)','3854765432','3855765432',NULL,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(7,'Claudia Fernanda','Gómez',29456129,27294561299,'claudia.gomez@tribunalsgo.gob.ar',0,1,'1981-07-05','S/N, La Banda','3854456129','3855456129',NULL,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(8,'Héctor Ramón','Ibáñez',24678901,20246789011,'hector.ibañez@tribunalsgo.gob.ar',1,1,'1976-12-25','S/N, Frías','3854678901','3855678901',NULL,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(9,'Verónica Soledad','Barrionuevo',31234567,27312345671,'veronica.barrionuevo@tribunalsgo.gob.ar',0,0,'1993-04-11','S/N, Añatuya','3854234567','3855234567',NULL,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(10,'Jorge Luis','Coronel',26345678,20263456786,'jorge.coronel@tribunalsgo.gob.ar',1,2,'1978-10-08','S/N, Santiago del Estero (Capital)','3854345678','3855345678',NULL,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(11,'Patricia Alejandra','Suárez',27890123,27278901233,'patricia.suarez@tribunalsgo.gob.ar',0,1,'1982-05-27','S/N, Fernández','3854890123','3855890123',NULL,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(12,'Ricardo Daniel','Acuña',23456789,20234567897,'ricardo.acuña@tribunalsgo.gob.ar',1,3,'1974-08-19','S/N, Quimilí','3854456789','3855456789',NULL,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(13,'Mónica Beatriz','Díaz',32109876,27321098768,'monica.diaz@tribunalsgo.gob.ar',0,0,'1995-01-30','S/N, La Banda','3854109876','3855109876',NULL,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(14,'Sergio Damián','Nazur',29876543,20298765439,'sergio.nazur@tribunalsgo.gob.ar',1,1,'1980-03-22','S/N, Santiago del Estero (Capital)','3854876543','3855876543',NULL,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(15,'Lorena Vanesa','Salomón',33456789,27334567899,'lorena.salomon@tribunalsgo.gob.ar',0,0,'1996-11-15','S/N, Monte Quemado','3854456789','3855456789',NULL,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(16,'Andrés Fabián','Taboada',25987654,20259876541,'andres.taboada@tribunalsgo.gob.ar',1,1,'1979-09-03','S/N, Santiago del Estero (Capital)','3854987654','3855987654',NULL,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(17,'Natalia Soledad','Iturre',30654321,27306543216,'natalia.iturre@tribunalsgo.gob.ar',0,1,'1988-06-17','S/N, Suncho Corral','3854654321','3855654321',NULL,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(18,'Pablo Ezequiel','Gerez',34567890,20345678906,'pablo.gerez@tribunalsgo.gob.ar',1,0,'1997-02-28','S/N, La Banda','3854567890','3855567890',NULL,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(19,'Carla Estefanía','Farías',31987654,27319876540,'carla.farias@tribunalsgo.gob.ar',0,0,'1994-12-09','S/N, Loreto','3854987654','3855987654',NULL,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(20,'Emanuel Nicolás','Corbalán',35678901,20356789017,'emanuel.corbalan@tribunalsgo.gob.ar',1,0,'1998-07-21','S/N, Santiago del Estero (Capital)','3854678901','3855678901',NULL,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(21,'Yolanda del Valle','Romano',22987321,27229873216,'yolanda.romano@tribunalsgo.gob.ar',0,3,'1973-04-06','S/N, Clodomira','3854987321','3855987321',NULL,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(22,'Walter Ariel','Frías',27456912,20274569124,'walter.frias@tribunalsgo.gob.ar',1,1,'1981-10-14','S/N, Beltrán','3854456912','3855456912',NULL,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(23,'Rosa Mabel','Ibarra',24123890,27241238909,'rosa.ibarra@tribunalsgo.gob.ar',0,2,'1977-05-02','S/N, Santiago del Estero (Capital)','3854123890','3855123890',NULL,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(24,'Julio César','Sayago',28234561,20282345618,'julio.sayago@tribunalsgo.gob.ar',1,1,'1979-08-28','S/N, La Banda','3854234561','3855234561',NULL,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(25,'Estela Marina','Ledesma',26789012,27267890124,'estela.ledesma@tribunalsgo.gob.ar',0,1,'1978-03-19','S/N, Forres','3854789012','3855789012',NULL,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(26,'Fabián Osvaldo','Juárez',29123045,20291230459,'fabian.juarez@tribunalsgo.gob.ar',1,1,'1982-01-11','S/N, Termas de Río Hondo','3854123045','3855123045',NULL,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(27,'Marisa Alejandra','Paz',30987012,27309870129,'marisa.paz@tribunalsgo.gob.ar',0,0,'1990-09-25','S/N, Santiago del Estero (Capital)','3854987012','3855987012',NULL,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(28,'Nicolás Ezequiel','Ávila',36123456,20361234562,'nicolas.avila@tribunalsgo.gob.ar',1,0,'1999-06-30','S/N, Colonia Dora','3854123456','3855123456',NULL,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(29,'Cynthia Paola','Cardozo',32456098,27324560985,'cynthia.cardozo@tribunalsgo.gob.ar',0,1,'1993-02-14','S/N, La Banda','3854456098','3855456098',NULL,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(30,'Ramón Alberto','Herrera',21345678,20213456785,'ramon.herrera@tribunalsgo.gob.ar',1,1,'1969-12-01','S/N, Santiago del Estero (Capital)','3854345678','3855345678',NULL,'2026-09-09 16:17:07','2026-09-09 16:17:07');
/*!40000 ALTER TABLE `personas` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `titulos`
--

DROP TABLE IF EXISTS `titulos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `titulos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `estudio_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `titulos_estudio_id_foreign` (`estudio_id`),
  CONSTRAINT `titulos_estudio_id_foreign` FOREIGN KEY (`estudio_id`) REFERENCES `estudios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `titulos`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `titulos` WRITE;
/*!40000 ALTER TABLE `titulos` DISABLE KEYS */;
INSERT INTO `titulos` VALUES
(1,'Contador/a Público/a',1,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(2,'Técnico/a Superior en Administración Pública',2,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(3,'Abogado/a',3,'2026-09-09 16:17:06','2026-09-09 16:17:06');
/*!40000 ALTER TABLE `titulos` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(255) NOT NULL DEFAULT 'empleado',
  `persona_id` bigint(20) unsigned DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Pedro','pedrogarcia@gmail.com','2026-09-09 16:17:00','$2y$12$bf2xEp0HDPAc/BLiaFrs5OWAkc.Dde8l8cL5HKcxejHarDe2jaRWe','empleado',1,'ejnT4sCmPv','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(2,'Empleado','empleado@gmail.com','2026-09-09 16:17:00','$2y$12$.SUkXZkLpUZY3IhoioHAtuYMxl.PIN6nW1OlK39CO19l2oUR6z3ny','empleado',NULL,'BnUs8wTJII','2026-09-09 16:17:00','2026-09-09 16:17:00'),
(3,'Funcionario','funcionario@gmail.com','2026-09-09 16:17:01','$2y$12$4OzbtN5dik7OyBDAFfU.duYhatT0xHo.7CLI1pN.o/ErZOCqL3852','funcionario',NULL,'MLtzUaEch2','2026-09-09 16:17:01','2026-09-09 16:17:01'),
(4,'RRHH','rrhh@gmail.com','2026-09-09 16:17:01','$2y$12$mrNHfU1B.BYOkKyimCH9eOZk4PfQnIowx7/sokNOfZ1cmDUluGFoC','rrhh',NULL,'aRqD3m9Cth','2026-09-09 16:17:01','2026-09-09 16:17:01'),
(5,'Administador','correofalso123@gmail.com','2026-09-09 16:17:01','$2y$12$YTA.SS8rE7eMHcM1B2VIv.mrlJICQ2TwcQSzONjttjcwtjqAYFahe','administrador',NULL,'eejAJ68ToX','2026-09-09 16:17:01','2026-09-09 16:17:01'),
(6,'Mario','mariosantos1@gmail.com','2026-09-09 16:17:01','$2y$12$Le8lSEnv6tdIQE8bk1goT.DjcUXL8oB0CuDj7fuDnTKXS6P6XYzge','empleado',15,'gY48ijSvxE','2026-09-09 16:17:01','2026-09-09 16:17:01'),
(7,'María marta Eugenia Ledesma','mariamarta.ledesma@tribunalsgo.gob.ar','2026-09-09 16:17:01','$2y$12$sEhNgXLSktnEPQJnJoOLcOn/kgHeQrC07IifN0VkfX7jmJhhD8D0C','funcionario',1,NULL,'2026-09-09 16:17:01','2026-09-09 16:17:01'),
(8,'Roberto Carlos Juárez','roberto.juarez@tribunalsgo.gob.ar','2026-09-09 16:17:01','$2y$12$1nGiQGDCIjHjUG124yng7.lkG7GSiQDDh/BeI1M.Rn852x8o6OVBO','funcionario',2,NULL,'2026-09-09 16:17:01','2026-09-09 16:17:01'),
(9,'Silvia Beatriz Paz','silvia.paz@tribunalsgo.gob.ar','2026-09-09 16:17:02','$2y$12$hs1QZvCuv2e8xJV96KZVEu4AoRDNST4sQllzCHek27lrc91qDQ/Tu','rrhh',3,NULL,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(10,'Marcelo Fabián Ávila','marcelo.avila@tribunalsgo.gob.ar','2026-09-09 16:17:02','$2y$12$OHX/nXOZEh.1dfzl.UojMe.R/zjqu5EyOC8zDBwdHHZqJYwpdvIIC','rrhh',4,NULL,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(11,'Gabriela Noelia Cardozo','gabriela.cardozo@tribunalsgo.gob.ar','2026-09-09 16:17:02','$2y$12$hVUtKXIkBQkPcyoCUSYgNuNoA4OMvXHumPFlutwwBqAZGCN.mjLya','rrhh',5,NULL,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(12,'Diego Alejandro Herrera','diego.herrera@tribunalsgo.gob.ar','2026-09-09 16:17:02','$2y$12$QKo/kPBDRC4DJHU62WqCLuPy37q/v7A6nINYJudcCndonsL7mfnYm','empleado',6,NULL,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(13,'Claudia Fernanda Gómez','claudia.gomez@tribunalsgo.gob.ar','2026-09-09 16:17:02','$2y$12$W3niiPBGkKWWvZZuQrNo/.JYWUOlXp.HTiCWxU6Q3jo3Dk5fljQlm','empleado',7,NULL,'2026-09-09 16:17:02','2026-09-09 16:17:02'),
(14,'Héctor Ramón Ibáñez','hector.ibañez@tribunalsgo.gob.ar','2026-09-09 16:17:03','$2y$12$aIxQEQTfLZvp7AMC89Hl1ugBvCuXqLdRxmjj.kR7WmrAWqFOQr276','empleado',8,NULL,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(15,'Verónica Soledad Barrionuevo','veronica.barrionuevo@tribunalsgo.gob.ar','2026-09-09 16:17:03','$2y$12$3MyeKXqsiU25cO/LJ2NNIeAl/6NN50JX/3Ptn5mf4MW/Qw4Vv8A2y','empleado',9,NULL,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(16,'Jorge Luis Coronel','jorge.coronel@tribunalsgo.gob.ar','2026-09-09 16:17:03','$2y$12$BTgn0b4pi2VhqMC.xUnQPO0ZFqDucIQUO/l0DJDhqeUWshZjrc65a','empleado',10,NULL,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(17,'Patricia Alejandra Suárez','patricia.suarez@tribunalsgo.gob.ar','2026-09-09 16:17:03','$2y$12$y/TuIlUTo7O4FxKaWfk6p.cLexuc8pJNDkvb1fyWR8qC7AaacTBgG','empleado',11,NULL,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(18,'Ricardo Daniel Acuña','ricardo.acuña@tribunalsgo.gob.ar','2026-09-09 16:17:03','$2y$12$DOa.xw6tfYcOlyrRsD99xu3JpzeB1NYm5Sl0ml/vUFe/8pAB3PvBi','empleado',12,NULL,'2026-09-09 16:17:03','2026-09-09 16:17:03'),
(19,'Mónica Beatriz Díaz','monica.diaz@tribunalsgo.gob.ar','2026-09-09 16:17:04','$2y$12$lvk01p3KCsEJhORDd4tCYet6Te99HcvGfLtltb0Bga2I12xQrN8/2','empleado',13,NULL,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(20,'Sergio Damián Nazur','sergio.nazur@tribunalsgo.gob.ar','2026-09-09 16:17:04','$2y$12$hE2S8l39sQs4bVRoPhIyBOkE0aN1giZZQ8Q8hBIoKjyh6r3U7aRlC','empleado',14,NULL,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(21,'Lorena Vanesa Salomón','lorena.salomon@tribunalsgo.gob.ar','2026-09-09 16:17:04','$2y$12$s5Ht8OQ9fFK4nnHM8tSl6OGJS2cTl8KaJkIAJcORi..JHICkXFStO','empleado',15,NULL,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(22,'Andrés Fabián Taboada','andres.taboada@tribunalsgo.gob.ar','2026-09-09 16:17:04','$2y$12$IIvC2hVfR3eB0t3VDcJoYu6jA0hW/7JcYFx5QYB9UeKdz504y3LZG','empleado',16,NULL,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(23,'Natalia Soledad Iturre','natalia.iturre@tribunalsgo.gob.ar','2026-09-09 16:17:04','$2y$12$1UKvCng8DGiiZ2lxmFptx.sGPDm4GQjtDkBj7qRGfth4KJtpv.Wga','empleado',17,NULL,'2026-09-09 16:17:04','2026-09-09 16:17:04'),
(24,'Pablo Ezequiel Gerez','pablo.gerez@tribunalsgo.gob.ar','2026-09-09 16:17:05','$2y$12$hyQi4SM2xf04GFIHHhMYh.iBvQS0YFUKKjLIroGJggP9uwnypnVsu','empleado',18,NULL,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(25,'Carla Estefanía Farías','carla.farias@tribunalsgo.gob.ar','2026-09-09 16:17:05','$2y$12$kZFNGvkCWDlLIUl5VvUOzuFQYBD2cjXn62wJvZijRFzm2AONSBuUa','empleado',19,NULL,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(26,'Emanuel Nicolás Corbalán','emanuel.corbalan@tribunalsgo.gob.ar','2026-09-09 16:17:05','$2y$12$ZdAT/cpN6ivUTuGkUPMfwuMTZ/0TqVcgxkF7jBxKWjlNq2oHi/f06','empleado',20,NULL,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(27,'Yolanda del Valle Romano','yolanda.romano@tribunalsgo.gob.ar','2026-09-09 16:17:05','$2y$12$pt7nzPjyAS.P1vT5Dvzo3e7sdRe7rCS8clIDA1jxpqasFoZ9Hm.F2','empleado',21,NULL,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(28,'Walter Ariel Frías','walter.frias@tribunalsgo.gob.ar','2026-09-09 16:17:05','$2y$12$sPQh6wSHRdipGHNl4ulgWOKKw8/u2DxA21o/5QL4u1Bj2mJLpb6a6','empleado',22,NULL,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(29,'Rosa Mabel Ibarra','rosa.ibarra@tribunalsgo.gob.ar','2026-09-09 16:17:05','$2y$12$aonVYbB/kuVzmkOuKFOlW.sE4KOXsRnmXBuHRRD.YIdGQFds/gM6K','empleado',23,NULL,'2026-09-09 16:17:05','2026-09-09 16:17:05'),
(30,'Julio César Sayago','julio.sayago@tribunalsgo.gob.ar','2026-09-09 16:17:06','$2y$12$gsLyrHVMyEp2AtdL4lT.iuWQtoBhI4PRPcluEB1UjPUobR3mDx1zu','empleado',24,NULL,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(31,'Estela Marina Ledesma','estela.ledesma@tribunalsgo.gob.ar','2026-09-09 16:17:06','$2y$12$m1BgmxcHh00Zxodx7dvTYeL4XAdMqF560X7B0J10e/jByk7eZVPq6','empleado',25,NULL,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(32,'Fabián Osvaldo Juárez','fabian.juarez@tribunalsgo.gob.ar','2026-09-09 16:17:06','$2y$12$FHiiaK09/5CJ7aujqgCR2eTeUsBUihk1u7D/5BTLP4sQp1Axqj//.','empleado',26,NULL,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(33,'Marisa Alejandra Paz','marisa.paz@tribunalsgo.gob.ar','2026-09-09 16:17:06','$2y$12$0PQRwVZSQNVvyc3vhFic6ugXH2r1ehtRRPrU5reqhuNJ.XwZ50qvC','empleado',27,NULL,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(34,'Nicolás Ezequiel Ávila','nicolas.avila@tribunalsgo.gob.ar','2026-09-09 16:17:06','$2y$12$fWP/HJfMv.JOEde69rf25ebM.tNyrt6PX3T0j2p9FzoZ/VEJsjP6y','empleado',28,NULL,'2026-09-09 16:17:06','2026-09-09 16:17:06'),
(35,'Cynthia Paola Cardozo','cynthia.cardozo@tribunalsgo.gob.ar','2026-09-09 16:17:07','$2y$12$WH11jUFXE4yjBVuXoRqsS.NOHqUNC7oQWBOGih5G1Tc/7l1LmKBoq','empleado',29,NULL,'2026-09-09 16:17:07','2026-09-09 16:17:07'),
(36,'Ramón Alberto Herrera','ramon.herrera@tribunalsgo.gob.ar','2026-09-09 16:17:07','$2y$12$iCqu/g7MIPOlBWcXz8VyN.T9o/2lVGCSrROqrDqPvS5VxXyeQr2ti','empleado',30,NULL,'2026-09-09 16:17:07','2026-09-09 16:17:07');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-09-09 13:17:37
