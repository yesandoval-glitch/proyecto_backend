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

    private function obtenerIncapacidad(int $id, string $token): ?array
    {
        $ch = curl_init("http://localhost:8083/incapacidades/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: $token"]);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200) return null;
        return json_decode($result, true);
    }

    // GET /seguimiento
    public function listar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Seguimiento::query();

        if (!empty($params['incapacidad_id'])) {
            $query->where('incapacidad_id', $params['incapacidad_id']);
        }
        if (!empty($params['empleado_id'])) {
            $query->where('empleado_id', $params['empleado_id']);
        }
        if (!empty($params['usuario_id'])) {
            $query->where('usuario_id', $params['usuario_id']);
        }
        if (!empty($params['accion'])) {
            $query->where('accion', $params['accion']);
        }

        return $this->json($response, $query->orderBy('fecha_accion', 'desc')->get());
    }

    // GET /seguimiento/{id}
    public function obtener(Request $request, Response $response, array $args): Response
    {
        $seguimiento = Seguimiento::find($args['id']);
        if (!$seguimiento) {
            return $this->json($response, ['error' => 'Registro de seguimiento no encontrado'], 404);
        }
        return $this->json($response, $seguimiento);
    }

    // GET /seguimiento/incapacidad/{incapacidad_id}
    public function porIncapacidad(Request $request, Response $response, array $args): Response
    {
        $registros = Seguimiento::where('incapacidad_id', $args['incapacidad_id'])
            ->orderBy('fecha_accion', 'desc')
            ->get();

        return $this->json($response, $registros);
    }

    // POST /seguimiento
    public function registrar(Request $request, Response $response): Response
    {
        $data  = $request->getParsedBody();
        $token = $request->getHeaderLine('Authorization');

        $requeridos = ['incapacidad_id', 'accion'];
        foreach ($requeridos as $campo) {
            if (empty($data[$campo])) {
                return $this->json($response, ['error' => "El campo $campo es requerido"], 400);
            }
        }

        $accionesValidas = ['creacion', 'actualizacion', 'cambio_estado', 'consulta', 'aprobacion', 'rechazo'];
        if (!in_array($data['accion'], $accionesValidas)) {
            return $this->json($response, ['error' => 'Accion invalida'], 400);
        }

        $usuario = $this->obtenerUsuario($token);
        if (!$usuario) {
            return $this->json($response, ['error' => 'No se pudo obtener informacion del usuario'], 401);
        }

        $incapacidad = $this->obtenerIncapacidad((int)$data['incapacidad_id'], $token);
        if (!$incapacidad) {
            return $this->json($response, ['error' => 'La incapacidad no existe'], 404);
        }

        $seguimiento = Seguimiento::create([
            'incapacidad_id'  => $data['incapacidad_id'],
            'empleado_id'     => $incapacidad['empleado_id'],
            'usuario_id'      => $usuario['id'],
            'accion'          => $data['accion'],
            'estado_anterior' => $data['estado_anterior'] ?? null,
            'estado_nuevo'    => $data['estado_nuevo'] ?? null,
            'observaciones'   => $data['observaciones'] ?? null,
            'fecha_accion'    => date('Y-m-d H:i:s')
        ]);

        return $this->json($response, ['mensaje' => 'Seguimiento registrado correctamente', 'seguimiento' => $seguimiento], 201);
    }

    // GET /seguimiento/reporte
    public function reporte(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Seguimiento::query();

        if (!empty($params['fecha_inicio'])) {
            $query->where('fecha_accion', '>=', $params['fecha_inicio']);
        }
        if (!empty($params['fecha_fin'])) {
            $query->where('fecha_accion', '<=', $params['fecha_fin'] . ' 23:59:59');
        }
        if (!empty($params['empleado_id'])) {
            $query->where('empleado_id', $params['empleado_id']);
        }

        $registros = $query->orderBy('fecha_accion', 'desc')->get();

        $resumen = [
            'total'          => $registros->count(),
            'por_accion'     => $registros->groupBy('accion')->map->count(),
            'por_estado_nuevo'=> $registros->whereNotNull('estado_nuevo')->groupBy('estado_nuevo')->map->count(),
            'registros'      => $registros
        ];

        return $this->json($response, $resumen);
    }
}
