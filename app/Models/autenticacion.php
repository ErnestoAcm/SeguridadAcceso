<?php

namespace App\Models;

use App\Services\BD;

class autenticacion
{
    protected $bd;

    public function __construct(BD $bd)
    {
        $this->bd = $bd;
    }

    public function autenticar($correo, $nip)
    {
            try {
                $usuario = $this->bd->obtenerUsuario($correo);

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
            }catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }finally {
                if (isset($usuario)) {
                $usuario->setEnProceso(false);
                $this->bd->grabar($usuario);
                }
            }
    }

    public function cerrarSesion($correo)
    {
            try {
                $usuario = $this->bd->obtenerUsuario($correo);

                $usuario->desconectar();
                $this->bd->grabar($usuario);
                return ['success' => true, 'message' => 'Sesión cerrada correctamente.'];
            }catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            } finally {
                if (isset($usuario)) {
                    $usuario->setEnProceso(false);
                    $this->bd->grabar($usuario);
                    }
            }
    }

    public function registrarUsuario($correo, $password)
    {
            $usuarioExistente = $this->bd->existeUsuario($correo);

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
