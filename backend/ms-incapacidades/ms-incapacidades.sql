CREATE TABLE incapacidades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    empleado_id BIGINT UNSIGNED NOT NULL,

    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,

    tipo ENUM(
        'enfermedad_general',
        'accidente_laboral',
        'licencia_medica',
        'incapacidad_temporal'
    ) NOT NULL,

    diagnostico_general TEXT NOT NULL,
    entidad_medica VARCHAR(150) NOT NULL,
    observaciones TEXT NULL,

    dias_incapacidad INT NOT NULL,

    estado ENUM(
        'registrada',
        'en_revision',
        'aprobada',
        'rechazada',
        'finalizada'
    ) DEFAULT 'registrada',

    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

INSERT INTO incapacidades (
    empleado_id,
    fecha_inicio,
    fecha_fin,
    tipo,
    diagnostico_general,
    entidad_medica,
    observaciones,
    dias_incapacidad,
    estado,
    created_at,
    updated_at
)
VALUES
(
    1,
    '2026-06-01',
    '2026-06-05',
    'enfermedad_general',
    'Infeccion respiratoria',
    'Clinica Central',
    'Reposo medico durante cinco dias',
    5,
    'aprobada',
    NOW(),
    NOW()
),
(
    2,
    '2026-06-08',
    '2026-06-10',
    'licencia_medica',
    'Control medico general',
    'Hospital Regional',
    'Seguimiento medico preventivo',
    3,
    'en_revision',
    NOW(),
    NOW()
);