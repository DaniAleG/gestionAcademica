-- ============================================================
-- Sistema de Gestión Académica - Esquema PostgreSQL
-- ============================================================
-- Ejecuta este archivo una sola vez sobre tu base de datos
-- (local con pgAdmin/psql, o en la base Postgres que crees en Render).
--
--   psql "TU_DATABASE_URL" -f database/schema.sql
--
-- ADVERTENCIA: este script BORRA y recrea todas las tablas.
-- ============================================================

DROP TABLE IF EXISTS nota CASCADE;
DROP TABLE IF EXISTS convocatoria CASCADE;
DROP TABLE IF EXISTS matricula CASCADE;
DROP TABLE IF EXISTS asignatura CASCADE;
DROP TABLE IF EXISTS titulacion CASCADE;
DROP TABLE IF EXISTS maestro CASCADE;
DROP TABLE IF EXISTS alumno CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;

-- ---------------------------------------------------------------
-- ALUMNO
-- ---------------------------------------------------------------
CREATE TABLE alumno (
    id_alumno         SERIAL PRIMARY KEY,
    cedula            VARCHAR(10) NOT NULL UNIQUE CHECK (cedula ~ '^\d{10}$'),
    nombre            VARCHAR(100) NOT NULL,
    apellido          VARCHAR(100) NOT NULL,
    correo            VARCHAR(150) NOT NULL UNIQUE,
    fecha_nacimiento  DATE NOT NULL
);

-- ---------------------------------------------------------------
-- MAESTRO
-- ---------------------------------------------------------------
CREATE TABLE maestro (
    id_maestro        SERIAL PRIMARY KEY,
    cedula            VARCHAR(10) NOT NULL UNIQUE CHECK (cedula ~ '^\d{10}$'),
    nombre            VARCHAR(100) NOT NULL,
    apellido          VARCHAR(100) NOT NULL,
    correo            VARCHAR(150) NOT NULL UNIQUE,
    fecha_nacimiento  DATE NOT NULL,
    especialidad      VARCHAR(150)
);

-- ---------------------------------------------------------------
-- USUARIOS (login del sistema; puede estar ligado a un alumno o
-- a un maestro según el rol; para 'admin' ambos quedan en NULL)
-- ---------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario  SERIAL PRIMARY KEY,
    usuario     VARCHAR(50) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL, -- se guarda con password_hash() de PHP (bcrypt)
    rol         VARCHAR(20) NOT NULL DEFAULT 'admin' CHECK (rol IN ('admin', 'maestro', 'alumno')),
    id_alumno   INTEGER REFERENCES alumno(id_alumno) ON DELETE CASCADE,
    id_maestro  INTEGER REFERENCES maestro(id_maestro) ON DELETE CASCADE
);

-- ---------------------------------------------------------------
-- TITULACION
-- ---------------------------------------------------------------
CREATE TABLE titulacion (
    id_titulacion  SERIAL PRIMARY KEY,
    nombre         VARCHAR(150) NOT NULL UNIQUE,
    descripcion    VARCHAR(500)
);

-- ---------------------------------------------------------------
-- ASIGNATURA (pertenece a una titulación; puede tener un maestro asignado)
-- ---------------------------------------------------------------
CREATE TABLE asignatura (
    id_asignatura  SERIAL PRIMARY KEY,
    nombre         VARCHAR(150) NOT NULL,
    creditos       INTEGER NOT NULL CHECK (creditos BETWEEN 1 AND 20),
    id_titulacion  INTEGER NOT NULL REFERENCES titulacion(id_titulacion) ON DELETE CASCADE,
    id_maestro     INTEGER REFERENCES maestro(id_maestro) ON DELETE SET NULL
);

-- ---------------------------------------------------------------
-- MATRICULA (relaciona alumno <-> asignatura)
-- ---------------------------------------------------------------
CREATE TABLE matricula (
    id_matricula      SERIAL PRIMARY KEY,
    id_alumno         INTEGER NOT NULL REFERENCES alumno(id_alumno) ON DELETE CASCADE,
    id_asignatura     INTEGER NOT NULL REFERENCES asignatura(id_asignatura) ON DELETE CASCADE,
    fecha_matricula   DATE NOT NULL,
    estado            VARCHAR(20) NOT NULL CHECK (estado IN ('Activa', 'Inactiva', 'Retirada')),
    UNIQUE (id_alumno, id_asignatura)
);

-- ---------------------------------------------------------------
-- CONVOCATORIA (exámenes de una asignatura)
-- ---------------------------------------------------------------
CREATE TABLE convocatoria (
    id_convocatoria  SERIAL PRIMARY KEY,
    id_asignatura    INTEGER NOT NULL REFERENCES asignatura(id_asignatura) ON DELETE CASCADE,
    fecha_examen     DATE NOT NULL,
    tipo             VARCHAR(20) NOT NULL CHECK (tipo IN ('Parcial', 'Final', 'Supletorio'))
);

-- ---------------------------------------------------------------
-- NOTA (calificación de un alumno matriculado, por tipo de evaluación)
-- ---------------------------------------------------------------
CREATE TABLE nota (
    id_nota          SERIAL PRIMARY KEY,
    id_matricula     INTEGER NOT NULL REFERENCES matricula(id_matricula) ON DELETE CASCADE,
    tipo             VARCHAR(20) NOT NULL CHECK (tipo IN ('Parcial', 'Final', 'Supletorio')),
    nota             NUMERIC(4,2) NOT NULL CHECK (nota BETWEEN 0 AND 10),
    fecha_registro   DATE NOT NULL DEFAULT CURRENT_DATE,
    UNIQUE (id_matricula, tipo)
);

-- ============================================================
-- DATOS DE PRUEBA
-- ============================================================

INSERT INTO titulacion (nombre, descripcion) VALUES
('Ingeniería en Tecnologías de la Información', 'Formación en desarrollo de software, redes y bases de datos.'),
('Ingeniería Industrial', 'Formación en procesos de producción y optimización.');

INSERT INTO alumno (cedula, nombre, apellido, correo, fecha_nacimiento) VALUES
('1712345678', 'María', 'Pérez', 'maria.perez@example.com', '2001-05-14'),
('1798765432', 'Carlos', 'Gómez', 'carlos.gomez@example.com', '2000-11-02');

INSERT INTO maestro (cedula, nombre, apellido, correo, fecha_nacimiento, especialidad) VALUES
('1103351562', 'Lucía', 'Ramírez', 'lucia.ramirez@example.com', '1985-04-10', 'Bases de Datos y Backend'),
('0919876541', 'Fernando', 'Vega', 'fernando.vega@example.com', '1980-09-23', 'Ingeniería de Software');

INSERT INTO asignatura (nombre, creditos, id_titulacion, id_maestro) VALUES
('Bases de Datos', 5, 1, 1),
('Programación Web', 6, 1, 2),
('Procesos Industriales', 4, 2, NULL);

INSERT INTO matricula (id_alumno, id_asignatura, fecha_matricula, estado) VALUES
(1, 1, '2026-03-01', 'Activa'),
(2, 2, '2026-03-01', 'Activa');

INSERT INTO convocatoria (id_asignatura, fecha_examen, tipo) VALUES
(1, '2026-09-20', 'Parcial'),
(2, '2026-09-23', 'Final');

INSERT INTO nota (id_matricula, tipo, nota) VALUES
(1, 'Parcial', 8.50),
(2, 'Parcial', 6.00);

-- Usuarios del sistema:
--   admin   / admin123    (acceso total)
--   lucia   / maestro123  (rol maestro, ligado al maestro Lucía Ramírez)
--   maria   / alumno123   (rol alumno, ligado a la alumna María Pérez)
INSERT INTO usuarios (usuario, password, rol, id_alumno, id_maestro) VALUES
('admin', '$2y$10$VsTl3hcI9guJGYjGyFOa4uPVVQKf7VmQCmL3g0A9oP4eh9NEYMZ9e', 'admin', NULL, NULL),
('lucia', '$2y$10$6ERFZ2Pg6Nl115iWu/Z3G.PclxyXrDyI5AkyHsYGwc1JBTANJOIG6', 'maestro', NULL, 1),
('maria', '$2y$10$Sow30MFtHjaMKRc3aFBpJuFK6fsndgJ2moF4oIynPLyPrBPDns9rC', 'alumno', 1, NULL);
