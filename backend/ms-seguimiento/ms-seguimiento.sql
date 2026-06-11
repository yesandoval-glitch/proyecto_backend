CREATE DATABASE ms_seguimiento;
USE ms_seguimiento;

CREATE TABLE seguimientos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    incapacidad_id BIGINT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    comentario TEXT NOT NULL,
    estado ENUM('registrada','en_revision','aprobada','rechazada','finalizada') NOT NULL,
    usuario_responsable VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

INSERT INTO seguimientos (incapacidad_id, fecha, comentario, estado, usuario_responsable, created_at, updated_at) VALUES
(1, '2026-06-01', 'Incapacidad registrada correctamente', 'registrada', 'gestionhumana', NOW(), NOW()),
(1, '2026-06-02', 'Incapacidad aprobada por gestion humana', 'aprobada', 'admin', NOW(), NOW()),
(2, '2026-06-08', 'Pendiente validacion de soportes medicos', 'en_revision', 'gestionhumana', NOW(), NOW());