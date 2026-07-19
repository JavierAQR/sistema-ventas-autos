<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendedor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validamos que lleguen los datos requeridos
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Buscamos al vendedor por su email
        $vendedor = Vendedor::where('email', $request->email)->first();

        // 3. Verificamos que el vendedor exista y la contraseña coincida
        if (!$vendedor || !Hash::check($request->password, $vendedor->password)) {
            return response()->json([
                'mensaje' => 'Las credenciales proporcionadas son incorrectas.'
            ], 401);
        }

        // 4. Generamos el token de Sanctum
        $token = $vendedor->createToken('auth_token')->plainTextToken;

        // 5. Retornamos el vendedor y su token
        return response()->json([
            'mensaje' => 'Login exitoso',
            'vendedor' => $vendedor,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }
}