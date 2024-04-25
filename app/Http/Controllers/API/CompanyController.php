<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ActivityType;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Solo el admin puede ver todas las empresas
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['error' => 'User must be an admin'], 400);
        }

        $companies = Company::all();

        return response()->json(['companies' => $companies], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'document_type' => 'required|in:dni,cif,nie,passport,other',
                'document_number' => 'required|string|max:255',
            ]
        );

        if ($validatedData->fails()) {
            return response()->json(['errors' => $validatedData->errors()], 400);
        }

        $user = $request->user();

        if ($user->company) {
            return response()->json(['error' => 'User already has a company'], 400);
        }

        if (!$user->hasRole('admin')) {
            return response()->json(['error' => 'User must be an admin'], 400);
        }

        DB::beginTransaction();
        try {
            $company = Company::create($request->all());
            $user->company()->save($company);
            $company->user()->associate($user);
            DB::commit();



            return response()->json(['company' => $company], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error creating company'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {

        $company = Company::find($id);

        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        $company->load('activityTypes')->load('user');

        if (
            !$request->user()->hasRole('admin') &&
            $request->user()->company->id !== $company->id
        ) {
            return response()->json(['error' => 'User must be an admin'], 400);
        }

        return response()->json(['company' => $company], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'document_type' => 'required|in:dni,cif,nie,passport,other',
                'document_number' => 'required|string|max:255',
                'status' => 'in:active,inactive',
            ]
        );

        if ($validatedData->fails()) {
            return response()->json(['errors' => $validatedData->errors()], 400);
        }

        $company = Company::find($id);

        //solo el admin puede actualizar la empresa
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['error' => 'User must be an admin'], 400);
        }

        DB::beginTransaction();
        try {
            $company->update($request->all());
            DB::commit();

            return response()->json([
                'message' => 'Company updated successfully',
                'company' => $company
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error updating company'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request  $request, string $id)
    {
        $company = Company::find($id);

        //solo el admin puede eliminar la empresa
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['error' => 'User must be an admin'], 400);
        }

        DB::beginTransaction();
        try {
            $company->delete();
            DB::commit();

            return response()->json(
                ['message' => 'Company deleted successfully'],
                204
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error deleting company'], 500);
        }
    }

    public function toogleActivityType(Request $request)
    {

        $validatedData = Validator::make(
            $request->all(),
            [
                'company_id' => 'required|exists:companies,id',
                'activity_type_id' => 'required|exists:activity_types,id',
            ]
        );

        if ($validatedData->fails()) {
            return response()->json(['errors' => $validatedData->errors()], 400);
        }

        $company = Company::find($request->company_id);
        $activityType = ActivityType::find($request->activity_type_id);

        if (!$company || !$activityType) {
            return response()->json(['error' => 'Company or activity type not found'], 404);
        }

        if (!$request->user()->hasRole('admin') || $request->user()->company->id !== $company->id) {
            return response()->json([
                'error' => 'You must be an admin or the owner of the company to toggle activity types.',
            ], 400);
        }

        if ($company->activityTypes->contains($activityType)) {
            $company->activityTypes()->detach($activityType);
        } else {
            $company->activityTypes()->attach($activityType);
        }

        return response()->json(['message' => 'Activity type toggled successfully'], 200);
    }
}
