<?php
namespace App\Controllers;

use App\Models\Empleado;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EmpleadoController
{
    private function json(Response $response, $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    // GET /empleados
    public function listar(Request $request, Response $response): Response
    {
        $params    = $request->getQueryParams();
        $query     = Empleado::query();

        if (!empty($params['documento'])) {
            $query->where('documento', 'like', '%' . $params['documento'] . '%');
        }
        if (!empty($params['area'])) {
            $query->where('area', $params['area']);
        }
        if (!empty($params['estado'])) {
            $query->where('estado', $params['estado']);
        }

        return $this->json($response, $query->get());
    }

    // GET /empleados/{id}
    public function obtener(Request $request, Response $response, array $args): Response
    {
        $empleado = Empleado::find($args['id']);
        if (!$empleado) {
            return $this->json($response, ['error' => 'Empleado no encontrado'], 404);
        }
        return $this->json($response, $empleado);
    }

    // POST /empleados
    public function crear(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $requeridos = ['nombres', 'apellidos', 'documento', 'correo', 'telefono', 'cargo', 'area', 'fecha_ingreso'];
        foreach ($requeridos as $campo) {
            if (empty($data[$campo])) {
                return $this->json($response, ['error' => "El campo $campo es requerido"], 400);
            }
        }

        if (Empleado::where('documento', $data['documento'])->exists()) {
            return $this->json($response, ['error' => 'El documento ya esta registrado'], 400);
        }
        if (Empleado::where('correo', $data['correo'])->exists()) {
            return $this->json($response, ['error' => 'El correo ya esta registrado'], 400);
        }

        $fecha = \DateTime::createFromFormat('Y-m-d', $data['fecha_ingreso']);
        if (!$fecha) {
            return $this->json($response, ['error' => 'Fecha de ingreso invalida, use formato YYYY-MM-DD'], 400);
        }

        $empleado = Empleado::create([
            'nombres'      => $data['nombres'],
            'apellidos'    => $data['apellidos'],
            'documento'    => $data['documento'],
            'correo'       => $data['correo'],
            'telefono'     => $data['telefono'],
            'cargo'        => $data['cargo'],
            'area'         => $data['area'],
            'fecha_ingreso'=> $data['fecha_ingreso'],
            'estado'       => 'activo'
        ]);

        return $this->json($response, ['mensaje' => 'Empleado creado correctamente', 'empleado' => $empleado], 201);
    }

    // PUT /empleados/{id}
    public function actualizar(Request $request, Response $response, array $args): Response
    {
        $empleado = Empleado::find($args['id']);
        if (!$empleado) {
            return $this->json($response, ['error' => 'Empleado no encontrado'], 404);
        }

        $data = $request->getParsedBody();

        if (!empty($data['documento']) && $data['documento'] !== $empleado->documento) {
            if (Empleado::where('documento', $data['documento'])->exists()) {
                return $this->json($response, ['error' => 'El documento ya esta registrado'], 400);
            }
        }
        if (!empty($data['correo']) && $data['correo'] !== $empleado->correo) {
            if (Empleado::where('correo', $data['correo'])->exists()) {
                return $this->json($response, ['error' => 'El correo ya esta registrado'], 400);
            }
        }

        $campos = ['nombres', 'apellidos', 'documento', 'correo', 'telefono', 'cargo', 'area', 'fecha_ingreso', 'estado'];
        foreach ($campos as $campo) {
            if (isset($data[$campo])) {
                $empleado->$campo = $data[$campo];
            }
        }
        $empleado->save();

        return $this->json($response, ['mensaje' => 'Empleado actualizado correctamente', 'empleado' => $empleado]);
    }

    // PATCH /empleados/{id}/estado
    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $empleado = Empleado::find($args['id']);
        if (!$empleado) {
            return $this->json($response, ['error' => 'Empleado no encontrado'], 404);
        }

        $data   = $request->getParsedBody();
        $estado = $data['estado'] ?? '';

        if (!in_array($estado, ['activo', 'inactivo'])) {
            return $this->json($response, ['error' => 'Estado invalido, use: activo o inactivo'], 400);
        }

        $empleado->estado = $estado;
        $empleado->save();

        return $this->json($response, ['mensaje' => 'Estado actualizado correctamente', 'empleado' => $empleado]);
    }
}
