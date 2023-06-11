<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ExpenseTest extends TestCase
{
    public function test_getAll_expenses()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an expense
        $expense = $user->expenses()->create([
            'description' => 'Test Expense',
            'date' => '2022-01-01',
            'value' => 100.50,
        ]);

        // Make a GET request to the 'index' route
        $response = $this->actingAs($user)->get(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'description' => $expense->description,
            'date' => $expense->date,
            'value' => $expense->value,
        ]);

        // Now, let's test if an unauthenticated user can get all expenses
        $response = $this->get(route('expenses.index'));

        $response->assertStatus(401); // 401 Unauthorized
    }

    public function test_get_expense()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an expense
        $expense = $user->expenses()->create([
            'description' => 'Test Expense',
            'date' => '2022-01-01',
            'value' => 100.50,
        ]);

        // Make a GET request to the 'show' route
        $response = $this->actingAs($user)->get(route('expenses.show', $expense->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'description' => $expense->description,
            'date' => $expense->date,
            'value' => $expense->value,
        ]);

        // Now, let's test if an unauthenticated user can get an expense
        $response = $this->get(route('expenses.show', $expense->id));

        $response->assertStatus(401); // 401 Unauthorized
    }

    public function test_create_expense()
    {
        // Create a user
        $user = User::factory()->create();

        // Generate expense data
        $expenseData = [
            'description' => 'Test Expense',
            'date' => '2022-01-01',
            'value' => 100.50,
        ];

        // Make a POST request to the 'store' route
        $response = $this->actingAs($user)->post(route('expenses.store'), $expenseData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('expenses', $expenseData);

        // Now, let's test if an unauthenticated user can create an expense
        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertStatus(401); // 401 Unauthorized
    }

    public function test_update_expense()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an expense
        $expense = $user->expenses()->create([
            'description' => 'Test Expense',
            'date' => '2022-01-01',
            'value' => 100.50,
        ]);

        // Generate expense data
        $expenseData = [
            'description' => 'Test Expense Updated',
            'date' => '2022-01-01',
            'value' => 100.50,
        ];

        // Make a PUT request to the 'update' route
        $response = $this->actingAs($user)->put(route('expenses.update', $expense->id), $expenseData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('expenses', $expenseData);

        // Now, let's test if another user can update an expense
        $anotherUser = User::factory()->create();

        $response = $this->actingAs($anotherUser)->put(route('expenses.update', $expense->id), $expenseData);

        $response->assertStatus(403); // 403 Forbidden

        // Now, let's test if an unauthenticated user can update an expense
        $response = $this->put(route('expenses.update', $expense->id), $expenseData);

        $response->assertStatus(403); // 401 Unauthorized
    }

    public function test_delete_expense()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an expense
        $expense = $user->expenses()->create([
            'description' => 'Test Expense',
            'date' => '2022-01-01',
            'value' => 100.50,
        ]);

        // Make a DELETE request to the 'destroy' route
        $response = $this->actingAs($user)->delete(route('expenses.destroy', $expense->id));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('expenses', $expense->toArray());

        // Now, let's test if another user can delete an expense
        $anotherUser = User::factory()->create();

        $response = $this->actingAs($anotherUser)->delete(route('expenses.destroy', $expense->id));

        $response->assertStatus(404); // 404 Not found

        // Now, let's test if an unauthenticated user can delete an expense
        $response = $this->delete(route('expenses.destroy', $expense->id));

        $response->assertStatus(404); // 404 Not found
    }
}
