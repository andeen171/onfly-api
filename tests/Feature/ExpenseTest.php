<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;
use App\Notifications;

class ExpenseTest extends TestCase
{
    use DatabaseMigrations;
    use WithFaker;
    // use RefreshDatabase;  // Way too slow and not needed for these tests

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
            'description' => $this->faker->text(191),
            'date' => $this->faker->date('Y-m-d', 'now'),
            'value' => $this->faker->randomFloat(2, 0, 1000)
        ]);

        $response = $this->actingAs($user)->getJson(route('expenses.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'description' => $expense->description,
            'date' => $expense->date,
            'value' => (string)$expense->value,
        ]);
    }

    public function test_get_expense()
    {
        $user = User::factory()->create();

        $expense = $user->expenses()->create([
            'description' => $this->faker->text(191),
            'date' => $this->faker->date('Y-m-d', 'now'),
            'value' => $this->faker->randomFloat(2, 0, 1000)
        ]);

        $response = $this->actingAs($user)->getJson(route('expenses.show', $expense->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'description' => $expense->description,
            'date' => $expense->date,
            'value' => (string)$expense->value,
        ]);
    }

    public function test_create_expense()
    {
        Notification::fake();

        $user = User::factory()->create();

        $expenseData = [
            'description' => $this->faker->text(191),
            'date' => $this->faker->date('Y-m-d', 'now'),
            'value' => $this->faker->randomFloat(2, 0, 1000)
        ];

        $response = $this->actingAs($user)->postJson(route('expenses.store'), $expenseData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('expenses', $expenseData);
        Notification::assertSentTo($user, Notifications\ExpenseCreated::class);

        // Test input validation
        $badExpenseData = [
            'description' => $this->faker->text(192), // Description greater than 191 characters
            'date' => $this->faker->dateTimeBetween('now', '+2 years'), // Date on future
            'value' => $this->faker->randomFloat('2', -9999, 0), // Negative value
        ];

        $response = $this->actingAs($user)->postJson(route('expenses.store'), $badExpenseData);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('expenses', $badExpenseData);
    }

    public function test_update_expense()
    {
        Notification::fake();

        $user = User::factory()->create();


        $expense = $user->expenses()->create([
            'description' => $this->faker->text(191),
            'date' => $this->faker->date('Y-m-d', 'now'),
            'value' => $this->faker->randomFloat(2, 0, 1000)
        ]);

        // Generate expense data
        $expenseData = [
            'description' => $this->faker->text(191),
            'date' => $this->faker->date('Y-m-d', 'now'),
            'value' => $this->faker->randomFloat(2, 0, 1000)
        ];

        $anotherUser = User::factory()->create();

        $response = $this->actingAs($anotherUser)->putJson(route('expenses.update', $expense->id), $expenseData);

        $response->assertStatus(403);

        $response = $this->actingAs($user)->putJson(route('expenses.update', $expense->id), $expenseData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('expenses', $expenseData);
        Notification::assertSentTo($user, Notifications\ExpenseUpdated::class);

        // Test input validation
        $badExpenseData = [
            'description' => $this->faker->text(192), // Description greater than 191 characters
            'date' => $this->faker->dateTimeBetween('now', '+2 years'), // Date on future
            'value' => $this->faker->randomFloat('2', -9999, 0), // Negative value
        ];

        $response = $this->actingAs($user)->putJson(route('expenses.update', $expense->id), $badExpenseData);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('expenses', $badExpenseData);
    }

    public function test_delete_expense()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an expense
        $expense = $user->expenses()->create([
            'description' => $this->faker->text(191),
            'date' => $this->faker->date('Y-m-d', 'now'),
            'value' => $this->faker->randomFloat(2, 0, 1000)
        ]);

        $anotherUser = User::factory()->create();

        $response = $this->actingAs($anotherUser)->deleteJson(route('expenses.destroy', $expense->id));

        $response->assertStatus(403);

        $response = $this->actingAs($user)->deleteJson(route('expenses.destroy', $expense->id));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('expenses', $expense->toArray());
    }
}
