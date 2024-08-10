<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['register', 'login']]);
    }


    public function register (Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $roleUser = Roles::where('name', 'user')->first();

        $createUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $roleUser->id,
        ]);

        $token = JWTAuth::fromUser($createUser);
        $user = User::with('Roles')->where('id', $createUser->id)->first();

        return response()->json([
            'message' => 'Register Berhasil',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function login(Request $request) {
        $credentials = $request->only('email', 'password');

        if (!$user = auth()->attempt($credentials)) {
            return response()->json(['error' => 'User Invalid'], 401);
        }

        $dataUser = User::with('Roles')->where('email', $request['email'])->first();
        $token = JWTAuth::fromUser($dataUser);

        return response()->json([
            'token' => $token,
            'user' => $dataUser
        ]);
    }

    public function getUser()
    {
        $user = auth()->user();
        $dataUser = User::with('Roles','Profile', 'Borrow.Book')->where('id', $user->id)->first();
        return response()->json([
            "message" => "Berhasil Get User",
            "user" => $dataUser
        ]);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}
