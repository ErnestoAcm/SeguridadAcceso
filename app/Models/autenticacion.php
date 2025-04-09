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

            if ($usuario->getEnProceso()) {
                return ['success' => false, 'message' => 'El usuario está siendo procesado. Intente nuevamente.'];
            }

            $usuario->setEnProceso(true);
            $this->bd->grabar($usuario);

            try {
                if ($usuario->getIntentos() >= 3) {
                    $tiempoBloqueo = $usuario->getUpdatedAt()->addMinutes(30);
                    if (now()->greaterThanOrEqualTo($tiempoBloqueo)) {
                        $usuario->setIntentos(0);
                    } else {
                        return ['success' => false, 'message' => 'Cuenta bloqueada. Intente nuevamente después de 30 minutos.'];
                    }
                }

                if (!$usuario->verificarNip($nip)) {
                    $this->agregarintentos($usuario);
                    return ['success' => false, 'message' => 'Credenciales incorrectas.'];
                }

                if ($usuario->getConectado()) {
                    $this->agregarintentos($usuario);
                    return ['success' => false, 'message' => 'El usuario ya está conectado.'];
                }

                $usuario->setIntentos(0);
                $usuario->setConectado(true);
                $this->bd->grabar($usuario);

                return ['success' => true, 'message' => 'Inicio de sesión exitoso.'];
            }finally {
                $usuario->setEnProceso(false);
                $this->bd->grabar($usuario);
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

            if ($usuario->getEnProceso()) {
                return ['success' => false, 'message' => 'El usuario está siendo procesado. Intente nuevamente.'];
            }

            $usuario->setEnProceso(true);
            $this->bd->grabar($usuario);

            try {
                $usuario->desconectar();
                $this->bd->grabar($usuario);

                return ['success' => true, 'message' => 'Sesión cerrada correctamente.'];
            } finally {
                $usuario->setEnProceso(false);
                $this->bd->grabar($usuario);
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

            $usuario = new Usuario();
            $usuario->setCorreo($correo);
            $usuario->setNip($password);
            $usuario->setIntentos(0);
            $usuario->setConectado(false);

            $this->bd->grabar($usuario);

            return ['success' => true, 'message' => 'Usuario registrado exitosamente.'];
        });
    }
    public function agregarintentos(usuario $usuario)
    {
    $usuario->setIntentos($usuario->getIntentos() + 1);
                    if ($usuario->getIntentos() >= 3) {
                        $usuario->bloquear();
                    }
                    $this->bd->grabar($usuario);
                    return;
    }


}
