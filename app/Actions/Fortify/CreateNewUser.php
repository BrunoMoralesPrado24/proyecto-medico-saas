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
        // 1. Validaciones Inteligentes (Condicionales por Rol)
        Validator::make($input, [
            // Reglas Generales (Todos)
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'role_type' => ['required', 'string', 'in:medico,paciente_titular'],

            // 🔥 Reglas Condicionales (Solo Médicos)
            'curp' => [
                Rule::requiredIf($input['role_type'] === 'medico'),
                'nullable',
                'string',
                'size:18',
                'regex:/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z\d]\d$/i',
                'unique:users'
            ],
            'clinic_name' => [Rule::requiredIf($input['role_type'] === 'medico'), 'nullable', 'string', 'max:255'],
            'clues' => [Rule::requiredIf($input['role_type'] === 'medico'), 'nullable', 'string', 'size:11', 'regex:/^[A-Z]{5}\d{6}$/i'],
            'cedula_profesional' => [Rule::requiredIf($input['role_type'] === 'medico'), 'nullable', 'string', 'max:20', 'alpha_num'],
            'universidad_egreso' => [Rule::requiredIf($input['role_type'] === 'medico'), 'nullable', 'string', 'max:255'],
        ], [
            // Mensajes Personalizados
            'name.regex' => 'El nombre no puede contener números ni caracteres especiales.',
            'curp.regex' => 'El formato del CURP no es válido.',
            'clues.regex' => 'El formato de la CLUES no es válido (Ej. MCSSA012345).',
            'cedula_profesional.alpha_num' => 'La cédula solo debe contener letras y números.',
        ])->validate();

        // LIMPIEZA FINAL antes de guardar (Previene inyección XSS)
        $input['name'] = strip_tags($input['name']);

        // 2. Transacción de Base de Datos
        return DB::transaction(function () use ($input) {

            // A. Creamos el usuario base
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                // 🔥 MAGIA: Si es médico guardamos el CURP, si es paciente guardamos NULL
                'curp' => $input['role_type'] === 'medico' ? strtoupper($input['curp']) : null,
                'password' => Hash::make($input['password']),
            ]);

            // B. Asignamos rol (Spatie Permission)
            $user->assignRole($input['role_type']);

            // C. Lógica inteligente para Médicos
            if ($input['role_type'] === 'medico') {

                $input['clinic_name'] = strip_tags($input['clinic_name']);

                // Busca la CLUES. Si existe, trae la clínica. Si NO existe, la crea.
                $clinic = Clinic::firstOrCreate(
                    ['clues' => strtoupper($input['clues'])],
                    [
                        'nombre' => $input['clinic_name'],
                        'admin_id' => $user->id,
                        'join_code' => strtoupper(Str::random(8)),
                    ]
                );

                // Creamos su perfil de doctor
                $doctor = Doctor::create([
                    'user_id' => $user->id,
                    'cedula_profesional' => $input['cedula_profesional'],
                    'universidad_egreso' => $input['universidad_egreso'],
                ]);

                // Lo unimos a la clínica
                $doctor->clinics()->attach($clinic->id, [
                    'is_active' => true,
                    'precio_consulta' => 0.00,
                ]);
            }

            return $user;
        });
    }
}
