<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ActivityType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ActivityTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activityTypes = ActivityType::all();

        return response()->json([
            'activityTypes' => $activityTypes,
            'message' => 'Activity types retrieved successfully.',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validatedData->errors(),
            ], 400);
        }

        $user = $request->user();

        DB::beginTransaction();
        try {
            $activityType = ActivityType::create([
                'name' => $request->name,
                'description' => $request->description,
                'user_id' => $user->id,
            ]);
            DB::commit();

            return response()->json([
                'activityType' => $activityType,
                'message' => 'Activity type created successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Activity type creation failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $activityType = ActivityType::find($id);

        if (!$activityType) {
            return response()->json([
                'message' => 'Activity type not found.',
            ], 404);
        }

        return response()->json([
            'activityType' => $activityType,
            'message' => 'Activity type retrieved successfully.',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $activityType = ActivityType::find($id);

        if (!$activityType) {
            return response()->json([
                'message' => 'Activity type not found.',
            ], 404);
        }

        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validatedData->errors(),
            ], 400);
        }

        $user = $request->user();

        DB::beginTransaction();
        try {
            $activityType->update([
                'name' => $request->name,
                'description' => $request->description,
                'user_id' => $user->id,
            ]);
            DB::commit();

            return response()->json([
                'activityType' => $activityType,
                'message' => 'Activity type updated successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Activity type update failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {


        $activityType = ActivityType::find($id);
        if (!$activityType) {
            return response()->json([
                'message' => 'Activity type not found.',
            ], 404);
        }

        $user = $request->user();
        if (!$user->hasRole('admin')) {
            return response()->json([
                'message' => 'Not authorized to delete activity type',
            ], 401);
        }

        DB::beginTransaction();
        try {
            $activityType->delete();
            DB::commit();

            return response()->json([
                'message' => 'Activity type deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Activity type deletion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
