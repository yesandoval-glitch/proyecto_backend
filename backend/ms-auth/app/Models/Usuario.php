<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'correo',
        'usuario',
        'contrasena',
        'rol',
        'token',
        'sesion_activa',
        'estado',
    ];

    protected $hidden = [
        'contrasena',
    ];

    protected $casts = [
        'sesion_activa' => 'boolean',
    ];

    /**
     * Genera un token simple basado en datos del usuario + timestamp
     */
    public function generarToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Activa la sesion del usuario y asigna el token
     */
    public function iniciarSesion(): string
    {
        $token = $this->generarToken();
        $this->token = $token;
        $this->sesion_activa = true;
        $this->save();
        return $token;
    }

    /**
     * Cierra la sesion del usuario
     */
    public function cerrarSesion(): void
    {
        $this->token = null;
        $this->sesion_activa = false;
        $this->save();
    }

    /**
     * Verifica si un token pertenece a este usuario y esta activo
     */
    public function tokenValido(string $token): bool
    {
        return $this->token === $token && $this->sesion_activa === true;
    }

    /**
     * Busca un usuario por usuario o correo
     */
    public static function buscarPorCredencial(string $credencial): ?self
    {
        return self::where('usuario', $credencial)
            ->orWhere('correo', $credencial)
            ->where('estado', 'activo')
            ->first();
    }

    /**
     * Busca usuario activo por token
     */
    public static function buscarPorToken(string $token): ?self
    {
        return self::where('token', $token)
            ->where('sesion_activa', true)
            ->where('estado', 'activo')
            ->first();
    }
}
