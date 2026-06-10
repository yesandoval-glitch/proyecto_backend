# ms-auth

Microservicio de autenticación - Corporate Solutions

## Instalación

```bash
composer install
```

## Configurar .env

Edita el archivo `.env` con tus datos de MySQL.

## Correr el servidor

```bash
php -S localhost:8081 -t public
```

## Endpoints

| Método | Ruta           | Descripción              |
|--------|----------------|--------------------------|
| POST   | /auth/login    | Iniciar sesión           |
| POST   | /auth/logout   | Cerrar sesión            |
| GET    | /auth/validar  | Validar token activo     |

## Ejemplo login

```json
POST /auth/login
{
  "usuario": "admin",
  "contrasena": "admin123"
}
```
