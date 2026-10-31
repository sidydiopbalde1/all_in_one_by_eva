<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => fn () => Role::query()->where('name', 'admin')->value('id'),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function role(string $role): static
    {
        return $this->state(fn () => ['role_id' => Role::query()->where('name', $role)->value('id')]);
    }
}
