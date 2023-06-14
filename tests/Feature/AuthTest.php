<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use App\Notifications;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseMigrations;
    use WithFaker;

    public function test_register()
    {
        Notification::fake();

        $userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson(route('register'), $userData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        $user = \App\Models\User::where('email', $userData['email'])->firstOrFail();

        Notification::assertSentTo($user, Notifications\UserRegistered::class);
    }

    public function test_login()
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);
    }

    // Until someone figures out how to test logout with Sanctum i'm leaving this commented out
    // public function test_logout()
    // {
    //     $user = \App\Models\User::factory()->create();

    //     $token = $user->createToken('test token')->plainTextToken;

    //     $this->withHeader('Authorization', 'Bearer ' . $token);

    //     $response = $this->postJson(route('logout'));

    //     $response->assertStatus(200);

    //     $response = $this->getJson(route('expenses.index'));
    //     $response->assertStatus(401);
    // }
}
