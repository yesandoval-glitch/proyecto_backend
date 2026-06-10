# ms-incapacidades

Microservicio de gestión de incapacidades - Corporate Solutions

## Instalación

```bash
composer install
```

## Correr el servidor

```bash
php -S localhost:8083 -t public
```

## Endpoints

| Método | Ruta                           | Descripción           |
|--------|--------------------------------|-----------------------|
| GET    | /incapacidades                 | Listar                |
| GET    | /incapacidades/{id}            | Obtener una           |
| POST   | /incapacidades                 | Registrar             |
| PUT    | /incapacidades/{id}            | Actualizar            |
| PATCH  | /incapacidades/{id}/estado     | Cambiar estado        |

## Tipos válidos
enfermedad_general, accidente_trabajo, enfermedad_laboral, maternidad, paternidad

## Estados válidos
registrada, en_proceso, aprobada, rechazada

## Autenticación
Todos los endpoints requieren header: Authorization: <token>
