# ms-empleados

Microservicio de gestión de empleados - Corporate Solutions

## Instalación

```bash
composer install
```

## Correr el servidor

```bash
php -S localhost:8082 -t public
```

## Endpoints

| Método | Ruta                        | Descripción              |
|--------|-----------------------------|--------------------------|
| GET    | /empleados                  | Listar empleados         |
| GET    | /empleados/{id}             | Obtener empleado         |
| POST   | /empleados                  | Crear empleado           |
| PUT    | /empleados/{id}             | Actualizar empleado      |
| PATCH  | /empleados/{id}/estado      | Cambiar estado           |

## Filtros disponibles (GET /empleados)

- `?documento=123`
- `?area=Tecnologia`
- `?estado=activo`

## Autenticación

Todos los endpoints requieren el header:
```
Authorization: <token>
```
