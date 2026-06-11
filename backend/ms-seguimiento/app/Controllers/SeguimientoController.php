<?php
namespace App\Controllers;

use App\Models\Seguimiento;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SeguimientoController
{
    private function json(Response $response, $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private function obtenerUsuario(string $token): ?array
    {
        $ch = curl_init('http://localhost:8081/auth/validar');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: $token"]);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status !== 200) return null;
        return json_decode($result, true);
    }

    private function validarIncapacidad(int $id, string $token): bool
    {
        $ch = curl_init("http://localhost:8083/incapacidades/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: $token"]);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $status === 200;
    }

    public function listar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Seguimiento::query();

        if (!empty($params['incapacidad_id']))    $query->where('incapacidad_id', $params['incapacidad_id']);
        if (!empty($params['usuario_responsable'])) $query->where('usuario_responsable', $params['usuario_responsable']);
        if (!empty($params['estado']))             $query->where('estado', $params['estado']);

        return $this->json($response, $query->orderBy('fecha', 'desc')->get());
    }

    public function obtener(Request $request, Response $response, array $args): Response
    {
        $seguimiento = Seguimiento::find($args['id']);
        if (!$seguimiento) {
            return $this->json($response, ['error' => 'Registro no encontrado'], 404);
        }
        return $this->json($response, $seguimiento);
    }

    public function porIncapacidad(Request $request, Response $response, array $args): Response
    {
        $registros = Seguimiento::where('incapacidad_id', $args['incapacidad_id'])
            ->orderBy('fecha', 'desc')->get();
        return $this->json($response, $registros);
    }

    public function registrar(Request $request, Response $response): Response
    {
        $data  = $request->getParsedBody();
        $token = $request->getHeaderLine('Authorization');

        $requeridos = ['incapacidad_id', 'comentario', 'estado'];
        foreach ($requeridos as $campo) {
            if (empty($data[$campo])) {
                return $this->json($response, ['error' => "El campo $campo es requerido"], 400);
            }
        }

        $estadosValidos = ['registrada', 'en_revision', 'aprobada', 'rechazada', 'finalizada'];
        if (!in_array($data['estado'], $estadosValidos)) {
            return $this->json($response, ['error' => 'Estado invalido'], 400);
        }

        $usuario = $this->obtenerUsuario($token);
        if (!$usuario) {
            return $this->json($response, ['error' => 'No se pudo obtener informacion del usuario'], 401);
        }

        if (!$this->validarIncapacidad((int)$data['incapacidad_id'], $token)) {
            return $this->json($response, ['error' => 'La incapacidad no existe'], 404);
        }

        $seguimiento = Seguimiento::create([
            'incapacidad_id'      => $data['incapacidad_id'],
            'fecha'               => date('Y-m-d'),
            'comentario'          => $data['comentario'],
            'estado'              => $data['estado'],
            'usuario_responsable' => $usuario['usuario']
        ]);

        return $this->json($response, ['mensaje' => 'Seguimiento registrado correctamente', 'seguimiento' => $seguimiento], 201);
    }

    public function reporte(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Seguimiento::query();

        if (!empty($params['fecha_inicio'])) $query->where('fecha', '>=', $params['fecha_inicio']);
        if (!empty($params['fecha_fin']))    $query->where('fecha', '<=', $params['fecha_fin']);
        if (!empty($params['incapacidad_id'])) $query->where('incapacidad_id', $params['incapacidad_id']);

        $registros = $query->orderBy('fecha', 'desc')->get();

        return $this->json($response, [
            'total'      => $registros->count(),
            'por_estado' => $registros->groupBy('estado')->map->count(),
            'registros'  => $registros
        ]);
    }
}
