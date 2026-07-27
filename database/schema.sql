-- ============================================================
-- Sistema de Gestión Académica - Esquema PostgreSQL
-- ============================================================
-- Ejecuta este archivo una sola vez sobre tu base de datos
-- (local con pgAdmin/psql, o en la base Postgres que crees en Render).
--
--   psql -U postgres -d gestion_academica -f database/schema.sql
--
-- ============================================================

DROP TABLE IF EXISTS convocatoria CASCADE;
DROP TABLE IF EXISTS matricula CASCADE;
DROP TABLE IF EXISTS asignatura CASCADE;
DROP TABLE IF EXISTS titulacion CASCADE;
DROP TABLE IF EXISTS alumno CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;

-- ---------------------------------------------------------------
-- USUARIOS (login del sistema)
-- ---------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario  SERIAL PRIMARY KEY,
    usuario     VARCHAR(50) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL, -- se guarda con password_hash() de PHP (bcrypt)
    rol         VARCHAR(20) NOT NULL DEFAULT 'admin'
);

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
-- TITULACION
-- ---------------------------------------------------------------
CREATE TABLE titulacion (
    id_titulacion  SERIAL PRIMARY KEY,
    nombre         VARCHAR(150) NOT NULL UNIQUE,
    descripcion    VARCHAR(500)
);

-- ---------------------------------------------------------------
-- ASIGNATURA (pertenece a una titulación)
-- ---------------------------------------------------------------
CREATE TABLE asignatura (
    id_asignatura  SERIAL PRIMARY KEY,
    nombre         VARCHAR(150) NOT NULL,
    creditos       INTEGER NOT NULL CHECK (creditos BETWEEN 1 AND 20),
    id_titulacion  INTEGER NOT NULL REFERENCES titulacion(id_titulacion) ON DELETE CASCADE
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

-- ============================================================
-- DATOS DE PRUEBA
-- ============================================================

-- Usuario para entrar al sistema:
--   usuario:  admin
--   password: admin123
INSERT INTO usuarios (usuario, password, rol) VALUES
('admin', '$2y$10$VsTl3hcI9guJGYjGyFOa4uPVVQKf7VmQCmL3g0A9oP4eh9NEYMZ9e', 'admin');

INSERT INTO titulacion (nombre, descripcion) VALUES
('Ingeniería en Tecnologías de la Información', 'Formación en desarrollo de software, redes y bases de datos.'),
('Ingeniería Industrial', 'Formación en procesos de producción y optimización.');

INSERT INTO alumno (cedula, nombre, apellido, correo, fecha_nacimiento) VALUES
('1712345678', 'María', 'Pérez', 'maria.perez@example.com', '2001-05-14'),
('1798765432', 'Carlos', 'Gómez', 'carlos.gomez@example.com', '2000-11-02');

INSERT INTO asignatura (nombre, creditos, id_titulacion) VALUES
('Bases de Datos', 5, 1),
('Programación Web', 6, 1),
('Procesos Industriales', 4, 2);

INSERT INTO matricula (id_alumno, id_asignatura, fecha_matricula, estado) VALUES
(1, 1, '2026-03-01', 'Activa'),
(2, 2, '2026-03-01', 'Activa');

INSERT INTO convocatoria (id_asignatura, fecha_examen, tipo) VALUES
(1, '2026-07-20', 'Parcial'),
(2, '2026-07-23', 'Final');
