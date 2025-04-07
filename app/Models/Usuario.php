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

    protected $fillable = ['correo', 'nip', 'intentos', 'Conectado'];

    public function setNipAttribute($value)
    {
        $this->attributes['nip'] = Hash::make($value);
    }
}
