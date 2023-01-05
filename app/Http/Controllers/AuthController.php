<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'logout', 'register']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {

        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        // Accept all form data
        $this->validate($request, [
            'nama' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'nim' => 'size:8|unique:mahasiswa',
            'id' => 'size:5|unique:dosen',
            'tanggal_lahir' => 'date_format:d-m-Y',
            'jurusan' => 'integer'
        ]);

        // Filter only the credential
        $credentials = $request->only(['email', 'password']);

        // Save data to conditional input
        // If the user is Mahasiswa
        if ($request->has('nim') && $request->missing('id')) :
            $user = User::create(array_merge(
                $credentials,
                [
                    'password' => Hash::make($request->password),
                    'role_id' => 1
                ]
            ));

            $user_id = User::where('email', $request->email)->first()->id;
            $data = $request->only(['nim', 'jurusan', 'nama']);

            // Input to mahasiswa table
            Mahasiswa::create(array_merge(
                $data,
                [
                    'user_id' => $user_id,
                    'tl' => \DateTime::createFromFormat('d-m-Y', $request->tanggal_lahir)->format('Y-m-d')
                ]
            ));

        // If the user is Dosen
        elseif ($request->has('id') && $request->missing('nim')) :
            $user = User::create(array_merge(
                $credentials,
                [
                    'password' => Hash::make($request->password),
                    'role_id' => 2
                ]
            ));

            $user_id = User::where('email', $request->email)->first()->id;
            $data = $request->only(['nama', 'id']);

            Dosen::create(array_merge(
                $data,
                [
                    'user_id' => $user_id
                ]
            ));

        // If the user input has nim and id
        elseif ($request->has(['nim', 'id'])) :
            return response()->json([
                'message' => 'Masukkan data yang valid!'
            ], 300);
        endif;

        // Response message
        return response()->json([
            'message' => 'Berhasil Registrasi!',
            'user' => $user
        ], 201);
    }

    public function me()
    {
        $data = [
            'email' => auth()->user()->email,
            'core' => Mahasiswa::where('user_id', auth()->user()->id)->first(),
            'role' => User::where('role_id', auth()->user()->role_id)->first()->role->name
        ];
        return response()->json($data);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
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
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60 * 3
        ]);
    }


    public function guard()
    {
        return Auth::guard();
    }
}
