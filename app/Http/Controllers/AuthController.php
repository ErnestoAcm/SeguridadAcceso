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
        $request->validate([
            'correo' => [
                'required',
                'email',
                'max:100',
                'regex:/^[a-zA-Z0-9._%+-]{1,50}@[a-zA-Z0-9.-]{1,50}\.com$/'
            ],
            'nip' => [
                'required',
                'min:12',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/'
            ],
        ], [
            'correo.regex' => 'El correo debe tener un máximo de 50 caracteres antes y después del @, y terminar con ".com".',
            'nip.regex' => 'La contraseña debe tener al menos 12 caracteres, incluyendo una mayúscula, una minúscula, un número y un símbolo.',
        ]);

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
        $request->validate([
            'correo' => [
                'required',
                'email',
                'max:100',
                'regex:/^[a-zA-Z0-9._%+-]{1,50}@[a-zA-Z0-9.-]{1,50}\.com$/'
            ],
            'password' => [
                'required',
                'min:12',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/',
                'confirmed'
            ],
        ], [
            'correo.regex' => 'El correo debe tener un máximo de 50 caracteres antes y después del @, y terminar con ".com".',
            'nip.regex' => 'La contraseña debe tener al menos 12 caracteres, incluyendo una mayúscula, una minúscula, un número y un símbolo.',
        ]);

        $resultado = $this->autenticacion->registrarUsuario($request->correo, $request->password);

        if ($resultado['success']) {
            session()->flash('success', $resultado['message']);
        } else {
            session()->flash('error', $resultado['message']);
        }

        return back();
    }


}
