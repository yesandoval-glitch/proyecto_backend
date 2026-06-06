<?php

namespace App\Controllers;

use App\Models\Empleado;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EmpleadoController
{
    // -------------------------------------------------------
    // GET /empleados
    // -------------------------------------------------------
    public function index(Request $request, Response $response): Response
    {
        $params     = $request->getQueryParams();
        $query      = Empleado::query()->orderBy('nombres');

        if (!empty($params['documento'])) {
            $query->porDocumento($params['documento']);
        }

        if (!empty($params['area'])) {
            $query->porArea($params['area']);
        }

        if (!empty($params['estado'])) {
            $query->porEstado($params['estado']);
        }

        $empleados = $query->get();

        return $this->jsonResponse($response, [
            'success' => true,
            'data'    => $empleados,
            'total'   => $empleados->count(),
        ], 200);
    }

    // -------------------------------------------------------
    // GET /empleados/{id}
    // -------------------------------------------------------
    public function show(Request $request, Response $response, array $args): Response
    {
        $empleado = Empleado::find((int) $args['id']);

        if (!$empleado) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Empleado no encontrado.',
            ], 404);
        }

        return $this->jsonResponse($response, [
            'success' => true,
            'data'    => $empleado,
        ], 200);
    }

    // -------------------------------------------------------
    // POST /empleados
    // -------------------------------------------------------
    public function store(Request $request, Response $response): Response
    {
        $body   = $request->getParsedBody();
        $errores = $this->validarCamposObligatorios($body);

        if (!empty($errores)) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Errores de validacion.',
                'errores' => $errores,
            ], 422);
        }

        if (Empleado::documentoExiste($body['documento'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => "El documento '{$body['documento']}' ya esta registrado.",
            ], 409);
        }

        if (Empleado::correoExiste($body['correo'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => "El correo '{$body['correo']}' ya esta registrado.",
            ], 409);
        }

        if (!$this->fechaValida($body['fecha_ingreso'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'La fecha de ingreso no es valida.',
            ], 422);
        }

        $empleado = Empleado::create([
            'nombres'      => trim($body['nombres']),
            'apellidos'    => trim($body['apellidos']),
            'documento'    => trim($body['documento']),
            'correo'       => strtolower(trim($body['correo'])),
            'telefono'     => trim($body['telefono']),
            'cargo'        => trim($body['cargo']),
            'area'         => trim($body['area']),
            'fecha_ingreso'=> $body['fecha_ingreso'],
            'estado'       => 'activo',
        ]);

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Empleado creado correctamente.',
            'data'    => $empleado,
        ], 201);
    }

    // -------------------------------------------------------
    // PUT /empleados/{id}
    // -------------------------------------------------------
    public function update(Request $request, Response $response, array $args): Response
    {
        $empleado = Empleado::find((int) $args['id']);

        if (!$empleado) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Empleado no encontrado.',
            ], 404);
        }

        $body = $request->getParsedBody();

        // Validar documento unico (excluyendo el actual)
        if (!empty($body['documento']) && Empleado::documentoExiste($body['documento'], $empleado->id)) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => "El documento '{$body['documento']}' ya esta registrado.",
            ], 409);
        }

        // Validar correo unico (excluyendo el actual)
        if (!empty($body['correo']) && Empleado::correoExiste($body['correo'], $empleado->id)) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => "El correo '{$body['correo']}' ya esta registrado.",
            ], 409);
        }

        if (!empty($body['fecha_ingreso']) && !$this->fechaValida($body['fecha_ingreso'])) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'La fecha de ingreso no es valida.',
            ], 422);
        }

        $camposActualizables = ['nombres', 'apellidos', 'documento', 'correo', 'telefono', 'cargo', 'area', 'fecha_ingreso', 'estado'];
        foreach ($camposActualizables as $campo) {
            if (isset($body[$campo])) {
                $empleado->$campo = trim($body[$campo]);
            }
        }
        $empleado->save();

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => 'Empleado actualizado correctamente.',
            'data'    => $empleado,
        ], 200);
    }

    // -------------------------------------------------------
    // PATCH /empleados/{id}/estado
    // -------------------------------------------------------
    public function cambiarEstado(Request $request, Response $response, array $args): Response
    {
        $empleado = Empleado::find((int) $args['id']);

        if (!$empleado) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Empleado no encontrado.',
            ], 404);
        }

        $body   = $request->getParsedBody();
        $estado = $body['estado'] ?? '';

        if (!in_array($estado, ['activo', 'inactivo'], true)) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => 'Estado invalido. Valores permitidos: activo, inactivo.',
            ], 422);
        }

        try {
            $empleado->cambiarEstado($estado);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return $this->jsonResponse($response, [
            'success' => true,
            'message' => "Estado del empleado actualizado a '{$estado}'.",
            'data'    => $empleado,
        ], 200);
    }

    // -------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------

    private function validarCamposObligatorios(array $body): array
    {
        $requeridos = ['nombres', 'apellidos', 'documento', 'correo', 'telefono', 'cargo', 'area', 'fecha_ingreso'];
        $errores    = [];
        foreach ($requeridos as $campo) {
            if (empty($body[$campo])) {
                $errores[] = "El campo '{$campo}' es obligatorio.";
            }
        }
        return $errores;
    }

    private function fechaValida(string $fecha): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }

    private function jsonResponse(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
