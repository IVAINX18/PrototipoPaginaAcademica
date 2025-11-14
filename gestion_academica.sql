-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-11-2025 a las 04:12:19
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
-- Base de datos: `gestion_academica`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

USE bliw09vjkqs6npl8riiy;

CREATE TABLE `actividades` (
  `id_actividad` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `porcentaje` int(11) DEFAULT NULL,
  `estado` enum('Activo','Pendiente') DEFAULT NULL,
  `id_curso` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actividades`
--

INSERT INTO `actividades` (`id_actividad`, `nombre`, `tipo`, `fecha_entrega`, `porcentaje`, `estado`, `id_curso`) VALUES
(1, 'TAREA 2', 'TRABAJO', '2025-11-13', 50, 'Activo', 2),
(2, 'Tarea 1 - Algoritmos básicos', 'Taller', '2025-11-15', 20, 'Activo', 1),
(3, 'Examen 1 - Condicionales y ciclos', 'Examen', '2025-11-22', 30, 'Activo', 1),
(4, 'Proyecto 1 - Diseño de componentes', 'Trabajo', '2025-11-18', 25, 'Activo', 2),
(5, 'Taller 2 - Patrón MVC', 'Taller', '2025-11-25', 25, 'Activo', 2),
(6, 'Consulta - Normalización', 'Tarea', '2025-11-19', 20, 'Activo', 3),
(7, 'Proyecto SQL - Base de datos relacional', 'Trabajo', '2025-11-28', 30, 'Activo', 3),
(8, 'Laboratorio 1 - Configuración de red', 'Taller', '2025-11-20', 25, 'Activo', 4),
(9, 'Examen práctico - Subredes y protocolos', 'Examen', '2025-11-29', 25, 'Activo', 4),
(10, 'Taller 1 - Procesos e hilos', 'Taller', '2025-11-16', 20, 'Activo', 5),
(11, 'Exposición - Sistemas Operativos modernos', 'Trabajo', '2025-11-24', 30, 'Activo', 5),
(12, 'Tarea 1 - Casos de uso UML', 'Tarea', '2025-11-17', 20, 'Activo', 6),
(13, 'Proyecto - Desarrollo en equipo', 'Trabajo', '2025-11-26', 30, 'Activo', 6),
(14, 'Cuestionario - Introducción a la IA', 'Tarea', '2025-11-18', 25, 'Activo', 7),
(15, 'Proyecto - Chatbot básico', 'Trabajo', '2025-11-27', 25, 'Activo', 7),
(16, 'Ensayo - Principios de ciberseguridad', 'Tarea', '2025-11-19', 20, 'Activo', 8),
(17, 'Laboratorio - Ataques de red simulados', 'Taller', '2025-11-30', 30, 'Activo', 8),
(18, 'Tarea - Estructura HTML y CSS', 'Tarea', '2025-11-20', 25, 'Activo', 9),
(19, 'Proyecto - Página web completa', 'Trabajo', '2025-12-01', 25, 'Activo', 9),
(20, 'Investigación - Tipos de servicios en la nube', 'Tarea', '2025-11-21', 20, 'Activo', 10),
(21, 'Proyecto - Implementación en AWS', 'Trabajo', '2025-12-02', 30, 'Activo', 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id_curso` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('Activo','Pendiente') DEFAULT NULL,
  `id_docente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id_curso`, `nombre`, `codigo`, `descripcion`, `estado`, `id_docente`) VALUES
(1, 'Programación I', '100101', 'Introducción a la programación estructurada', 'Activo', 3),
(2, 'Arquitectura de Software', '107289', 'Enseña a diseña y estructura sistemas eficientes, escalables y bien organizados.', 'Activo', 1),
(3, 'Bases de Datos', '100102', 'Diseño y administración de bases de datos', 'Activo', 5),
(4, 'Redes de Computadores', '100103', 'Fundamentos de redes y protocolos', 'Activo', 2),
(5, 'Sistemas Operativos', '100104', 'Gestión de recursos del sistema operativo', 'Activo', 8),
(6, 'Ingeniería de Software', '100105', 'Modelado y desarrollo de software', 'Activo', 4),
(7, 'Inteligencia Artificial', '100106', 'Fundamentos y aplicaciones de IA', 'Activo', 6),
(8, 'Seguridad Informática', '100107', 'Principios de seguridad y criptografía', 'Activo', 9),
(9, 'Desarrollo Web', '100108', 'Creación de aplicaciones web modernas', 'Activo', 7),
(10, 'Computación en la Nube', '100109', 'Servicios y arquitecturas en la nube', 'Activo', 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes` (
  `id_docente` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `docentes`
--

INSERT INTO `docentes` (`id_docente`, `nombre`, `correo`, `telefono`) VALUES
(1, 'Andres Barrett', 'Andre.Barett@campusucc.edu.co', '3209984449'),
(2, 'Vidarte Goat', 'Javier.Vidarte@campusucc.edu.co', '3109990302'),
(3, 'Oscar Valderrama', 'Oscar.valderrama@gmail.com', '3202341567'),
(4, 'Laura Méndez', 'laura.mendez@campusucc.edu.co', '3159087741'),
(5, 'Carlos Rojas', 'carlos.rojas@campusucc.edu.co', '3209845632'),
(6, 'María Fernanda López', 'maria.lopez@campusucc.edu.co', '3112059874'),
(7, 'Julián Torres', 'julian.torres@campusucc.edu.co', '3017896543'),
(8, 'Camila Pérez', 'camila.perez@campusucc.edu.co', '3176245098'),
(9, 'Sebastián Ramírez', 'sebastian.ramirez@campusucc.edu.co', '3129874501'),
(10, 'Daniela Castaño', 'daniela.castano@campusucc.edu.co', '3196708425');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id_estudiante` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `id_curso` int(11) DEFAULT NULL,
  `nota_final` decimal(3,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id_estudiante`, `nombre`, `id_curso`, `nota_final`) VALUES
(1, 'Ivan velazco ', 2, 3.0),
(2, 'Cristian Toro ', 2, 4.5),
(3, 'Ana Maria ', 2, 4.8),
(4, 'Laura Gutiérrez', 3, 4.5),
(5, 'Andrés Morales', 1, 3.8),
(6, 'Sofía Ramírez', 7, 4.2),
(7, 'Camilo Pérez', 2, 2.9),
(8, 'Valentina Torres', 5, 4.0),
(9, 'Sebastián López', 8, 3.3),
(10, 'Daniela Vargas', 4, 4.7),
(11, 'Juan Esteban Ruiz', 10, 3.5),
(12, 'María José Herrera', 6, 2.8),
(13, 'Felipe Cárdenas', 9, 3.9),
(14, 'Sara González', 1, 4.6),
(15, 'Julián Castro', 3, 3.1),
(16, 'Alejandra Martínez', 5, 4.9),
(17, 'David Montoya', 8, 2.5),
(18, 'Natalia Pineda', 10, 4.3);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id_actividad`),
  ADD KEY `id_curso` (`id_curso`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id_curso`),
  ADD KEY `id_docente` (`id_docente`);

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD PRIMARY KEY (`id_docente`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id_estudiante`),
  ADD KEY `id_curso` (`id_curso`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id_curso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
  MODIFY `id_docente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id_estudiante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD CONSTRAINT `actividades_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`);

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`);

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
