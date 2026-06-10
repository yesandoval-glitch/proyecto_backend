<?php
namespace App\Controllers;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function login(Request $request, Response $response): Response
    {
        $data      = $request->getParsedBody();
        $login     = $data['usuario'] ?? $data['correo'] ?? '';
        $contrasena = $data['contrasena'] ?? '';

        if (!$login || !$contrasena) {
            $response->getBody()->write(json_encode(['error' => 'Usuario y contrasena son requeridos']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $usuario = Usuario::where(function ($q) use ($login) {
            $q->where('usuario', $login)->orWhere('correo', $login);
        })->where('estado', 'activo')->first();

        if (!$usuario || $usuario->contrasena !== $contrasena) {
            $response->getBody()->write(json_encode(['error' => 'Credenciales incorrectas']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $token = bin2hex(random_bytes(32));
        $usuario->token        = $token;
        $usuario->save();

        $response->getBody()->write(json_encode([
            'mensaje' => 'Inicio de sesion exitoso',
            'token'   => $token,
            'usuario' => [
                'id'     => $usuario->id,
                'nombre' => $usuario->nombre,
                'rol'    => $usuario->rol
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function logout(Request $request, Response $response): Response
    {
        $token   = $request->getHeaderLine('Authorization');
        $usuario = Usuario::where('token', $token)->where('sesion_activa', true)->first();

        if (!$usuario) {
            $response->getBody()->write(json_encode(['error' => 'Token invalido o sesion ya cerrada']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $usuario->token        = null;
        $usuario->sesion_activa = false;
        $usuario->save();

        $response->getBody()->write(json_encode(['mensaje' => 'Sesion cerrada correctamente']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function validar(Request $request, Response $response): Response
    {
        $token   = $request->getHeaderLine('Authorization');
        $usuario = Usuario::where('token', $token)->where('sesion_activa', true)->first();

        if (!$usuario) {
            $response->getBody()->write(json_encode(['valido' => false, 'mensaje' => 'Token invalido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $response->getBody()->write(json_encode([
            'valido'  => true,
            'usuario' => $usuario->nombre,
            'rol'     => $usuario->rol,
            'id'      => $usuario->id
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
