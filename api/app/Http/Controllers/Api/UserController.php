<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\UpdateEmailRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUsernameRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Devuelve la paginación de los diez primeros usuarios.
     */
    public function index(): AnonymousResourceCollection
    {
        $users = User::paginate(10);

        return UserResource::collection($users);
    }

    /**
     * Crea un usuario en la base de datos.
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'] ?? null,
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Devuelve los datos de un usuario introduciendo solo el email y la contraseña.
     */
    public function login(LoginUserRequest $request): UserResource|JsonResponse
    {
        $user = $this->authenticateUser(
            $request->validated('email'),
            $request->validated('password')
        );

        if (! $user) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        return new UserResource($user);
    }

    /**
     * Actualiza el username de un usuario pasándole el email y la contraseña.
     */
    public function updateUsername(UpdateUsernameRequest $request): UserResource|JsonResponse
    {
        $user = $this->authenticateUser(
            $request->validated('email'),
            $request->validated('password')
        );

        if (! $user) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        $user->update([
            'username' => $request->validated('username'),
        ]);

        return new UserResource($user);
    }

    /**
     * Actualiza el email de un usuario pasándole el email y la contraseña.
     */
    public function updateEmail(UpdateEmailRequest $request): UserResource|JsonResponse
    {
        $user = $this->authenticateUser(
            $request->validated('email'),
            $request->validated('password')
        );

        if (! $user) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        $user->update([
            'email' => $request->validated('new_email'),
        ]);

        return new UserResource($user);
    }

    /**
     * Actualiza la contraseña de un usuario pasándole el email y la contraseña.
     */
    public function updatePassword(UpdatePasswordRequest $request): UserResource|JsonResponse
    {
        $user = $this->authenticateUser(
            $request->validated('email'),
            $request->validated('password')
        );

        if (! $user) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        $user->update([
            'password' => $request->validated('new_password'),
        ]);

        return new UserResource($user);
    }

    /**
     * Elimina el usuario pasándole email y contraseña.
     */
    public function destroy(DeleteUserRequest $request): JsonResponse
    {
        $user = $this->authenticateUser(
            $request->validated('email'),
            $request->validated('password')
        );

        if (! $user) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente.',
        ], 200);
    }

    /**
     * Authenticate and retrieve user by email and password.
     */
    private function authenticateUser(string $email, string $password): ?User
    {
        /** @var User|null $user */
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }
}
