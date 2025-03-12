<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="User API Documentation",
 *      description="API untuk manajemen pengguna",
 *      @OA\Contact(
 *          email="support@example.com"
 *      ),
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register user",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password","name"},
     *             @OA\Property(property="username", type="string", example="user123"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="name", type="string", example="John Doe")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User registered successfully"),
     *     @OA\Response(response=400, description="Invalid request")
     * )
     */
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'token' => bin2hex(random_bytes(32)), // Generate random token
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user)
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="user123"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        if (!Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            return response()->json(['errors' => ['message' => ['Invalid credentials']]], 401);
        }

        $user = Auth::user();
        $user->token = bin2hex(random_bytes(32)); // Generate new token
        $user->save();

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user)
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Get authenticated user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="User profile"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function profile(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['errors' => ['message' => ['Unauthorized']]], 401);
        }

        return response()->json([
            'user' => new UserResource($user)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/update",
     *     summary="Update user information",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Name"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="User updated successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(UserUpdateRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['errors' => ['message' => ['Unauthorized']]], 401);
        }

        if ($request->filled('name')) {
            $user->name = $request->name;
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => new UserResource($user)
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Logout successful"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['errors' => ['message' => ['Unauthorized']]], 401);
        }

        $user->token = null;
        $user->save();
        Auth::logout(); // Logout session

        return response()->json([
            'message' => 'Logout successful'
        ], 200);
    }
}
