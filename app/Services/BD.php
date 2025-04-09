<?php

namespace App\Services;

use App\Models\Usuario;

class BD
{
    public function obtenerUsuarioPorCorreo($correo)
    {
        return Usuario::where('correo', $correo)->lockForUpdate()->first();
    }

    public function grabar($usuario)
    {
        $usuario->save();
    }


}
