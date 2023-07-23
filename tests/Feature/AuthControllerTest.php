<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Test user registration.
     */
    public function testUserRegistration()
    {
        DB::beginTransaction();

        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123', // or use $this->faker->password(8)
        ];

        $response = $this->post('/api/register', $userData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User created successfully',
            ]);

        DB::rollBack();
    }

    /**
     * Test user login.
     */
    public function testUserLogin()
    {
        DB::beginTransaction();

        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $loginData = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        $response = $this->post('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'authorization' => ['token', 'type'],
            ]);

        DB::rollBack();
    }

    /**
     * Test user logout.
     */
    public function testUserLogout()
    {
        DB::beginTransaction();

        $user = User::factory()->create();
        Auth::login($user);

        $response = $this->post('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);

        DB::rollBack();
    }

    /**
     * Test token refresh.
     */
    public function testTokenRefresh()
    {
        DB::beginTransaction();

        $user = User::factory()->create();
        Auth::login($user);

        $response = $this->post('/api/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'authorisation' => ['token', 'type'],
            ]);

        DB::rollBack();
    }
}
