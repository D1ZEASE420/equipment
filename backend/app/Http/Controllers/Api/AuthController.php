<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user account.
     * Protected by 'admin' middleware in api.php — only admins can create accounts.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role'     => ['sometimes', 'in:student,admin'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'] ?? 'student',
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Vale e-posti aadress või parool.'],
            ]);
        }

        /** @var User $user */
        $user  = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Välja logitud.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    // GET /users — list all user accounts (admin only)
    public function users(): JsonResponse
    {
        $users = User::orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        return response()->json($users);
    }

    // PUT /users/{user} — update a user account (admin only)
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'role'     => ['sometimes', 'in:student,admin'],
            'password' => ['sometimes', 'confirmed', Password::min(8)],
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json($user->only(['id', 'name', 'email', 'role']));
    }

    // DELETE /users/{user} — delete a user account (admin only)
    public function deleteUser(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Sa ei saa oma kontot kustutada.'], 422);
        }

        $activeBorrowings = $user->borrowings()->whereNull('returned_at')->count();
        if ($activeBorrowings > 0) {
            return response()->json([
                'message'           => "Sellel kasutajal on {$activeBorrowings} aktiivset laenutust. Kustutamine blokeeritud.",
                'active_borrowings' => $activeBorrowings,
            ], 422);
        }

        $user->delete();
        return response()->json(['message' => 'Kasutaja kustutatud.']);
    }
}