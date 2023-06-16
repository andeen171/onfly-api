<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Auth\Access\AuthorizationException;
use App\Notifications\ExpenseUpdated;
use App\Notifications\ExpenseCreated;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use App\Http\Requests\ExpenseRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Expenses",
 *     description="Endpoints for managing expenses"
 * )
 */
class ExpenseController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/expenses",
     *     summary="Get a list of expenses",
     *     security={{"sanctum":{}}},
     *     tags={"Expenses"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ExpenseResource")
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *                 @OA\Property(property="prev", type="string"),
     *                 @OA\Property(property="next", type="string")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(
     *                     property="links",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="url", type="string"),
     *                         @OA\Property(property="label", type="string"),
     *                         @OA\Property(property="active", type="boolean")
     *                     )
     *                 ),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     * Display a listing of the resource.
     * @throws AuthorizationException
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Expense::class);

        $limit = (int)request()->get('limit', 10);

        $expenses = auth()->user()->expenses()->orderBy('created_at', 'desc')->paginate($limit);
        return ExpenseResource::collection($expenses);
    }

    /**
     * @OA\Post(
     *     path="/api/expenses",
     *     summary="Create a new expense",
     *     security={{"sanctum":{}}},
     *     tags={"Expenses"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ExpenseRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Expense created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ExpenseResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     * Store a newly created resource in storage.
     * @throws AuthorizationException
     */
    public function store(ExpenseRequest $request): ExpenseResource
    {
        $validatedData = $request->validated();

        $this->authorize('create', Expense::class);

        // Associate the expense with the authenticated user.
        $validatedData['user_id'] = auth()->id();

        $expense = Expense::create($validatedData);

        auth()->user()->notify(new ExpenseCreated($expense));
        return new ExpenseResource($expense);
    }

    /**
     * @OA\Get(
     *     path="/api/expenses/{id}",
     *     summary="Get a specific expense",
     *     security={{"sanctum":{}}},
     *     tags={"Expenses"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the expense",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ExpenseResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     * Display the specified resource.
     * @throws AuthorizationException
     */
    public function show(Expense $expense): ExpenseResource
    {
        $this->authorize('view', $expense);
        return new ExpenseResource($expense);
    }

    /**
     * @OA\Put(
     *     path="/api/expenses/{id}",
     *     summary="Update a specific expense",
     *     security={{"sanctum":{}}},
     *     tags={"Expenses"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the expense",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ExpenseRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Expense updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ExpenseResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     * Update the specified resource in storage.
     * @throws AuthorizationException
     */
    public function update(ExpenseRequest $request, Expense $expense): ExpenseResource
    {
        $validatedData = $request->validated();

        $this->authorize('update', $expense);

        $expense->update($validatedData);
        auth()->user()->notify(new ExpenseUpdated($expense));
        return new ExpenseResource($expense);
    }

    /**
     * @OA\Delete(
     *     path="/api/expenses/{id}",
     *     summary="Delete a specific expense",
     *     security={{"sanctum": {}}},
     *     tags={"Expenses"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the expense",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Expense deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Expense not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Expense $expense): Response
    {
        $this->authorize('delete', $expense);
        $expense->delete();
        return response()->noContent();
    }
}
