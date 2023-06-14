<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\Notifications\ExpenseUpdated;
use App\Notifications\ExpenseCreated;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     * @throws AuthorizationException
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Expense::class);
        return ExpenseResource::collection(auth()->user()->expenses);
    }

    /**
     * Store a newly created resource in storage.
     * @throws AuthorizationException
     */
    public function store(Request $request): ExpenseResource
    {
        $validatedData = $request->validate([
            'description' => 'required|string|max:191',
            'date' => 'required|date|before_or_equal:today',
            'value' => 'required|numeric|min:0',
        ]);

        $this->authorize('create', Expense::class);

        // Associate the expense with the authenticated user.
        $validatedData['user_id'] = auth()->id();

        $expense = Expense::create($validatedData);

        auth()->user()->notify(new ExpenseCreated($expense));
        return new ExpenseResource($expense);
    }

    /**
     * Display the specified resource.
     * @throws AuthorizationException
     */
    public function show(Expense $expense): ExpenseResource
    {
        $this->authorize('view', $expense);
        return new ExpenseResource($expense);
    }

    /**
     * Update the specified resource in storage.
     * @throws AuthorizationException
     */
    public function update(Request $request, Expense $expense): ExpenseResource
    {
        $validatedData = $request->validate([
            'description' => 'required|string|max:191',
            'date' => 'required|date|before_or_equal:today',
            'value' => 'required|numeric|min:0',
        ]);

        $this->authorize('update', $expense);

        $expense->update($validatedData);
        auth()->user()->notify(new ExpenseUpdated($expense));
        return new ExpenseResource($expense);
    }

    /**
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
