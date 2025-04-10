<?php
namespace App\Http\Controllers;

use App\Models\autenticacion;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $autenticacion;

    public function __construct(autenticacion $autenticacion)
    {
        $this->autenticacion = $autenticacion;
    }

    public function login(Request $request)
    {
        $this->autenticacion->formato($request);

        $resultado = $this->autenticacion->autenticar($request->correo, $request->nip);

        if (!$resultado['success']) {
            return redirect()->route('login')->with('error', $resultado['message']);
        }

        if ($resultado['success']) {
            $request->session()->put('correo', $request->correo);
            return redirect()->route('dashboard')->with('success', $resultado['message']);
        }
    }
    public function logout(Request $request){
        $correo = $request->session()->get('correo');
        if ($correo) {
            $this->autenticacion->cerrarSesion($correo);
        }
        $request->session()->invalidate();
        return redirect()->route('login')->with('success', 'Has cerrado sesión correctamente.');

    }

        public function registrarUsuario(Request $request)
    {
        $this->autenticacion->formato($request);

        $resultado = $this->autenticacion->registrarUsuario($request->correo, $request->password);

        if ($resultado['success']) {
            session()->flash('success', $resultado['message']);
        } else {
            session()->flash('error', $resultado['message']);
        }

        return back();
    }


}
