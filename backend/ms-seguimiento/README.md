# ms-seguimiento

Microservicio de trazabilidad de incapacidades - Corporate Solutions

## Instalación

```bash
composer install
```

## Correr el servidor

```bash
php -S localhost:8084 -t public
```

## Endpoints

| Método | Ruta                                    | Descripción                    |
|--------|-----------------------------------------|--------------------------------|
| GET    | /seguimiento                            | Listar registros               |
| GET    | /seguimiento/{id}                       | Obtener un registro            |
| GET    | /seguimiento/incapacidad/{id}           | Historial de una incapacidad   |
| GET    | /seguimiento/reporte                    | Reporte con resumen            |
| POST   | /seguimiento                            | Registrar acción               |

## Acciones válidas
creacion, actualizacion, cambio_estado, consulta, aprobacion, rechazo

## Autenticación
Todos los endpoints requieren header: Authorization: <token>
