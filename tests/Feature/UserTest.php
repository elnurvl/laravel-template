<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_users_with_specified_name(): void
    {
        // Arrange
        User::factory()->count(10)->create();
        User::factory()->create([
            'name' => 'PHPUnit',
        ]);

        $response = $this->getJson('/users?name=PHPUnit');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_gets_requested_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        $response = $this->getJson('/users/' . $user->getKey());

        $response->assertStatus(200);
    }
}
