<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CompanyTypeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/company-types",
     *     tags={"Company Types"},
     *     summary="Get list of company types",
     *     description="Returns a list of all company types.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        $companyTypes = CompanyType::all();
        return response()->json($companyTypes, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/api/company-types/{id}",
     *     tags={"Company Types"},
     *     summary="Get a company type by ID",
     *     description="Returns a single company type",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of company type to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Company type found"
     *     ),
     *     @OA\Response(response=404, description="Company type not found")
     * )
     */
    public function show($id)
    {
        $companyType = CompanyType::find($id);

        if (!$companyType) {
            return response()->json(['message' => 'Company type not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($companyType, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/api/company-types",
     *     tags={"Company Types"},
     *     summary="Create a new company type",
     *     description="Adds a new company type to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="LLC"),
     *             @OA\Property(property="description", type="string", example="Limited Liability Company")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Company type created"
     *     ),
     *     @OA\Response(response=400, description="Bad request")
     * )
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:company_types',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $companyType = CompanyType::create($validatedData->validated());
        return response()->json($companyType, Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *     path="/api/company-types/{id}",
     *     tags={"Company Types"},
     *     summary="Update an existing company type",
     *     description="Updates company type details by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of company type to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Corp"),
     *             @OA\Property(property="description", type="string", example="Corporation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Company type updated"
     *     ),
     *     @OA\Response(response=404, description="Company type not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $companyType = CompanyType::find($id);

        if (!$companyType) {
            return response()->json(['message' => 'Company type not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = Validator::make($request->all(), [
            'name' => 'string|max:255|unique:company_types,name,' . $companyType->id,
            'description' => 'nullable|string|max:500',
        ]);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors(), Response::HTTP_BAD_REQUEST);
        }

        $companyType->update($validatedData->validated());
        return response()->json($companyType, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *     path="/api/company-types/{id}",
     *     tags={"Company Types"},
     *     summary="Delete a company type",
     *     description="Deletes a company type by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of company type to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Company type deleted"),
     *     @OA\Response(response=404, description="Company type not found")
     * )
     */
    public function destroy($id)
    {
        $companyType = CompanyType::find($id);

        if (!$companyType) {
            return response()->json(['message' => 'Company type not found'], Response::HTTP_NOT_FOUND);
        }

        $companyType->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
