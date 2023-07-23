<?php

namespace App\Http\Controllers\API;

use Throwable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            $response =  [
                "response" => false,
                "message" => "Invalid request data",
                "details" => $validator->errors()
            ];
            return response()->json($response, 400);
        }

        $credentials = $request->only('email', 'password');
        $token = Auth::attempt($credentials);

        if (!$token) {
            $response = [
                "response" => false,
                'message' => 'Unauthorized',
            ];
            return response()->json($response, 401);
        }

        $user = Auth::user();
        return response()->json([
            "response" => true,
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            $response =  [
                "response" => false,
                "message" => "Invalid request data",
                "details" => $validator->errors()
            ];
            return response()->json($response, 400);
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            $response = [
                'response' => true,
                'message' => 'User created successfully',
                'user' => $user
            ];
            return response()->json($response, 201);
        } catch (Throwable $th) {
            DB::rollback();

            $response = [
                "response" => false,
                "message" => $th->getMessage(),
            ];

            return response()->json($response, 500);
        }
    }

    public function logout()
    {
        try {
            Auth::logout();
            $response = [
                'response' => true,
                'message' => 'Successfully logged out',
            ];
            return response()->json($response);
        } catch (Throwable $th) {
            $response = [
                "response" => false,
                "message" => $th->getMessage(),
            ];

            return response()->json($response, 500);
        }
    }

    public function refresh()
    {
        try {
            return response()->json([
                'user' => Auth::user(),
                'authorisation' => [
                    'token' => Auth::refresh(),
                    'type' => 'bearer',
                ]
            ]);
        } catch (Throwable $th) {
            $response = [
                "response" => false,
                "message" => $th->getMessage(),
            ];

            return response()->json($response, 500);
        }
    }
}
