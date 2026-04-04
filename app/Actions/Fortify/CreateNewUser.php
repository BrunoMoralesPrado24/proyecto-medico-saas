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
        // 1. Validación estricta
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'clinic_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        // 2. Transacción atómica: Si algo falla, se revierte todo para no dejar basura en la BD
        return DB::transaction(function () use ($input) {

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]);

            $doctor = Doctor::create([
                'user_id' => $user->id,
            ]);

            $clinic = Clinic::create([
                'nombre' => $input['clinic_name'],
                'admin_id' => $user->id,
                'join_code' => strtoupper(Str::random(8)),
            ]);

            // Unimos a Doctor y Clínica usando la tabla Pivote de Paco
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
