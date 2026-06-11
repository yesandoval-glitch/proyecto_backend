CREATE DATABASE ms_empleados;
USE ms_empleados;

CREATE TABLE empleados (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    documento VARCHAR(30) NOT NULL UNIQUE,
    correo VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(30) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    area VARCHAR(100) NOT NULL,
    fecha_ingreso DATE NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

INSERT INTO empleados (nombres, apellidos, documento, correo, telefono, cargo, area, fecha_ingreso, estado, created_at, updated_at) VALUES
('Carlos', 'Ramirez', '1000123456', 'carlos@empresa.com', '3001234567', 'Analista', 'Tecnologia', '2024-01-15', 'activo', NOW(), NOW()),
('Laura', 'Martinez', '1000456789', 'laura@empresa.com', '3019876543', 'Auxiliar', 'Gestion Humana', '2023-07-10', 'activo', NOW(), NOW());