<?php

namespace App\Models;

use App\Services\BD;
use Illuminate\Support\Facades\DB;

class autenticacion
{
    protected $bd;

    public function __construct(BD $bd)
    {
        $this->bd = $bd;
    }

    public function autenticar($correo, $nip)
    {
        return DB::transaction(function () use ($correo, $nip) {
            $usuario = $this->bd->obtenerUsuarioPorCorreo($correo);

            if (!$usuario) {
                return ['success' => false, 'message' => 'Usuario no encontrado.'];
            }

            if ($usuario->concurrencia) {
                return ['success' => false, 'message' => 'El usuario está siendo procesado. Intente nuevamente.'];
            }

            $usuario->concurrencia = true;
            $this->bd->actualizarUsuario($usuario);

            try {
                if ($usuario->intentos >= 3) {
                    $tiempoBloqueo = $usuario->updated_at->addMinutes(30);
                    if (now()->greaterThanOrEqualTo($tiempoBloqueo)) {
                        $this->reiniciarIntentos($usuario);
                        $this->bd->actualizarUsuario($usuario);
                    } else {
                        return ['success' => false, 'message' => 'Cuenta bloqueada. Intente nuevamente después de 30 minutos.'];
                    }
                }

                if (!$this->verificarNip($usuario, $nip)) {
                    $usuario->intentos += 1;

                    if ($usuario->intentos >= 3) {
                        $this->bloquear($usuario);
                    }

                    $this->bd->actualizarUsuario($usuario);
                    return ['success' => false, 'message' => 'Credenciales incorrectas.'];
                }

                if ($usuario->Conectado) {
                    return ['success' => false, 'message' => 'El usuario ya está conectado.'];
                }

                $this->reiniciarIntentos($usuario);
                $this->conectar($usuario);
                $this->bd->actualizarUsuario($usuario);

                return ['success' => true, 'message' => 'Inicio de sesión exitoso.'];
            } finally {
                $usuario->concurrencia = false;
                $this->bd->actualizarUsuario($usuario);
            }
        });
    }

    public function cerrarSesion($correo)
    {
        return DB::transaction(function () use ($correo) {
            $usuario = $this->bd->obtenerUsuarioPorCorreo($correo);

            if (!$usuario) {
                return ['success' => false, 'message' => 'Usuario no encontrado.'];
            }

            if ($usuario->concurrencia) {
                return ['success' => false, 'message' => 'El usuario está siendo procesado. Intente nuevamente.'];
            }

            $usuario->concurrencia = true;
            $this->bd->actualizarUsuario($usuario);

            try {
                $this->desconectar($usuario);
                $this->bd->actualizarUsuario($usuario);

                return ['success' => true, 'message' => 'Sesión cerrada correctamente.'];
            } finally {
                $usuario->concurrencia = false;
                $this->bd->actualizarUsuario($usuario);
            }
        });
    }


    public function registrarUsuario($correo, $password)
    {
        return DB::transaction(function () use ($correo, $password) {
            $usuarioExistente = $this->bd->obtenerUsuarioPorCorreo($correo);

            if ($usuarioExistente) {
                return ['success' => false, 'message' => 'El correo ya está registrado.'];
            }

            $usuario = new \App\Models\Usuario([
                'correo' => $correo,
                'nip' => $password,
                'intentos' => 0,
                'Conectado' => false,
            ]);

            $this->bd->guardarUsuario($usuario);

            return ['success' => true, 'message' => 'Usuario registrado exitosamente.'];
        });
    }

    private function verificarNip($usuario, $nip)
    {
        return password_verify($nip, $usuario->nip);
    }

    private function bloquear($usuario)
    {
        $usuario->intentos = 3;
        $usuario->updated_at = now()->addMinutes(30);
    }

    private function reiniciarIntentos($usuario)
    {
        $usuario->intentos = 0;
    }

    private function conectar($usuario)
    {
        $usuario->Conectado = true;
    }

    private function desconectar($usuario)
    {
        $usuario->Conectado = false;
    }
}
