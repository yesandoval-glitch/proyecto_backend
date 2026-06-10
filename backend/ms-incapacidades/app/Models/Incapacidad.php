<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incapacidad extends Model
{
    protected $table = 'incapacidades';

    protected $fillable = [
        'empleado_id',
        'tipo',
        'diagnostico',
        'fecha_inicio',
        'fecha_fin',
        'dias',
        'entidad',
        'estado',
        'observaciones'
    ];
}
