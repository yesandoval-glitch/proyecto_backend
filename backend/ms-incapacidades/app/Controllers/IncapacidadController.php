<?php
namespace App\Controllers;

use App\Models\Incapacidad;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IncapacidadController
{
    private function json(Response $response, $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private function validarEmpleado(int $id, string $token): bool
    {
        $ch = curl_init("http://localhost:8082/empleados/$id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: $token"]);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $status === 200;
    }

    // GET /incapacidades
    public function listar(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query  = Incapacidad::query();

        if (!empty($params['empleado_id'])) {
            $query->where('empleado_id', $params['empleado_id']);
        }
        if (!empty($params['tipo'])) {
            $query->where('tipo', $params['tipo']);
        }
        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }
        if (!empty($params['fecha_inicio'])) {
            $query->where('fecha_inicio', '>=', $params['fecha_inicio']);
        }
        if (!empty($params['fecha_fin'])) {
            $query->where('fecha_fin', '<=', $params['fecha_fin']);
        }

        return $this->json($response, $query->orderBy('fecha_inicio', 'desc')->get());
    }

    // GET /incapacidades/{id}
    public function obtener(Request $request, Response $response, array $args): Response
    {
        $incapacidad = Incapacidad::find($args['id']);
        if (!$incapacidad) {
            return $this->json($response, ['error' => 'Incapacidad no encontrada'], 404);
        }
        return $this->json($response, $incapacidad);
    }

    // POST /incapacidades
    public function crear(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $token = $request->getHeaderLine('Authorization');

        $requeridos = ['empleado_id', 'tipo', 'diagnostico', 'fecha_inicio', 'fecha_fin', 'entidad'];
        foreach ($requeridos as $campo) {
            if (empty($data[$campo])) {
                return $this->json($response, ['error' => "El campo $campo es requerido"], 400);
            }
        }

        $tiposValidos = ['enfermedad_general', 'accidente_trabajo', 'enfermedad_laboral', 'maternidad', 'paternidad'];
        if (!in_array($data['tipo'], $tiposValidos)) {
            return $this->json($response, ['error' => 'Tipo de incapacidad invalido'], 400);
        }

        $fechaInicio = new \DateTime($data['fecha_inicio']);
        $fechaFin    = new \DateTime($data['fecha_fin']);
        if ($fechaFin < $fechaInicio) {
            return $this->json($response, ['error' => 'La fecha fin no puede ser menor a la fecha inicio'], 400);
        }

        $dias = $fechaInicio->diff($fechaFin)->days + 1;

        if (!$this->validarEmpleado((int)$data['empleado_id'], $token)) {
            return $this->json($response, ['error' => 'El empleado no existe o no esta activo'], 404);
        }

        $incapacidad = Incapacidad::create([
            'empleado_id'   => $data['empleado_id'],
            'tipo'          => $data['tipo'],
            'diagnostico'   => $data['diagnostico'],
            'fecha_inicio'  => $data['fecha_inicio'],
            'fecha_fin'     => $data['fecha_fin'],
            'dias'          => $dias,
            'entidad'       => $data['entidad'],
            'estado'        => 'registrada',
            'observaciones' => $data['observaciones'] ?? null
        ]);

        return $this->json($response, ['mensaje' => 'Incapacidad registrada correctamente', 'incapacidad' => $incapacidad], 201);
    }

    // PUT /incapacidades/{id}
    public function actualizar(Request $request, Response $response, array $args): Response
    {
        $incapacidad = Incapacidad::find($args['id']);
        if (!$incapacidad) {
            return $this->json($response, ['error' => 'Incapacidad no encontrada'], 404);
        }

        if (in_array($incapacidad->estado, ['aprobada', 'rechazada'])) {
            return $this->json($response, ['error' => 'No se puede modificar una incapacidad aprobada o rechazada'], 400);
        }

        $data = $request->getParsedBody();

        if (!empty($data['fecha_inicio']) && !empty($data['fecha_fin'])) {
            $fechaInicio = new \DateTime($data['fecha_inicio']);
            $fechaFin    = new \DateTime($data['fecha_fin']);
            if ($fechaFin < $fechaInicio) {
                return $this->json($response, ['error' => 'La fecha fin no puede ser menor a la fecha inicio'], 400);
            }
            $data['dias'] = $fechaInicio->diff($fechaFin)->days + 1;
        }

        $campos = ['tipo', 'diagnostico', 'fecha_inicio', 'fecha_fin', 'dias', 'entidad', 'observaciones'];
        foreach ($campos as $campo) {
            if (isset($data[$campo])) {
                $incapacidad->$campo = $data[$campo];
            }
        }
        $incapacidad->save();

        return $this->json($response, ['mensaje' => 'Incapacidad actualizada correctamente', 'incapacidad' => $incapacidad]);
    }

    // PATCH /incapacidades/{id}/estado
    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $incapacidad = Incapacidad::find($args['id']);
        if (!$incapacidad) {
            return $this->json($response, ['error' => 'Incapacidad no encontrada'], 404);
        }

        $data   = $request->getParsedBody();
        $estado = $data['estado'] ?? '';

        $estadosValidos = ['registrada', 'en_proceso', 'aprobada', 'rechazada'];
        if (!in_array($estado, $estadosValidos)) {
            return $this->json($response, ['error' => 'Estado invalido. Use: registrada, en_proceso, aprobada, rechazada'], 400);
        }

        $incapacidad->estado = $estado;
        if (!empty($data['observaciones'])) {
            $incapacidad->observaciones = $data['observaciones'];
        }
        $incapacidad->save();

        return $this->json($response, ['mensaje' => 'Estado actualizado correctamente', 'incapacidad' => $incapacidad]);
    }
}
