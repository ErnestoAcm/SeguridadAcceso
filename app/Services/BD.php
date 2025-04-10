<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use App\Models\Usuario;

class BD
{
    public function obtenerUsuario($correo)
{
    return DB::transaction(function () use ($correo) {
    $usuario = Usuario::where('correo', $correo)->lockForUpdate()->first();

    if (!$usuario) {
        throw new \Exception('Usuario no encontrado.');
    }
    if ($usuario->getEnProceso()) {
        throw new \Exception('El usuario ya está siendo modificado.');
    }
    $usuario->setEnProceso(true);
    $usuario->save();
    return $usuario;
    });
}

public function existeUsuario($correo)
{
    return Usuario::where('correo', $correo)->lockForUpdate()->first();
}

    public function grabar($usuario)
    {
        return DB::transaction(function () use ($usuario) {
        $usuario->save();
    });
    }


}
