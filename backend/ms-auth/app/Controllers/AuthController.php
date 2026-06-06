<?php

namespace App\Controllers;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    /**
     * POST /login
     * Inicia sesion con usuario/correo y contrasena
     */
    public function login(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        $credencial = trim($body['usuario'] ?? $body['correo'] ?? '');
        $contrasena = trim($body['contrasena'] ?? '');

        if (empty($credencial) || empty($contrasena)) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'El usuario/correo y la contrasena son obligatorios.',
            ], 400);
        }

        $usuario = Usuario::buscarPorCredencial($credencial);

        if (!$usuario) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        // Validacion de contrasena (texto plano segun la BD entregada)
        // En produccion se usaria password_verify con hash
        if ($usuario->contrasena !== $contrasena) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        $token = $usuario->iniciarSesion();

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Inicio de sesion exitoso.',
            'data'    => [
                'token'   => $token,
                'usuario' => [
                    'id'     => $usuario->id,
                    'nombre' => $usuario->nombre,
                    'correo' => $usuario->correo,
                    'usuario'=> $usuario->usuario,
                    'rol'    => $usuario->rol,
                ],
            ],
        ], 200);
    }

    /**
     * POST /logout
     * Cierra la sesion del usuario autenticado
     */
    public function logout(Request $request, Response $response): Response
    {
        $token = $this->extraerToken($request);

        if (!$token) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Token no proporcionado.',
            ], 401);
        }

        $usuario = Usuario::buscarPorToken($token);

        if (!$usuario) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Token invalido o sesion ya cerrada.',
            ], 401);
        }

        $usuario->cerrarSesion();

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Sesion cerrada correctamente.',
        ], 200);
    }

    /**
     * GET /validate
     * Valida si un token esta activo
     */
    public function validarToken(Request $request, Response $response): Response
    {
        $token = $this->extraerToken($request);

        if (!$token) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Token no proporcionado.',
            ], 401);
        }

        $usuario = Usuario::buscarPorToken($token);

        if (!$usuario) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Token invalido o sesion inactiva.',
            ], 401);
        }

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Token valido.',
            'data'    => [
                'id'     => $usuario->id,
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'usuario'=> $usuario->usuario,
                'rol'    => $usuario->rol,
            ],
        ], 200);
    }

    /**
     * Extrae el token del header Authorization: Bearer <token>
     */
    private function extraerToken(Request $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');
        if (str_starts_with($header, 'Bearer ')) {
            return trim(substr($header, 7));
        }
        return null;
    }

    /**
     * Retorna una respuesta JSON estandarizada
     */
    private function jsonResponse(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}