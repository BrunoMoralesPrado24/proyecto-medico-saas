<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class MedicalEncrypt implements CastsAttributes
{
    /**
     * Encripta el valor ANTES de guardarlo en la base de datos (AES-256).
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }
        return Crypt::encryptString($value);
    }

    /**
     * Desencripta el valor CUANDO lo lees de la base de datos.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            // Si la llave de encriptación cambia o el dato está corrupto
            return null;
        }
    }
}
