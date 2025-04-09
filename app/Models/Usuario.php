<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuarios';
    protected $primaryKey = 'correo';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['correo', 'nip', 'intentos', 'Conectado', 'concurrencia', 'created_at', 'updated_at'];


    public function getCorreo()
    {
        return $this->attributes['correo'];
    }

    public function setCorreo($correo)
    {
        $this->attributes['correo'] = $correo;
    }

    public function getNip()
    {
        return $this->attributes['nip'];
    }

    public function setNip($nip)
    {
        $this->attributes['nip'] = Hash::make($nip);
    }

    public function getIntentos()
    {
        return $this->attributes['intentos'];
    }

    public function setIntentos($intentos)
    {
        $this->attributes['intentos'] = $intentos;
    }

    public function getConectado()
    {
        return $this->attributes['Conectado'];
    }

    public function setConectado($estado)
    {
        $this->attributes['Conectado'] = $estado;
    }

    public function getEnProceso()
    {
        return $this->attributes['concurrencia'];
    }

    public function setEnProceso($estado)
    {
        $this->attributes['concurrencia'] = $estado;
    }
    public function getUpdatedAt()
    {
        return $this->asDateTime($this->attributes['updated_at']);
    }
    public function verificarNip($nip)
    {
        return Hash::check($nip, $this->getNip());
    }
    public function bloquear()
    {
        $this->intentos = 3;
        $this->updated_at = now()->addMinutes(30);
    }

    public function desconectar()
    {
        $this->setConectado(false);
    }

}
