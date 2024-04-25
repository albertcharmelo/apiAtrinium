<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\UserRegistered;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'error' => 'User must be an admin'
            ], 400);
        }

        $users = User::all();

        return response()->json([
            'users' => $users
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'errors' => $validatedData->errors()
            ], 400);
        }

        // save user
        $data = $validatedData->validated();
        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->save();
        Mail::to($user->email)->send(new UserRegistered($user));
        $user->assignRole(Role::findByName('user', 'api'));

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }


    public function login(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'errors' => $validatedData->errors()
            ], 400);
        }

        $data = $validatedData->validated();
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function user(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        return response()->json([
            'user' => $request->user()
        ], 200);
    }


    public function logout(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User logged out successfully'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        if (
            !$request->user()->hasRole('admin') &&
            $request->user()->id !== $user->id
        ) {
            return response()->json([
                'message' => 'User must be an admin or the user itself.'
            ], 400);
        }

        return response()->json([
            'user' => $user,
            'message' => 'User retrieved successfully.'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        if (
            !$request->user()->hasRole('admin') &&
            $request->user()->id !== $user->id
        ) {
            return response()->json([
                'message' => 'User must be an admin or the user itself.'
            ], 400);
        }

        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validatedData->errors()
            ], 400);
        }

        $user->update($validatedData->validated());

        return response()->json([
            'user' => $user,
            'message' => 'User updated successfully.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        if (
            !$request->user()->hasRole('admin') &&
            $request->user()->id !== $user->id
        ) {
            return response()->json([
                'message' => 'User must be an admin or the user itself.'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.'
        ]);
    }
}
