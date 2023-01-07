<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Create a new controller instance
     *
     * - Registers the "auth:api" middleware, which verifies that the user is authenticated before allowing access to the controller's methods.
     * 
     * @return  void
     */
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

    /**
     * Handle register
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate Input
        $this->validateRegisterData($request);

        // Filter only the credential
        $credentials = $request->only(['email', 'password']);

        if ($request->has('nim') && $request->missing('id')) :
            // Create User and Mahasiswa Object 
            $this->createUser($credentials, 1);

            // Retrieve User Id
            $user_id = User::where('email', $request->email)->first()->id;

            // Create Mahasiswa data
            $data = $request->only(['nim', 'jurusan', 'nama']);
            $data['tl'] = $request->tanggal_lahir;
            $user = $this->createMahasiswa($data, $user_id);

        elseif ($request->has('id') && $request->missing('nim')) :
            // Create User and Dosen object.
            $this->createUser($credentials, 2);

            // Retrive User id
            $user_id = User::where('email', $request->email)->first()->id;

            // Create Dosen data
            $data = $request->only(['nama', 'id']);
            $user = $this->createDosen($data, $user_id);

        elseif ($request->has(['nim', 'id'])) :
            return response()->json([
                'status' => 300,
                'type' => 'error',
                'description' => 'Masukkan data yang valid!'
            ], 300);
        endif;

        // Response message
        return response()->json([
            'status' => 201,
            'type' => 'success',
            'description' => 'Berhasil Registrasi!',
            'data' => $user
        ], 201);
    }


    public function validateRegisterData(Request $request)
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
    }

    /**
     * Create user object using spesific form data
     *
     * @param array $credential is email and password
     * @param int $role is user role based on spesific data
     * @return void
     */
    public function createUser($credential, $role)
    {
        User::create(array_merge(
            $credential,
            [
                'password' => Hash::make($credential['password']),
                'role_id' => $role
            ]
        ));
    }

    /**
     * Create Mahasiswa Object data
     *
     * @param array $data is Spesific data which only mahasiswa having
     * @param int $user_id is retrieved after pass the validation
     * @return void
     */
    public function createMahasiswa($data, $user_id)
    {
        $mahasiswaData = [
            'user_id' => $user_id,
            'tl' => \DateTime::createFromFormat('d-m-Y', $data['tl'])->format('Y-m-d')
        ];

        return Mahasiswa::create(array_merge($data, $mahasiswaData));
    }

    /**
     * Create Dosen Object data
     *
     * @param array $data is spesific data dosen have
     * @param int $user_id is id retrieved after creating user object
     * @return void
     */
    public function createDosen($data, $user_id)
    {
        $dosenData = [
            'user_id' => $user_id
        ];

        return Dosen::create(array_merge($data, $dosenData));
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

    /**
     * Using Auth Guard instance
     *
     * @return  void
     */
    public function guard()
    {
        return Auth::guard();
    }
}
