CREATE DATABASE ms_auth;
USE ms_auth;

CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'gestion_humana', 'empleado') NOT NULL,
    token VARCHAR(255) NULL,
    sesion_activa BOOLEAN DEFAULT FALSE,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

INSERT INTO usuarios (nombre, correo, usuario, contrasena, rol, token, sesion_activa, estado, created_at, updated_at) VALUES
('Administrador General', 'admin@empresa.com', 'admin', 'admin123', 'administrador', NULL, FALSE, 'activo', NOW(), NOW()),
('Analista Gestion Humana', 'gh@empresa.com', 'gestionhumana', 'gh123', 'gestion_humana', NULL, FALSE, 'activo', NOW(), NOW());