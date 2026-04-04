<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\ClinicDoctor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
        // 1. Validaciones estrictas (Alineadas a tu base de datos)
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'clinic_name' => ['required', 'string', 'max:255'],
            'cedula_profesional' => ['required', 'string', 'max:20'],
            'universidad_egreso' => ['required', 'string', 'max:255'], // <-- NUEVO
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            // 2. Inyectamos Cédula y Universidad en la tabla estricta de Paco
            $doctor = Doctor::create([
                'user_id' => $user->id,
                'cedula_profesional' => $input['cedula_profesional'],
                'universidad_egreso' => $input['universidad_egreso'], // <-- NUEVO
            ]);

            $clinic = Clinic::create([
                'nombre' => $input['clinic_name'],
                'admin_id' => $user->id,
                'join_code' => strtoupper(Str::random(8)),
            ]);

            ClinicDoctor::create([
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor->id,
                'precio_consulta' => 0,
                'is_active' => true,
            ]);

            return $user;
        });
    }
}
