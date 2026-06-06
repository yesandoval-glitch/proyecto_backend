<?php

namespace App\Middleware;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    private Client $httpClient;
    private string $authServiceUrl;

    public function __construct()
    {
        $this->authServiceUrl = $_ENV['AUTH_SERVICE_URL'] ?? 'http://localhost:8001';
        $this->httpClient     = new Client(['timeout' => 5]);
    }

    public function process(Request $request, Handler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $token      = null;

        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = trim(substr($authHeader, 7));
        }

        if (!$token) {
            return $this->unauthorized('Token no proporcionado.');
        }

        try {
            $res  = $this->httpClient->get("{$this->authServiceUrl}/validate", [
                'headers' => ['Authorization' => "Bearer {$token}"],
            ]);
            $data = json_decode((string) $res->getBody(), true);

            if (!($data['success'] ?? false)) {
                return $this->unauthorized('Token invalido o sesion inactiva.');
            }

            // Adjunta datos del usuario al request para uso en controladores
            $request = $request->withAttribute('usuario_autenticado', $data['data']);
        } catch (RequestException $e) {
            return $this->unauthorized('No se pudo validar el token con el servicio de autenticacion.');
        }

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
