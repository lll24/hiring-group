-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-11-2025 a las 22:16:09
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hiring_group`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areaconocimiento`
--

CREATE TABLE `areaconocimiento` (
  `id_area` bigint(20) NOT NULL,
  `nombre_area` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `areaconocimiento`
--

INSERT INTO `areaconocimiento` (`id_area`, `nombre_area`, `descripcion`) VALUES
(1, 'Desarrollo Web', 'Creación y mantenimiento de sitios y aplicaciones web'),
(2, 'Desarrollo Móvil', 'Desarrollo de aplicaciones para dispositivos móviles'),
(3, 'Inteligencia Artificial', 'Sistemas que simulan inteligencia humana'),
(4, 'Machine Learning', 'Algoritmos que aprenden de datos'),
(5, 'Ciencia de Datos', 'Análisis e interpretación de datos complejos'),
(6, 'Seguridad Informática', 'Protección de sistemas contra accesos no autorizados'),
(7, 'Redes y Telecomunicaciones', 'Diseño y gestión de redes de computadoras'),
(8, 'Desarrollo de Videojuegos', 'Creación de juegos para diversas plataformas'),
(9, 'Realidad Virtual y Aumentada', 'Tecnologías de realidad extendida'),
(10, 'Blockchain', 'Tecnología de registro distribuido'),
(11, 'Cloud Computing', 'Servicios en la nube y computación distribuida'),
(12, 'Internet de las Cosas (IoT)', 'Dispositivos conectados e inteligentes'),
(13, 'DevOps', 'Integración entre desarrollo y operaciones IT'),
(14, 'Diseño UX/UI', 'Experiencia e interfaz de usuario'),
(15, 'Testing QA', 'Aseguramiento de calidad de software'),
(16, 'Administración de Bases de Datos', 'Gestión y optimización de bases de datos'),
(17, 'Big Data', 'Procesamiento de grandes volúmenes de datos'),
(18, 'Arquitectura de Software', 'Diseño de estructuras de sistemas complejos'),
(19, 'Programación Embebida', 'Desarrollo para sistemas embebidos'),
(20, 'Bioinformática', 'Aplicación de TI en ciencias biológicas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `banco`
--

CREATE TABLE `banco` (
  `id_banco` bigint(20) NOT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `codigo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `banco`
--

INSERT INTO `banco` (`id_banco`, `nombre`, `codigo`) VALUES
(1, 'Banco de Venezuela', '0102'),
(2, 'Banco Venezolano de Crédito', '0104'),
(3, 'Banco Mercantil', '0105'),
(4, 'Banco Provincial', '0108'),
(5, 'Banesco Banco Universal', '0114'),
(6, 'Banco Exterior', '0115'),
(7, 'Banco Occidental de Descuento (BOD)', '0116'),
(8, 'Banco Caroní', '0128'),
(9, 'Bancaribe', '0134'),
(10, 'Banco Sofitasa', '0137'),
(11, 'Banco Plaza', '0138'),
(12, 'Bangente', '0146'),
(13, 'Banco del Tesoro', '0151'),
(14, '100% Banco', '0156'),
(15, 'DelSur Banco Universal', '0157'),
(16, 'Banco del Pueblo Soberano', '0163'),
(17, 'Banco Agrícola de Venezuela', '0166'),
(18, 'Bancrecer', '0168'),
(19, 'Mi Banco', '0169'),
(20, 'Banco Activo', '0171'),
(21, 'Bancamiga', '0172'),
(22, 'Banco Internacional de Desarrollo (BID)', '0173'),
(23, 'Banplus', '0174'),
(24, 'Banco Bicentenario del Pueblo', '0175'),
(25, 'BANFANB', '0176'),
(26, 'Banco Nacional de Crédito (BNC)', '0177'),
(27, 'Banco Industrial de Venezuela', '0191');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratado`
--

CREATE TABLE `contratado` (
  `id_contratacion` bigint(20) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `tipo_contrato` varchar(20) DEFAULT NULL,
  `salario_mensual` decimal(12,2) DEFAULT NULL,
  `tipo_sangre` varchar(10) DEFAULT NULL,
  `contacto_emergencia` varchar(100) DEFAULT NULL,
  `telefono_emergencia` varchar(20) DEFAULT NULL,
  `nro_cuenta` varchar(50) DEFAULT NULL,
  `fk_banco` bigint(20) DEFAULT NULL,
  `fk_postulante` bigint(20) DEFAULT NULL,
  `fk_oferta` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contratado`
--

INSERT INTO `contratado` (`id_contratacion`, `fecha_inicio`, `fecha_fin`, `tipo_contrato`, `salario_mensual`, `tipo_sangre`, `contacto_emergencia`, `telefono_emergencia`, `nro_cuenta`, `fk_banco`, `fk_postulante`, `fk_oferta`) VALUES
(1, '2025-07-18', '2030-12-01', 'Tiempo determinado', 15.00, 'O+', 'Nidia Longart', '04249151636', '01021847192847', 1, 1, 4),
(2, '2025-07-20', '2025-12-31', 'Tiempo determinado', 23.00, 'O+', 'simon bolivar', '02869733456', '2183987129847', 19, 2, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` bigint(20) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `RIF` varchar(50) DEFAULT NULL,
  `sector` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `persona_contacto` varchar(100) DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `fk_usuario` bigint(20) DEFAULT NULL,
  `creado_por` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nombre`, `RIF`, `sector`, `direccion`, `persona_contacto`, `telefono_contacto`, `fk_usuario`, `creado_por`) VALUES
(2, 'Fernandowed', 'j23134', 'ni idea', 'san felix centro', 'Fernando Centeno', '04122132957', 3, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `id_estado` bigint(20) NOT NULL,
  `nombre_estado` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`id_estado`, `nombre_estado`) VALUES
(1, 'Amazonas'),
(2, 'Anzoátegui'),
(3, 'Apure'),
(4, 'Aragua'),
(5, 'Barinas'),
(6, 'Bolívar'),
(7, 'Carabobo'),
(8, 'Cojedes'),
(9, 'Delta Amacuro'),
(10, 'Distrito Capital'),
(11, 'Falcón'),
(12, 'Guárico'),
(13, 'Lara'),
(14, 'Mérida'),
(15, 'Miranda'),
(16, 'Monagas'),
(17, 'Nueva Esparta'),
(18, 'Portuguesa'),
(19, 'Sucre'),
(20, 'Táchira'),
(21, 'Trujillo'),
(22, 'La Guaira'),
(23, 'Yaracuy'),
(24, 'Zulia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `experiencialaboral`
--

CREATE TABLE `experiencialaboral` (
  `id_experiencia` bigint(20) NOT NULL,
  `empresa` varchar(255) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fk_postulante` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `experiencialaboral`
--

INSERT INTO `experiencialaboral` (`id_experiencia`, `empresa`, `cargo`, `fecha_inicio`, `fecha_fin`, `descripcion`, `fk_postulante`) VALUES
(1, 'Los chinitos', 'el que barria', '2006-05-24', '2025-07-18', 'aja', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nominamensual`
--

CREATE TABLE `nominamensual` (
  `id_nomina` bigint(20) NOT NULL,
  `mes` varchar(20) DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  `fk_empresa` bigint(20) DEFAULT NULL,
  `total_salario_base` decimal(10,2) DEFAULT NULL,
  `total_inces` decimal(10,2) DEFAULT NULL,
  `total_ivss` decimal(10,2) DEFAULT NULL,
  `total_hiring_group` decimal(10,2) DEFAULT NULL,
  `fecha_generacion` date DEFAULT NULL,
  `total_neto` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nominamensual`
--

INSERT INTO `nominamensual` (`id_nomina`, `mes`, `anio`, `fk_empresa`, `total_salario_base`, `total_inces`, `total_ivss`, `total_hiring_group`, `fecha_generacion`, `total_neto`) VALUES
(1, '8', 2025, 2, 15.00, 0.08, 0.15, 0.30, '2025-07-19', 14.48);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nomina_detalle`
--

CREATE TABLE `nomina_detalle` (
  `id_detalle` bigint(20) NOT NULL,
  `fk_nomina` bigint(20) DEFAULT NULL,
  `fk_contratado` bigint(20) DEFAULT NULL,
  `salario_base` decimal(10,2) DEFAULT NULL,
  `inces` decimal(10,2) DEFAULT NULL,
  `ivss` decimal(10,2) DEFAULT NULL,
  `hiring_group` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nomina_detalle`
--

INSERT INTO `nomina_detalle` (`id_detalle`, `fk_nomina`, `fk_contratado`, `salario_base`, `inces`, `ivss`, `hiring_group`) VALUES
(1, 1, 1, 15.00, 0.08, 0.15, 0.30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion`
--

CREATE TABLE `notificacion` (
  `id_notificacion` int(11) NOT NULL,
  `fk_usuario` bigint(20) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificacion`
--

INSERT INTO `notificacion` (`id_notificacion`, `fk_usuario`, `mensaje`, `leido`, `fecha`) VALUES
(1, 4, 'Tu postulación a la oferta \'junior\' ha sido ACEPTADA.', 1, '2025-07-19 05:58:52'),
(2, 4, 'Tu postulación a la oferta \'junior\' ha sido ACEPTADA.', 1, '2025-07-19 05:58:52'),
(3, 5, 'Tu postulación a la oferta \'tequeñero\' ha sido RECHAZADA.', 1, '2025-07-19 13:52:36'),
(4, 5, 'Tu postulación a la oferta \'el que barria\' ha sido RECHAZADA.', 1, '2025-07-20 01:43:00'),
(5, 5, 'Tu postulación a la oferta \'manueeeelllll\' ha sido ACEPTADA.', 1, '2025-07-20 01:55:29'),
(6, 5, 'Tu postulación a la oferta \'manueeeelllll\' ha sido ACEPTADA.', 1, '2025-07-20 01:55:29'),
(7, 6, 'Tu postulación a la oferta \'mango\' ha sido RECHAZADA.', 0, '2025-07-20 02:03:46'),
(8, 4, '💰 Se ha registrado tu pago del mes. Revisa tu nómina.', 1, '2025-07-22 04:11:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertalaboral`
--

CREATE TABLE `ofertalaboral` (
  `id_oferta` bigint(20) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `descripcion_perfil` text DEFAULT NULL,
  `modalidad` varchar(20) DEFAULT NULL,
  `salario` decimal(12,2) DEFAULT NULL,
  `estado_oferta` varchar(20) DEFAULT NULL,
  `fecha_creacion` date DEFAULT NULL,
  `fk_empresa` bigint(20) DEFAULT NULL,
  `fk_area` bigint(20) DEFAULT NULL,
  `fk_estado` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ofertalaboral`
--

INSERT INTO `ofertalaboral` (`id_oferta`, `cargo`, `descripcion_perfil`, `modalidad`, `salario`, `estado_oferta`, `fecha_creacion`, `fk_empresa`, `fk_area`, `fk_estado`) VALUES
(4, 'junior', 'el que barre', 'Presencial', 15.00, 'Inactiva', '2025-07-19', 2, 9, 17),
(9, 'manueeeelllll', 'akjsdhfaskjhd', 'Híbrido', 23.00, 'Inactiva', '2025-07-19', 2, 7, 16);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulacion`
--

CREATE TABLE `postulacion` (
  `id_postulacion` bigint(20) NOT NULL,
  `fecha_postulacion` date DEFAULT NULL,
  `estado_postulacion` varchar(30) DEFAULT NULL,
  `fk_postulante` bigint(20) DEFAULT NULL,
  `fk_oferta` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `postulacion`
--

INSERT INTO `postulacion` (`id_postulacion`, `fecha_postulacion`, `estado_postulacion`, `fk_postulante`, `fk_oferta`) VALUES
(7, '2025-07-19', 'Aceptada', 1, 4),
(10, '2025-07-19', 'Aceptada', 2, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulante`
--

CREATE TABLE `postulante` (
  `id_postulante` bigint(20) NOT NULL,
  `cedula` varchar(50) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `universidad_egreso` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `fk_usuario` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `postulante`
--

INSERT INTO `postulante` (`id_postulante`, `cedula`, `telefono`, `universidad_egreso`, `fecha_nacimiento`, `fk_usuario`) VALUES
(1, '31882343', '04249151636', 'Universidad Nacional Experimental Guayana', '2005-05-24', 4),
(2, '15520184', '04163966344', 'Universidad Nacional Experimental Guayana', '2004-06-24', 5),
(3, '31782782', '04121850970', 'universidad de la calle', '0000-00-00', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulantearea`
--

CREATE TABLE `postulantearea` (
  `id_PostArea` bigint(20) NOT NULL,
  `fk_area` bigint(20) DEFAULT NULL,
  `fk_postulante` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `postulantearea`
--

INSERT INTO `postulantearea` (`id_PostArea`, `fk_area`, `fk_postulante`) VALUES
(1, 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibopago`
--

CREATE TABLE `recibopago` (
  `id_recibo` bigint(20) NOT NULL,
  `mes` varchar(20) DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  `salario_base` decimal(12,2) DEFAULT NULL,
  `monto_inces` decimal(10,2) NOT NULL,
  `monto_ivss` decimal(10,2) NOT NULL,
  `monto_hiring` decimal(10,2) NOT NULL,
  `salario_neto` decimal(12,2) DEFAULT NULL,
  `fk_contratacion` bigint(20) DEFAULT NULL,
  `fk_nomina_detalle` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recibopago`
--

INSERT INTO `recibopago` (`id_recibo`, `mes`, `anio`, `salario_base`, `monto_inces`, `monto_ivss`, `monto_hiring`, `salario_neto`, `fk_contratacion`, `fk_nomina_detalle`) VALUES
(1, '8', 2025, 15.00, 0.08, 0.15, 0.30, 14.47, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` bigint(20) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `clave` varchar(255) DEFAULT NULL,
  `tipo_usuario` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `clave`, `tipo_usuario`) VALUES
(1, 'Adrian', 'Reina', 'adrian@gmail.com', 'adrian', 'admin'),
(2, 'Rafael', 'Rodriguez', 'rafael@gmail.com', 'rafael', 'hiring-group'),
(3, 'Fernando', 'Centeno', 'fernando@gmail.com', 'papu123', 'empresa'),
(4, 'Juan', 'Longart', 'juan@gmail.com', 'juan', 'contratado'),
(5, 'Manuel', 'Garcia', 'manuel@gmail.com', 'manoo', 'contratado'),
(6, 'diego', 'white', 'diego@gmail.com', '123', 'postulante'),
(7, 'joseito', 'a', 'joseito@gmail.com', 'jose', 'postulante');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `areaconocimiento`
--
ALTER TABLE `areaconocimiento`
  ADD PRIMARY KEY (`id_area`);

--
-- Indices de la tabla `banco`
--
ALTER TABLE `banco`
  ADD PRIMARY KEY (`id_banco`);

--
-- Indices de la tabla `contratado`
--
ALTER TABLE `contratado`
  ADD PRIMARY KEY (`id_contratacion`),
  ADD KEY `fk_postulante` (`fk_postulante`),
  ADD KEY `fk_oferta` (`fk_oferta`),
  ADD KEY `fk_banco` (`fk_banco`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`),
  ADD UNIQUE KEY `uq_rif` (`RIF`),
  ADD KEY `creado_por` (`creado_por`),
  ADD KEY `fk_usuario` (`fk_usuario`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `experiencialaboral`
--
ALTER TABLE `experiencialaboral`
  ADD PRIMARY KEY (`id_experiencia`),
  ADD KEY `fk_postulante` (`fk_postulante`);

--
-- Indices de la tabla `nominamensual`
--
ALTER TABLE `nominamensual`
  ADD PRIMARY KEY (`id_nomina`),
  ADD KEY `fk_empresa` (`fk_empresa`);

--
-- Indices de la tabla `nomina_detalle`
--
ALTER TABLE `nomina_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_nomina` (`fk_nomina`),
  ADD KEY `fk_contratado` (`fk_contratado`);

--
-- Indices de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `fk_usuario` (`fk_usuario`);

--
-- Indices de la tabla `ofertalaboral`
--
ALTER TABLE `ofertalaboral`
  ADD PRIMARY KEY (`id_oferta`),
  ADD KEY `fk_area` (`fk_area`),
  ADD KEY `fk_empresa` (`fk_empresa`),
  ADD KEY `fk_estado` (`fk_estado`);

--
-- Indices de la tabla `postulacion`
--
ALTER TABLE `postulacion`
  ADD PRIMARY KEY (`id_postulacion`),
  ADD KEY `fk_postulante` (`fk_postulante`),
  ADD KEY `fk_oferta` (`fk_oferta`);

--
-- Indices de la tabla `postulante`
--
ALTER TABLE `postulante`
  ADD PRIMARY KEY (`id_postulante`),
  ADD UNIQUE KEY `uq_cedula` (`cedula`),
  ADD KEY `fk_usuario` (`fk_usuario`);

--
-- Indices de la tabla `postulantearea`
--
ALTER TABLE `postulantearea`
  ADD PRIMARY KEY (`id_PostArea`),
  ADD KEY `fk_postulante` (`fk_postulante`),
  ADD KEY `fk_area` (`fk_area`);

--
-- Indices de la tabla `recibopago`
--
ALTER TABLE `recibopago`
  ADD PRIMARY KEY (`id_recibo`),
  ADD KEY `fk_contratacion` (`fk_contratacion`),
  ADD KEY `fk_dp_nd` (`fk_nomina_detalle`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uq_correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `areaconocimiento`
--
ALTER TABLE `areaconocimiento`
  MODIFY `id_area` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `banco`
--
ALTER TABLE `banco`
  MODIFY `id_banco` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `contratado`
--
ALTER TABLE `contratado`
  MODIFY `id_contratacion` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `id_estado` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `experiencialaboral`
--
ALTER TABLE `experiencialaboral`
  MODIFY `id_experiencia` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `nominamensual`
--
ALTER TABLE `nominamensual`
  MODIFY `id_nomina` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `nomina_detalle`
--
ALTER TABLE `nomina_detalle`
  MODIFY `id_detalle` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `ofertalaboral`
--
ALTER TABLE `ofertalaboral`
  MODIFY `id_oferta` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `postulacion`
--
ALTER TABLE `postulacion`
  MODIFY `id_postulacion` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `postulante`
--
ALTER TABLE `postulante`
  MODIFY `id_postulante` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `postulantearea`
--
ALTER TABLE `postulantearea`
  MODIFY `id_PostArea` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `recibopago`
--
ALTER TABLE `recibopago`
  MODIFY `id_recibo` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `contratado`
--
ALTER TABLE `contratado`
  ADD CONSTRAINT `contratado_ibfk_1` FOREIGN KEY (`fk_postulante`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `contratado_ibfk_2` FOREIGN KEY (`fk_oferta`) REFERENCES `ofertalaboral` (`id_oferta`),
  ADD CONSTRAINT `contratado_ibfk_3` FOREIGN KEY (`fk_banco`) REFERENCES `banco` (`id_banco`);

--
-- Filtros para la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD CONSTRAINT `empresa_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `empresa_ibfk_2` FOREIGN KEY (`fk_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `experiencialaboral`
--
ALTER TABLE `experiencialaboral`
  ADD CONSTRAINT `experiencialaboral_ibfk_1` FOREIGN KEY (`fk_postulante`) REFERENCES `postulante` (`id_postulante`);

--
-- Filtros para la tabla `nomina_detalle`
--
ALTER TABLE `nomina_detalle`
  ADD CONSTRAINT `nomina_detalle_ibfk_1` FOREIGN KEY (`fk_nomina`) REFERENCES `nominamensual` (`id_nomina`),
  ADD CONSTRAINT `nomina_detalle_ibfk_2` FOREIGN KEY (`fk_contratado`) REFERENCES `contratado` (`id_contratacion`);

--
-- Filtros para la tabla `notificacion`
--
ALTER TABLE `notificacion`
  ADD CONSTRAINT `notificacion_ibfk_1` FOREIGN KEY (`fk_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `ofertalaboral`
--
ALTER TABLE `ofertalaboral`
  ADD CONSTRAINT `ofertalaboral_ibfk_1` FOREIGN KEY (`fk_area`) REFERENCES `areaconocimiento` (`id_area`),
  ADD CONSTRAINT `ofertalaboral_ibfk_3` FOREIGN KEY (`fk_estado`) REFERENCES `estado` (`id_estado`);

--
-- Filtros para la tabla `postulacion`
--
ALTER TABLE `postulacion`
  ADD CONSTRAINT `postulacion_ibfk_1` FOREIGN KEY (`fk_postulante`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `postulacion_ibfk_2` FOREIGN KEY (`fk_oferta`) REFERENCES `ofertalaboral` (`id_oferta`);

--
-- Filtros para la tabla `postulante`
--
ALTER TABLE `postulante`
  ADD CONSTRAINT `postulante_ibfk_1` FOREIGN KEY (`fk_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `postulantearea`
--
ALTER TABLE `postulantearea`
  ADD CONSTRAINT `postulantearea_ibfk_1` FOREIGN KEY (`fk_postulante`) REFERENCES `postulante` (`id_postulante`),
  ADD CONSTRAINT `postulantearea_ibfk_2` FOREIGN KEY (`fk_area`) REFERENCES `areaconocimiento` (`id_area`);

--
-- Filtros para la tabla `recibopago`
--
ALTER TABLE `recibopago`
  ADD CONSTRAINT `fk_dp_nd` FOREIGN KEY (`fk_nomina_detalle`) REFERENCES `nomina_detalle` (`id_detalle`),
  ADD CONSTRAINT `recibopago_ibfk_1` FOREIGN KEY (`fk_contratacion`) REFERENCES `contratado` (`id_contratacion`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
