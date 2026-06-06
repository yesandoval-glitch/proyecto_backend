# Backend - Sistema de Gestión de Incapacidades Médicas
**Corporate Solutions | Programación Avanzada**

---

## Arquitectura

```
backend/
├── ms-auth/            → Puerto 8001
├── ms-empleados/       → Puerto 8002
├── ms-incapacidades/   → Puerto 8003
└── ms-seguimiento/     → Puerto 8004
```

Cada microservicio es **independiente**: tiene su propia base de datos, conexión y API REST.

---

## Instalación y configuración

### Requisitos
- PHP 8.0+
- Composer
- MySQL
- Extensiones PHP: `pdo`, `pdo_mysql`, `mbstring`, `openssl`

### Pasos para cada microservicio

```bash
# 1. Entrar al directorio
cd ms-auth   # (o ms-empleados, ms-incapacidades, ms-seguimiento)

# 2. Instalar dependencias
composer install

# 3. Configurar entorno
cp .env.example .env
# Editar .env con tus credenciales de base de datos

# 4. Crear la base de datos en MySQL e importar el SQL del documento

# 5. Levantar el servidor de desarrollo
php -S localhost:8001 -t public   # Ajustar puerto según microservicio
```

### Puertos sugeridos
| Microservicio   | Puerto |
|-----------------|--------|
| ms-auth         | 8001   |
| ms-empleados    | 8002   |
| ms-incapacidades| 8003   |
| ms-seguimiento  | 8004   |

---

## Autenticación

Todos los endpoints (excepto `POST /login`) requieren el header:

```
Authorization: Bearer <token>
```

El token se obtiene al hacer login y se invalida al cerrar sesión.

---

## Endpoints

### ms-auth (puerto 8001)

| Método | Ruta        | Descripción              | Auth |
|--------|-------------|--------------------------|------|
| POST   | /login      | Iniciar sesión           | No   |
| POST   | /logout     | Cerrar sesión            | Sí   |
| GET    | /validate   | Validar token activo     | Sí   |

#### POST /login
```json
// Body
{ "usuario": "admin", "contrasena": "admin123" }

// Respuesta 200
{
  "success": true,
  "message": "Inicio de sesion exitoso.",
  "data": {
    "token": "abc123...",
    "usuario": { "id": 1, "nombre": "...", "rol": "administrador" }
  }
}
```

---

### ms-empleados (puerto 8002)

| Método | Ruta                      | Descripción              |
|--------|---------------------------|--------------------------|
| GET    | /empleados                | Listar empleados         |
| GET    | /empleados/{id}           | Obtener empleado         |
| POST   | /empleados                | Crear empleado           |
| PUT    | /empleados/{id}           | Editar empleado          |
| PATCH  | /empleados/{id}/estado    | Cambiar estado           |

**Filtros GET /empleados:** `?documento=&area=&estado=`

#### POST /empleados
```json
{
  "nombres": "Carlos",
  "apellidos": "Ramirez",
  "documento": "1000123456",
  "correo": "carlos@empresa.com",
  "telefono": "3001234567",
  "cargo": "Analista",
  "area": "Tecnologia",
  "fecha_ingreso": "2024-01-15"
}
```

#### PATCH /empleados/{id}/estado
```json
{ "estado": "inactivo" }
```

---

### ms-incapacidades (puerto 8003)

| Método | Ruta                         | Descripción              |
|--------|------------------------------|--------------------------|
| GET    | /incapacidades               | Listar incapacidades     |
| GET    | /incapacidades/{id}          | Obtener incapacidad      |
| POST   | /incapacidades               | Registrar incapacidad    |
| PUT    | /incapacidades/{id}          | Editar incapacidad       |
| PATCH  | /incapacidades/{id}/finalizar| Finalizar incapacidad    |

**Filtros GET /incapacidades:** `?empleado_id=&estado=&tipo=&fecha=`

#### POST /incapacidades
```json
{
  "empleado_id": 1,
  "fecha_inicio": "2026-06-10",
  "fecha_fin": "2026-06-15",
  "tipo": "enfermedad_general",
  "diagnostico_general": "Infección respiratoria",
  "entidad_medica": "Clínica Central",
  "observaciones": "Reposo absoluto"
}
```
> **Nota:** `dias_incapacidad` se calcula automáticamente.

**Tipos válidos:** `enfermedad_general`, `accidente_laboral`, `licencia_medica`, `incapacidad_temporal`

**Estados:** `registrada`, `en_revision`, `aprobada`, `rechazada`, `finalizada`

---

### ms-seguimiento (puerto 8004)

| Método | Ruta                                  | Descripción              |
|--------|---------------------------------------|--------------------------|
| GET    | /seguimientos                         | Listar seguimientos      |
| GET    | /seguimientos/{id}                    | Obtener seguimiento      |
| GET    | /seguimientos/historial/{incapacidad_id} | Historial de incapacidad |
| POST   | /seguimientos                         | Registrar seguimiento    |

**Filtros GET /seguimientos:** `?incapacidad_id=&estado=`

#### POST /seguimientos
```json
{
  "incapacidad_id": 1,
  "fecha": "2026-06-10",
  "comentario": "Documentos verificados, se procede a aprobar.",
  "estado": "aprobada",
  "usuario_responsable": "admin"
}
```

---

## Formato de respuesta estándar

```json
// Éxito
{
  "success": true,
  "message": "Operación exitosa.",
  "data": { ... }
}

// Error de validación (422)
{
  "success": false,
  "message": "Errores de validacion.",
  "errores": ["El campo 'nombres' es obligatorio."]
}

// Error no encontrado (404)
{
  "success": false,
  "message": "Empleado no encontrado."
}
```

---

## Estructura de cada microservicio

```
ms-auth/
├── app/
│   ├── Config/
│   │   └── Database.php       ← Singleton de conexión Eloquent
│   ├── Controllers/
│   │   └── AuthController.php ← Lógica de negocio (POO)
│   ├── Middleware/
│   │   ├── AuthMiddleware.php  ← Valida token
│   │   └── CorsMiddleware.php  ← Headers CORS
│   ├── Models/
│   │   └── Usuario.php        ← Modelo Eloquent
│   └── Routes/
│       └── routes.php         ← Definición de rutas
├── public/
│   └── index.php              ← Entry point
├── composer.json
└── .env.example
```

---

## Convención de commits

```
git commit -m "TuNombre: [descripción del cambio]"
```
