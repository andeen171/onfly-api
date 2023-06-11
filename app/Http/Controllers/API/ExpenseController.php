<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\Request;
use App\Notifications\ExpenseUpdated;
use App\Notifications\ExpenseCreated;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Expense::class);
        return ExpenseResource::collection(Expense::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
     */
    public function show(Expense $expense)
    {
        $this->authorize('view', $expense);
        return new ExpenseResource($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
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
     */
    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);
        $expense->delete();
        return response()->noContent();
    }
}
