<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\Matcher\Not;

class ExpenseTest extends TestCase
{
    public function test_protected_expense_routes()
    {
        $response = $this->getJson(route('expenses.index'));

        $response->assertStatus(401);

        $response = $this->postJson(route('expenses.store'));

        $response->assertStatus(401);

        $response = $this->getJson(route('expenses.show', 1));

        $response->assertStatus(401);

        $response = $this->putJson(route('expenses.update', 1));

        $response->assertStatus(401);

        $response = $this->deleteJson(route('expenses.destroy', 1));

        $response->assertStatus(401);
    }

    public function test_getAll_expenses()
    {
        $user = User::factory()->create();

        $expense = $user->expenses()->create([
            'description' => 'Test Expense',
            'date' => '2022-01-01',
            'value' => 100.5,
        ]);

        $response = $this->actingAs($user)->getJson(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'description' => $expense->description,
            'date' => $expense->date,
            'value' => number_format($expense->value, 2, '.', ''),
        ]);
    }

    public function test_get_expense()
    {
        $user = User::factory()->create();

        $expense = $user->expenses()->create([
            'description' => 'Test Expense',
            'date' => '2022-01-01',
            'value' => 100.5,
        ]);

        $response = $this->actingAs($user)->getJson(route('expenses.show', $expense->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'description' => $expense->description,
            'date' => $expense->date,
            'value' => number_format($expense->value, 2, '.', ''),
        ]);
    }

    public function test_create_expense()
    {
        Notification::fake();

        $user = User::factory()->create();

        $expenseData = [
            'description' => 'Test Expense',
            'date' => '2022-01-01',
            'value' => 100.5,
        ];

        $response = $this->actingAs($user)->post(route('expenses.store'), $expenseData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('expenses', $expenseData);
        Notification::assertSentTo($user, \App\Notifications\ExpenseCreated::class);
    }

    public function test_update_expense()
    {
        Notification::fake();

        $user = User::factory()->create();


        $expense = $user->expenses()->create([
            'description' => 'Test Expense',
            'date' => '2022-01-01',
            'value' => 100.5,
        ]);

        // Generate expense data
        $expenseData = [
            'description' => 'Test Expense Updated',
            'date' => '2022-01-01',
            'value' => 100.5,
        ];

        $anotherUser = User::factory()->create();

        $response = $this->actingAs($anotherUser)->putJson(route('expenses.update', $expense->id), $expenseData);

        $response->assertStatus(403);

        $response = $this->actingAs($user)->putJson(route('expenses.update', $expense->id), $expenseData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('expenses', $expenseData);
        Notification::assertSentTo($user, \App\Notifications\ExpenseUpdated::class);
    }

    public function test_delete_expense()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an expense
        $expense = $user->expenses()->create([
            'description' => 'Test Expense',
            'date' => '2022-01-01',
            'value' => 100.5,
        ]);

        $anotherUser = User::factory()->create();

        $response = $this->actingAs($anotherUser)->deleteJson(route('expenses.destroy', $expense->id));

        $response->assertStatus(403);

        $response = $this->actingAs($user)->deleteJson(route('expenses.destroy', $expense->id));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('expenses', $expense->toArray());
    }
}
