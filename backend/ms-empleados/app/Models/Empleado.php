<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Empleado extends Model
{
    protected $table = 'empleados';

    protected $fillable = [
        'nombres',
        'apellidos',
        'documento',
        'correo',
        'telefono',
        'cargo',
        'area',
        'fecha_ingreso',
        'estado',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
    ];

    // -------------------------------------------------------
    // Scopes de consulta
    // -------------------------------------------------------

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 'activo');
    }

    public function scopePorDocumento(Builder $query, string $documento): Builder
    {
        return $query->where('documento', 'like', "%{$documento}%");
    }

    public function scopePorArea(Builder $query, string $area): Builder
    {
        return $query->where('area', 'like', "%{$area}%");
    }

    public function scopePorEstado(Builder $query, string $estado): Builder
    {
        return $query->where('estado', $estado);
    }

    // -------------------------------------------------------
    // Metodos de dominio
    // -------------------------------------------------------

    /**
     * Cambia el estado del empleado (activo / inactivo)
     */
    public function cambiarEstado(string $nuevoEstado): void
    {
        $estadosPermitidos = ['activo', 'inactivo'];
        if (!in_array($nuevoEstado, $estadosPermitidos, true)) {
            throw new \InvalidArgumentException("Estado '{$nuevoEstado}' no es valido.");
        }
        $this->estado = $nuevoEstado;
        $this->save();
    }

    /**
     * Verifica si existe otro empleado con el mismo documento (excluyendo el actual)
     */
    public static function documentoExiste(string $documento, ?int $excludeId = null): bool
    {
        $query = self::where('documento', $documento);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    /**
     * Verifica si existe otro empleado con el mismo correo (excluyendo el actual)
     */
    public static function correoExiste(string $correo, ?int $excludeId = null): bool
    {
        $query = self::where('correo', $correo);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}
