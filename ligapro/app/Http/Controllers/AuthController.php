<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:admin,representante,aficionado',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'aficionado',
        ]);

        event(new \Illuminate\Auth\Events\Registered($user));

        return response()->json([
            'message' => 'Usuario registrado exitosamente. Por favor verifica tu correo electrónico antes de iniciar sesión.',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Debes verificar tu correo electrónico para poder acceder.',
                'email_verified' => false
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Bienvenido de nuevo a LigaPro',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function verifyEmail(Request $request, $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'message' => 'Usuario no encontrado.'
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'El correo electrónico ya ha sido verificado previamente.'
            ]);
        }

        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($user));

        return response()->json([
            'message' => '¡Correo electrónico verificado exitosamente!'
        ]);
    }

    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'El correo electrónico ya ha sido verificado.'
            ], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Se ha reenviado el enlace de verificación a tu correo.'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'No encontramos un usuario registrado con ese correo electrónico.'
            ], 404);
        }

        Password::sendResetLink(['email' => $user->email]);

        return response()->json([
            'message' => 'Enlace de recuperación enviado con éxito.'
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }
}
