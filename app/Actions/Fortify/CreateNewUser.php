<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        // 1. Validaciones con reglas estrictas para CURP y CLUES
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'curp' => ['required', 'string', 'size:18', 'unique:users'], // <-- Obligatorio para todos (18 caracteres)
            'role_type' => ['required', 'string', 'in:medico,paciente_titular'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',

            // Campos extra SOLO si es médico
            'clinic_name' => [Rule::requiredIf($input['role_type'] === 'medico'), 'nullable', 'string', 'max:255'],
            'clues' => [Rule::requiredIf($input['role_type'] === 'medico'), 'nullable', 'string', 'size:11'], // <-- CLUES (11 caracteres)
            'cedula_profesional' => [Rule::requiredIf($input['role_type'] === 'medico'), 'nullable', 'string', 'max:20'],
            'universidad_egreso' => [Rule::requiredIf($input['role_type'] === 'medico'), 'nullable', 'string', 'max:255'],
        ])->validate();

        // 2. Transacción de Base de Datos
        return DB::transaction(function () use ($input) {

            // A. Creamos el usuario base
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'curp' => strtoupper($input['curp']), // Guardamos en mayúsculas por estándar
                'password' => Hash::make($input['password']),
            ]);

            // B. Asignamos rol
            $user->assignRole($input['role_type']);

            // C. Lógica inteligente para Médicos
            if ($input['role_type'] === 'medico') {

                // LA MAGIA: firstOrCreate
                // Busca la CLUES. Si existe, trae la clínica. Si NO existe, la crea con el array secundario.
                $clinic = Clinic::firstOrCreate(
                    ['clues' => strtoupper($input['clues'])],
                    [
                        'nombre' => $input['clinic_name'],
                        'admin_id' => $user->id, // El primer doctor en usar esta CLUES se vuelve el admin
                        'join_code' => strtoupper(Str::random(8)),
                    ]
                );

                // Creamos su perfil de doctor
                $doctor = Doctor::create([
                    'user_id' => $user->id,
                    'cedula_profesional' => $input['cedula_profesional'],
                    'universidad_egreso' => $input['universidad_egreso'],
                ]);

                // Lo unimos a la clínica encontrada (o recién creada)
                $doctor->clinics()->attach($clinic->id, [
                    'is_active' => true,
                    'precio_consulta' => 0.00, // Valor por defecto obligatorio de tu tabla
                ]);
            }

            return $user;
        });
    }
}
