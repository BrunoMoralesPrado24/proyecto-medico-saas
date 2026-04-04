<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Doctor; // <-- El modelo de Paco
use App\Models\ClinicDoctor; // <-- La tabla pivote de Paco
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // <-- Para generar códigos aleatorios
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // 1. Validación estricta (Incluye el nombre de la clínica)
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'clinic_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        // 2. Transacción atómica (Si algo falla, no se guarda nada basura en la BD)
        return DB::transaction(function () use ($input) {

            // A. Creamos al Usuario base (El que hace login)
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            // B. Le asignamos el rol (Descomentar si el RoleSeeder de Paco tiene el rol 'admin' o 'medico')
            // $user->assignRole('admin');

            // C. Creamos su Perfil de Doctor (Dejamos los campos extras vacíos para que los llene después en su perfil)
            $doctor = Doctor::create([
                'user_id' => $user->id,
            ]);

            // D. Creamos la Clínica (Usando los campos de la nueva migración de Paco)
            $clinic = Clinic::create([
                'nombre' => $input['clinic_name'],
                'admin_id' => $user->id, // El doctor que la crea es el admin
                'join_code' => strtoupper(Str::random(8)), // Código para que invite a sus secretarias luego
            ]);

            // E. Los unimos a través de la tabla Pivote de Paco
            ClinicDoctor::create([
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor->id,
                'precio_consulta' => 0, // Valor por defecto
                'is_active' => true,
            ]);

            return $user;
        });
    }
}
