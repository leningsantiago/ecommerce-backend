<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;


class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Aplica el middleware de autenticación, excepto para ciertos métodos.
        $this->middleware('auth:api', ['except' => ['loginAdmin', 'loginEcommerce', 'register']]);
    }

    /**
     * Attempt to log in the user and return the user details and access token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Intenta el inicio de sesión
        if ($token =  Auth::guard('api')->attempt(['email' => $request->email, 'password' => $request->password, 'state' => 1, 'type_user' => 2])) {
            return $this->respondWithToken($token);
        }

        // El inicio de sesión falló
        return response()->json([
            'message' => 'Unauthorized',
        ], 401);
    }

    public function loginEcommerce(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Intenta el inicio de sesión
        if ($token =  Auth::guard('api')->attempt(['email' => $request->email, 'password' => $request->password, 'state' => 1])) {
            return $this->respondWithToken($token);
        }

        // El inicio de sesión falló
        return response()->json([
            'message' => 'Unauthorized',
        ], 401);
    }


    /**
     * Register a new user and return the user details and access token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'type_user' => $request->type_user,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::guard('api')->login($user);

        return $this->respondWithToken($token);

        // return response()->json([
        //     'access_token' => $token,
        //     'status' => 'success',
        //     'message' => 'User created successfully',
        //     'user' => $user,
        //     'authorization' => [
        //         'type' => 'bearer',
        //     ]
        // ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Refresh a token and return the refreshed user details and new access token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return response()->json([
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function profile()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $user = auth('api')->user();


        // Cargar la relación 'role' en la consulta
        $userWithRole = User::with('role')->find($user->id);


        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            "user" => [
                "name" => $userWithRole->name,
                "surname" => $userWithRole->surname,
                "email" => $userWithRole->email,
                "role" => $userWithRole->role,
            ]
        ]);
    }
}
