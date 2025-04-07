<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run()
    {
        DB::table('usuarios')->insert([
            [
                'correo' => 'usuario1@correo.com',
                'nip' => Hash::make('Password@1234'),
                'Conectado' => false,
                'intentos' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'correo' => 'usuario2@correo.com',
                'nip' => Hash::make('Secure#5678'),
                'Conectado' => false,
                'intentos' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
