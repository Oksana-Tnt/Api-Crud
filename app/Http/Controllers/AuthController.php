<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8'
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);
        $token = $user->createToken('access_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token
        ], 201);
    }

    public function login(Request $request)
    {

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // auth()->attempt();
        //Prova ad utilizzare le credenziali fornite per vedere se L'accesso dell'utente è possibile
        //Sta quindi già verificando se email e password corrispondono a quelli di un utente effettivamente esistente, in caso positivo restituisce true e in caso negativo restituisce false
        if (!auth()->attempt($credentials)) {
            return response()->json(['error' => 'Credenziali non valide'], 401);
        }

        //Se sono arrivato fin qui significa che è stato trovato un utente corrispondente alle credenziali fornite, quindi posso ottenere l'utente richiedendolo direttamente alla funzione auth()
        $user = auth()->user();

        //Per poter generare un token verifica che l'utente ne abbia la capacità
        //La classe user deve utilizzare il seguente trait: use Laravel\Sanctum\HasApiTokens;
        $token = $user->createToken('access_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logout eseguito']);
    }
}
