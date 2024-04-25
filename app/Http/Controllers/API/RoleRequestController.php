<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\RoleRequestAswered;
use App\Models\RoleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RoleRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        if ($user->hasRole('admin')) {
            $roleRequests = RoleRequest::all();
            return response()->json([
                'role_requests' => $roleRequests
            ], 200);
        } else {
            return response()->json([
                'message' => 'You are not authorized to perform this action'
            ], 403);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'requested_role' => 'required|string|max:15',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'errors' => $validatedData->errors()
            ], 400);
        }
        $data = $validatedData->validated();
        $user = $request->user();

        if ($user->hasRole($data['requested_role'])) {
            return response()->json([
                'message' => 'You already have this role'
            ], 400);
        }

        if ($user->hasRole('admin')) {
            return response()->json([
                'message' => 'You are an admin, you can not request a role'
            ], 400);
        }

        $roleRequest = RoleRequest::where('user_id', $user->id)
            ->where('requested_role', $data['requested_role'])
            ->where('status', 'pending')
            ->exists();

        if ($roleRequest) {
            return response()->json([
                'message' => 'You already have a pending request for this role'
            ], 400);
        }
        DB::beginTransaction();
        try {

            $roleRequest = RoleRequest::create([
                'user_id' => $user->id,
                'current_role' => $user->roles->first()->name,
                'requested_role' => $data['requested_role'],
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Role request sent successfully',
                'role_request' => $roleRequest
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to request role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $roleRequest = RoleRequest::findOrFail($id);
        $user = request()->user();

        if ($user->hasRole('admin') || $roleRequest->user_id === $user->id) {
            return response()->json([
                'role_request' => $roleRequest
            ], 200);
        } else {
            return response()->json([
                'message' => 'You are not authorized to perform this action'
            ], 403);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = Validator::make($request->all(), [
            'status' => 'required|string|in:approved,rejected',
        ]);

        if ($validatedData->fails()) {


            return response()->json([
                'errors' => $validatedData->errors()
            ], 400);
        }

        $data = $validatedData->validated();
        $roleRequest = RoleRequest::findOrFail($id);
        $user = $request->user();


        if (!$user->hasRole('admin')) {
            return response()->json([
                'message' => 'You are not authorized to perform this action'
            ], 403);
        }

        if (!$roleRequest->isPending()) {
            return response()->json([
                'message' => 'This role request is not pending'
            ], 400);
        }


        DB::beginTransaction();
        try {
            if ($data['status'] === 'approved') {
                $roleRequest->approve();
            }

            if ($data['status'] === 'rejected') {
                $roleRequest->reject();
            }

            Mail::to($roleRequest->user->email)->send(new RoleRequestAswered($roleRequest, $roleRequest->user));



            DB::commit();
            return response()->json([
                'message' => 'Role request updated successfully',
                'role_request' => $roleRequest
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update role request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    }
}
