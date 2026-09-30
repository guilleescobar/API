<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_paginated_users_with_ten_per_page(): void
    {
        User::factory()->count(15)->create();

        $response = $this->getJson(route('users.index'));

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'username', 'email', 'created_at', 'updated_at'],
                ],
                'links',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);

        $this->assertSame(10, $response->json('meta.per_page'));
        $this->assertSame(15, $response->json('meta.total'));
    }

    public function test_it_creates_a_new_user_successfully(): void
    {
        $payload = [
            'name' => 'Carlos Gomez',
            'username' => 'carlosg',
            'email' => 'carlos@example.com',
            'password' => 'secret123',
        ];

        $response = $this->postJson(route('users.store'), $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.username', 'carlosg')
            ->assertJsonPath('data.email', 'carlos@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'carlos@example.com',
            'username' => 'carlosg',
        ]);

        $user = User::where('email', 'carlos@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_it_validates_create_user_data(): void
    {
        $response = $this->postJson(route('users.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_it_logs_in_user_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'ana@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson(route('users.login'), [
            'email' => 'ana@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', 'ana@example.com');
    }

    public function test_it_fails_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'ana@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson(route('users.login'), [
            'email' => 'ana@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Credenciales inválidas.');
    }

    public function test_it_updates_username_with_valid_credentials(): void
    {
        User::factory()->create([
            'username' => 'old_user',
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson(route('users.update-username'), [
            'email' => 'user@example.com',
            'password' => 'password123',
            'username' => 'new_user_123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.username', 'new_user_123');

        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'username' => 'new_user_123',
        ]);
    }

    public function test_it_fails_updating_username_with_wrong_password(): void
    {
        User::factory()->create([
            'username' => 'old_user',
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson(route('users.update-username'), [
            'email' => 'user@example.com',
            'password' => 'invalid_password',
            'username' => 'new_user_123',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Credenciales inválidas.');
    }

    public function test_it_updates_email_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'first@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson(route('users.update-email'), [
            'email' => 'first@example.com',
            'password' => 'password123',
            'new_email' => 'second@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'second@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'second@example.com',
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'first@example.com',
        ]);
    }

    public function test_it_fails_updating_email_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'first@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson(route('users.update-email'), [
            'email' => 'first@example.com',
            'password' => 'wrong',
            'new_email' => 'second@example.com',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Credenciales inválidas.');
    }

    public function test_it_updates_password_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'security@example.com',
            'password' => 'oldsecretpass',
        ]);

        $response = $this->postJson(route('users.update-password'), [
            'email' => 'security@example.com',
            'password' => 'oldsecretpass',
            'new_password' => 'newbrandsecret123',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('newbrandsecret123', $user->password));
    }

    public function test_it_fails_updating_password_with_wrong_credentials(): void
    {
        User::factory()->create([
            'email' => 'security@example.com',
            'password' => 'oldsecretpass',
        ]);

        $response = $this->postJson(route('users.update-password'), [
            'email' => 'security@example.com',
            'password' => 'incorrectpass',
            'new_password' => 'newbrandsecret123',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Credenciales inválidas.');
    }

    public function test_it_deletes_user_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'delete_me@example.com',
            'password' => 'password123',
        ]);

        $response = $this->deleteJson(route('users.destroy'), [
            'email' => 'delete_me@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Usuario eliminado correctamente.');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'email' => 'delete_me@example.com',
        ]);
    }

    public function test_it_fails_deleting_user_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'keep_me@example.com',
            'password' => 'password123',
        ]);

        $response = $this->deleteJson(route('users.destroy'), [
            'email' => 'keep_me@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Credenciales inválidas.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'keep_me@example.com',
        ]);
    }

    public function test_user_seeder_creates_at_least_two_users(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertGreaterThanOrEqual(2, User::count());
        $this->assertDatabaseHas('users', ['email' => 'juan@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'maria@example.com']);
    }
}
