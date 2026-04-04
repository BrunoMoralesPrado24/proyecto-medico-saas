<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role; // Importamos el modelo de Spatie

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Rol de Sistema (Ustedes los desarrolladores)
        Role::create(['name' => 'super_admin']);

        // 2. Roles del Staff de la Clínica (B2B)
        // Role::create(['name' => 'admin_clinica']); este no, ya que el admin se ve en la tabla de clínicas, no en la tabla de usuarios
        Role::create(['name' => 'medico']);
        Role::create(['name' => 'secretariado']);

        // 3. Roles de Pacientes (B2C)
        Role::create(['name' => 'paciente_titular']);
    }
}