-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 23-09-2024 a las 08:57:29
-- Versión del servidor: 8.3.0
-- Versión de PHP: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `caja_registradora`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

DROP TABLE IF EXISTS `productos`;
CREATE TABLE IF NOT EXISTS `productos` (
  `id_producto` int NOT NULL AUTO_INCREMENT,
  `codigo_producto` varchar(200) NOT NULL,
  `nombre_producto` varchar(200) NOT NULL,
  `precio` int NOT NULL,
  `cantidad_disponible` int NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` int NOT NULL,
  PRIMARY KEY (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `codigo_producto`, `nombre_producto`, `precio`, `cantidad_disponible`, `fecha_creacion`, `estado`) VALUES
(2, 'IEM-0002', 'Alambre de cobre\r\n', 14000, 96, '2024-09-21 21:35:46', 1),
(8, 'IEM-0003', 'Cable electrico', 2500, 98, '2024-09-22 17:31:21', 1),
(9, 'IEM-0004', 'Pilas', 5200, 94, '2024-09-22 17:31:21', 1),
(12, 'IEM-0010', 'Interruptores de luz', 15000, 100, '2024-09-23 02:21:07', 1),
(13, 'IEM-0011', 'Reflectores LED', 23000, 100, '2024-09-23 02:21:07', 1),
(14, 'IEM-0012', 'Cable coaxial', 15500, 100, '2024-09-23 02:21:07', 1),
(15, 'IEM-0013', 'Cajas de conexión', 125000, 0, '2024-09-23 02:21:07', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

DROP TABLE IF EXISTS `ventas`;
CREATE TABLE IF NOT EXISTS `ventas` (
  `id_venta` int NOT NULL AUTO_INCREMENT,
  `tipo_cliente` varchar(100) NOT NULL,
  `nombre_cliente` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `nombre_empresa` varchar(250) DEFAULT NULL,
  `razon_social` varchar(350) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `nit` int DEFAULT NULL,
  `fecha_venta` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_sin_iva` int NOT NULL,
  `total_iva` int NOT NULL,
  `estado` int NOT NULL,
  PRIMARY KEY (`id_venta`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `tipo_cliente`, `nombre_cliente`, `nombre_empresa`, `razon_social`, `nit`, `fecha_venta`, `total_sin_iva`, `total_iva`, `estado`) VALUES
(12, 'natural', 'Hernan Sanchez', NULL, NULL, NULL, '2024-09-23 02:15:24', 20600, 24514, 1),
(13, 'empresa', NULL, 'Coomeva', 'Se requiere producto debido a...', 1457841, '2024-09-23 02:17:12', 71600, 85204, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta_producto`
--

DROP TABLE IF EXISTS `venta_producto`;
CREATE TABLE IF NOT EXISTS `venta_producto` (
  `id_detalle` int NOT NULL AUTO_INCREMENT,
  `id_venta` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad_vendida` int NOT NULL,
  `subtotal` int NOT NULL,
  `total` int NOT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `id_venta` (`id_venta`),
  KEY `id_producto` (`id_producto`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `venta_producto`
--

INSERT INTO `venta_producto` (`id_detalle`, `id_venta`, `id_producto`, `cantidad_vendida`, `subtotal`, `total`) VALUES
(19, 12, 9, 3, 15600, 18564),
(20, 12, 8, 2, 5000, 5950),
(21, 13, 9, 3, 15600, 18564),
(22, 13, 2, 4, 56000, 66640);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `venta_producto`
--
ALTER TABLE `venta_producto`
  ADD CONSTRAINT `venta_producto_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`),
  ADD CONSTRAINT `venta_producto_ibfk_2` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
