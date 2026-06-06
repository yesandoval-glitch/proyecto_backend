<?php

namespace App\Middleware;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token  = null;

        if (str_starts_with($header, 'Bearer ')) {
            $token = trim(substr($header, 7));
        }

        if (!$token) {
            return $this->unauthorized('Token no proporcionado.');
        }

        $usuario = Usuario::buscarPorToken($token);

        if (!$usuario) {
            return $this->unauthorized('Token invalido o sesion inactiva.');
        }

        // Inyecta el usuario autenticado en los atributos del request
        $request = $request->withAttribute('usuario_autenticado', $usuario);

        return $handler->handle($request);
    }

    private function unauthorized(string $mensaje): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $mensaje,
        ], JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
