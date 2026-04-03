<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Clinic; // <-- Agregar esta importación
use Illuminate\Support\Facades\DB; // <-- Agregar esta importación
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
            'clinic_name' => ['required', 'string', 'max:255'], // <-- Exigimos el nombre de la clínica
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        // 2. Transacción atómica
        return DB::transaction(function () use ($input) {

            // A. Creamos la clínica primero
            $clinic = Clinic::create([
                'name' => $input['clinic_name'],
            ]);

            // B. Creamos al usuario y lo atamos a la clínica
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'clinic_id' => $clinic->id,
            ]);

            // C. Le asignamos el rol de Administrador de su clínica (Spatie)
            // $user->assignRole('admin'); // <-- Descomentar cuando Paco termine sus roles

            return $user;
        });
    }
}
